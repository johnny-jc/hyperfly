<?php

namespace Api\ApiBase;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
/**
 * Class ApiBaseController
 * @package Api\ApiBase
 */
class ApiBaseController
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        $this->__handlePropertyHandler(__CLASS__);
    }
    /**
     * 请求成功标示码
     */
    const SUCCESS_CODE = 1;
    /**
     * 请求失败标示码
     */
    const FAIL_CODE = 0;
    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;
    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;
    /**
     * @param array $data
     * @param int $count
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function success(array $data = [], int $count = 0, string $message = '请求成功')
    {
        $data = ['code' => self::SUCCESS_CODE, 'count' => $count, 'message' => $message, 'data' => $data];
        return $this->response->json($data);
    }
    /**
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function fail(string $message = '请求失败')
    {
        $data = ['code' => self::FAIL_CODE, 'count' => 0, 'message' => $message, 'data' => []];
        return $this->response->json($data);
    }
}