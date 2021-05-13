<?php
declare(strict_types=1);

namespace Api\ApiBase;

use Api\ApiService\Exception\ApiException;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Redis\Redis;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

use Hyperf\Di\Annotation\Inject;

/**
 * Class ApiBase
 * @package Api\ApiBase
 */
class Base extends ApiAbstract
{

    /**
     * 头部access_token授权key
     */
    const AUTH_HEADER_KEY = 'Authentication';

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
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

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

    /**
     * @param string|string $message
     * @param array $data
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function requestUnauthorized(string $message = '', array $data = [])
    {
        return $this->response->json(parent::requestUnauthorized($message, $data));
    }

    /**
     * @param string|string $message
     */
    public function throwApiException(string $message = '')
    {
        throw new ApiException($message);
    }

    /**
     * @param array $validateData
     * @param array $rules
     * @param array $message
     * @return \Hyperf\Contract\ValidatorInterface
     */
    public function validator(array $validateData = [], array $rules = [], array $message = [])
    {
        return $this->validationFactory->make($validateData, $rules, $message);
    }

    /**
     * 获取access_token
     * @return string
     */
    public function getHeaderAuthenticationCode()
    {
        $header = $this->request->getHeader(self::AUTH_HEADER_KEY);
        if (empty($header)) {
            return '';
        }
        if (!isset($header[0])) {
            return '';
        }
        return $header[0];
    }

}
