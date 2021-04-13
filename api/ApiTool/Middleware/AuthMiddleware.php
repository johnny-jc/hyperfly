<?php
declare(strict_types=1);

namespace Api\ApiTool\Middleware;

use Api\ApiActiveRecord\AdminActiveRecord;
use Api\ApiBase\Base;
use Hyperf\Redis\Redis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Utils\ApplicationContext;

use Hyperf\Di\Annotation\Inject;

/**
 * Class AuthMiddleware
 * @package Api\ApiTool\Middleware
 */
class AuthMiddleware extends Base implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //判断是否是白名单
        if ($this->isAllowedPath($this->getRequestPath())) {
            return $handler->handle($request);
        }

        //获取头部授权码
        if (!($authenticationCode = $this->getAuthenticationCode())) {
            return $this->requestFail('没有授权码');
        }

        //校验授权码
        if (!$this->hasAuth($authenticationCode)) {
            return $this->requestFail('请求未授权');
        }

        //刷新授权码有效期
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
        $adminModel = AdminActiveRecord::query()->where('access_token', $accessToken)->first();
        if ($adminModel === null) {
            return false;
        }
        $expireTimeStamp = date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60);
        $adminModel->access_token_expire_time = $expireTimeStamp;
        if (!$adminModel->save()) {
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

    /**
     * 获取路由路径
     * @return string
     */
    private function getRequestPath(): string
    {
        return $this->request->getUri()->getPath();
    }

    /**
     * 判断是否是路由白名单
     * @param string $path
     * @return bool
     */
    private function isAllowedPath(string $path): bool
    {
        $allowed = [
            '/admin/v1/admin/login'
        ];
        return in_array($path, $allowed);
    }

    /**
     * @param string|string $key
     * @return bool|string
     */
    private function getAuthenticationCode(string $key = 'Authentication')
    {
        $authentication = $this->request->getHeader($key);
        if (empty($authentication) || empty($authentication[0])) {
            return false;
        }
        return $authentication[0];
    }

    /**
     * @param string $authenticationCode
     * @return bool
     */
    private function hasAuth(string $authenticationCode): bool
    {
        //校验授权码
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);
        $exists = $redis->exists($authenticationCode);
        return $exists === 0 ? false : true;
    }

}
