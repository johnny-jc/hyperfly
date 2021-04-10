<?php

declare (strict_types=1);
namespace Api\ApiTool;

use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
/**
 * Class ApiToolMethodAllowedMiddleware
 * @package Api\ApiTool
 */
class ApiToolMethodAllowedMiddleware implements MiddlewareInterface
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        $this->__handlePropertyHandler(__CLASS__);
    }
    /**
     * @Inject
     * @var \Hyperf\HttpServer\Contract\ResponseInterface
     */
    private $response;
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $method = strtolower($request->getMethod());
        if ($method === 'post') {
            return $handler->handle($request);
        }
        $data = ['code' => 0, 'count' => 0, 'message' => '请求方式不允许', 'data' => []];
        return $this->response->json($data)->withStatus(200);
    }
}