<?php

declare (strict_types=1);
namespace Api\ApiBase;

use Api\ApiCore\ApiAbstract;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Redis\Redis;
use Hyperf\Di\Annotation\Inject;
/**
 * Class ApiBase
 * @package Api\ApiBase
 */
class Base extends ApiAbstract
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
     * @var ResponseInterface
     */
    public $response;
    /**
     * @Inject
     * @var RequestInterface
     */
    public $request;
    /**
     * @Inject
     * @var Redis
     */
    public $redis;
    /**
     * @param array $data
     * @param string|string $message
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function success(array $data = [], string $message = '')
    {
        return $this->response->json(parent::success($data, $message))->withStatus(200);
    }
    /**
     * @param string|string $message
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function fail(string $message = '', array $data = [])
    {
        return $this->response->json(parent::fail($message, $data))->withStatus(200);
    }
    /**
     * @param string|string $message
     * @param array $data
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function requestFail(string $message = '', array $data = [])
    {
        return $this->response->json(parent::requestFail($message, $data))->withStatus(200);
    }
    /**
     * @param array $data
     * @param string|string $message
     * @return array|\Psr\Http\Message\ResponseInterface
     */
    public function requestSuccess(array $data = [], string $message = '')
    {
        return $this->response->json(parent::requestSuccess($data, $message))->withStatus(200);
    }
}