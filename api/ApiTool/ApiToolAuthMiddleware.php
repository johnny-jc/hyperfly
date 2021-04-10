<?php
declare(strict_types=1);

namespace Api\ApiTool;

use Api\ApiActiveRecord\RbacAdminActiveRecord;
use Api\ApiBaseModel\RbacAdminModel;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Utils\ApplicationContext;

class ApiToolAuthMiddleware implements MiddlewareInterface
{
    /**
     * @Inject
     * @var \Hyperf\HttpServer\Contract\ResponseInterface
     */
    private $response;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if ($path === '/admin/v1/admin/login') {
            return $handler->handle($request);
        }

        $authentication = $request->getHeader('Authentication');
        if (empty($authentication) || empty($authentication[0])) {
            $data = [
                'code' => 0,
                'count' => 0,
                'message' => '请求未携带授权码',
                'data' => [],
            ];
            return $this->response->json($data)->withStatus(200);
        }
        $authenticationCode = $authentication[0];
        //校验授权码
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);
        $exists = $redis->exists($authenticationCode);
        if ($exists === 0) {
            $data = [
                'code' => 0,
                'count' => 0,
                'message' => '请求未授权',
                'data' => [],
            ];
            return $this->response->json($data)->withStatus(200);
        }
        if (!self::refreshAccessTokenCache($authenticationCode)) {
            //TODO 记录日志
            return $handler->handle($request);
        }
        return $handler->handle($request);
    }

    /**
     * @param $accessToken
     * @return bool
     */
    private static function refreshAccessTokenCache($accessToken)
    {
        //刷新数据库授权码过期时间
        $rbacAdminModel = RbacAdminActiveRecord::query()->where('access_token', $accessToken)->first();
        if ($rbacAdminModel === null) {
            return false;
        }
        $expireTimeStamp = date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60);
        $rbacAdminModel->access_token_expire_time = $expireTimeStamp;
        if (!$rbacAdminModel->save()) {
            return false;
        }

        //刷新缓存授权码过期时间
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);
        if (!$redis->expire($accessToken, 7 * 24 * 60 * 60)) {
            return false;
        }
        //修改缓存健`access_token_expire_time`过期时间
        $adminCacheArr = json_decode($redis->get($accessToken), true);
        $adminCacheArr['access_token_expire_time'] = $expireTimeStamp;
        if (!$redis->set($accessToken, json_encode($adminCacheArr))) {
            return false;
        }

        //刷新缓存授权码过期时间
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);
        return $redis->expire($accessToken, 7 * 24 * 60 * 60);
    }
}
