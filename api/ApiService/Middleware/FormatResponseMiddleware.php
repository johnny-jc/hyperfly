<?php
declare(strict_types=1);

namespace Api\ApiService\Middleware;

use Api\ApiBase\Base;
use Api\ApiService\Exception\ApiException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class FormatResponseMiddleware
 * @package Api\ApiService\Middleware
 */
class FormatResponseMiddleware extends Base implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handlerObj = $handler->handle($request);
        //校验返回的状态值必须是200
        if (!$this->checkStatusCode($handlerObj->getStatusCode())) {
            //强制转化状态值为200
            $handlerObj = $this->formatStatusCode($handlerObj);
        }
        $contents = $handlerObj->getBody()->getContents();
        //校验返回数据是不是标准的JSON对象格式
        if (!$this->checkContentsIsObject($contents)) {
            throw new ApiException('返回的数据格式不是标准的JSON对象格式');
        }
        $contentsObj = json_decode($contents);
        $responseKeys = [];
        foreach ($contentsObj as $k => $v) {
            $responseKeys[] = $k;
        }
        if (!$this->checkResponseKeysIsAllowed($responseKeys)) {
            throw new ApiException('返回的JSON参数不合法');
        }
        //校验返回的JSON参数：data是否为标准的JSON对象
        if (!property_exists($contentsObj, 'data')) {
            throw new ApiException('返回的JSON参数不合法');
        }
        //校验返回的JSON数据里，data参数是不是对象格式
        //如果不是，加一个参数`result`，强制转换成JSON对象
        if (!$this->checkResponseDataIsObject($contentsObj->data)) {
            $handlerObj = $this->formatResponseData($handlerObj, $contentsObj);
        }
        return $handlerObj;
    }

    /**
     * @param int $status
     * @return bool
     */
    private function checkStatusCode(int $status): bool
    {
        return $status === self::RESPONSE_STATUS_CODE;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    private function formatStatusCode(ResponseInterface $response)
    {
        return $response->withStatus(self::RESPONSE_STATUS_CODE);
    }

    /**
     * @param string $contents
     * @return bool
     */
    private function checkContentsIsObject(string $contents): bool
    {
        return is_object(json_decode($contents));
    }

    /**
     * @param array $responseKeys
     * @return bool
     */
    private function checkResponseKeysIsAllowed(array $responseKeys): bool
    {
        $allowedKeys = self::ALLOWED_RESPONSE_KEYS;
        rsort($allowedKeys);
        rsort($responseKeys);
        return $allowedKeys === $responseKeys;
    }

    /**
     * @param $responseData
     * @return bool
     */
    private function checkResponseDataIsObject($responseData)
    {
        return is_object($responseData);
    }

    /**
     * @param ResponseInterface $response
     * @param $contentsObj
     * @return ResponseInterface
     */
    private function formatResponseData(ResponseInterface $response, $contentsObj)
    {
        $contentsObj->data = [
            self::DATA_OBJECT_FLAG => $contentsObj->data,
        ];
        $contents = json_encode($contentsObj);
        return $response->withBody(new SwooleStream($contents));
    }

}

