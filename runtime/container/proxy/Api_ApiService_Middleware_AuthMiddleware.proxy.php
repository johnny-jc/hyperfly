<?php

declare (strict_types=1);
namespace Api\ApiService\Middleware;

use Api\ApiBase\Base;
use Api\ApiApp\Admin\Model\AdminModel;
use Api\ApiApp\Admin\Model\PermissionModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Di\Annotation\Inject;
/**
 * Class AuthMiddleware
 * @package Api\ApiService\Middleware
 */
class AuthMiddleware extends Base implements MiddlewareInterface
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct(...func_get_args());
        }
        $this->__handlePropertyHandler(__CLASS__);
    }
    /**
     * @Inject
     * @var AdminModel
     */
    private $adminModel;
    /**
     * @Inject
     * @var PermissionModel
     */
    private $permissionModel;
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        //判断是否是白名单
        if ($this->permissionModel->isInAuthWhitelist($this->request->getUri()->getPath())) {
            return $handler->handle($request);
        }
        //获取头部授权码
        if (($authenticationCode = $this->getAuthenticationCode()) === false) {
            return $this->requestUnauthorized('请先登录');
        }
        //判断授权码缓存是否存在
        if (!$this->adminModel->hasAccessTokenCache($authenticationCode)) {
            return $this->requestUnauthorized('请先登录');
        }
        //刷新授权码过期时间
        if (!$this->adminModel->refreshAccessTokenCache($authenticationCode)) {
            $this->throwApiException('系统错误，登录失败');
        }
        return $handler->handle($request);
    }
    /**
     * @return bool|string
     */
    private function getAuthenticationCode()
    {
        return $this->getHeaderAuthenticationCode() === '' ? false : $this->getHeaderAuthenticationCode();
    }
}