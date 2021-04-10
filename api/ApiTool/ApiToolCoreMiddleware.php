<?php
declare(strict_types=1);

namespace Api\ApiTool;

use Psr\Http\Message\ServerRequestInterface;
use Hyperf\Utils\Contracts\Arrayable;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * Class ApiToolCoreMiddleware
 * @package Api\ApiTool
 */
class ApiToolCoreMiddleware extends \Hyperf\HttpServer\CoreMiddleware
{
    /**
     * @Inject
     * @var \Hyperf\HttpServer\Contract\ResponseInterface
     */
    private $response;

    /**
     * Handle the response when cannot found any routes.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleNotFound(ServerRequestInterface $request)
    {
        // 重写路由找不到的处理逻辑
        return $notFoundJson = $this->response->json([
            'code' => 0,
            'count' => 0,
            'message' => '接口不存在',
            'data' => []
        ])->withStatus(200);
    }

    /**
     * Handle the response when the routes found but doesn't match any available methods.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request)
    {
        return $this->response->json([
            'code' => 0,
            'count' => 0,
            'message' => '接口不存在',
            'data' => [],
        ])->withStatus(200);
    }

}