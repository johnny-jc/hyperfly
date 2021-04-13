<?php

declare (strict_types=1);
namespace Api\ApiTool\Middleware;

use Api\ApiBase\Base;
use Psr\Http\Message\ServerRequestInterface;
use Hyperf\Utils\Contracts\Arrayable;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
/**
 * Class CoreMiddleware
 * @package Api\ApiTool\Middleware
 */
class CoreMiddleware extends \Hyperf\HttpServer\CoreMiddleware
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct(\Psr\Container\ContainerInterface $container, string $serverName)
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct(...func_get_args());
        }
        $this->__handlePropertyHandler(__CLASS__);
    }
    /**
     * @Inject
     * @var Base
     */
    private $base;
    /**
     * Handle the response when cannot found any routes.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleNotFound(ServerRequestInterface $request)
    {
        return $this->base->fail('业务方法不存在');
    }
    /**
     * Handle the response when the routes found but doesn't match any available methods.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request)
    {
        return $this->base->fail('请求方式不允许');
    }
}