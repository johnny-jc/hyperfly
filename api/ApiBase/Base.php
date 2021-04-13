<?php
declare(strict_types=1);

namespace Api\ApiBase;

use Api\ApiCore\ApiAbstract;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

/**
 * Class ApiBase
 * @package Api\ApiBase
 */
class Base extends ApiAbstract
{

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
