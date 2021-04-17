<?php
declare(strict_types=1);

namespace Api\ApiTool\Middleware;

use Api\ApiBase\Base;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CorsMiddleware
 * @package Api\ApiTool
 */
class CorsMiddleware extends Base implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $response = $this->setCorsHeaders($response);
        $response = $this->setCorsStatus($response);
        Context::set(ResponseInterface::class, $response);
        if (strtolower($request->getMethod()) == 'options') {
            return $response;
        }
        return $handler->handle($request);
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    private function setCorsHeaders(ResponseInterface $response)
    {
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => true,
            'Access-Control-Allow-Headers' => 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authentication',
        ];
        foreach ($headers as $k => $v) {
            $response = $response->withHeader($k, $v);
        }

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    private function setCorsStatus(ResponseInterface $response)
    {
        return $response->withStatus(self::RESPONSE_STATUS_CODE);
    }

}
