<?php
declare(strict_types=1);

namespace Api\ApiBase;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Redis\Redis;

use Hyperf\Di\Annotation\Inject;

/**
 * Class ApiAbstract
 * @package Api\ApiBase
 */
abstract class ApiAbstract implements ApiRequestResultInterface, ApiActionResultInterface
{
    /**
     * 统一的返回的状态码
     * 200
     */
    const RESPONSE_STATUS_CODE = 200;

    /**
     * 允许返回的接口参数
     */
    const ALLOWED_RESPONSE_KEYS = ['code', 'message', 'data', 'type'];

    /**
     * data参数转化为对象格式需要的key值
     */
    const DATA_OBJECT_FLAG = 'result';

    /**
     * 默认查询起始位置
     */
    const OFFSET = 0;

    /**
     * 默认查询条数
     */
    const LIMIT = 20;

    /**
     * 设置默认的请求成功接口返回数据
     * 强制在data为空数据的时候，类型为对象
     * 避免在JS/iOS/Android或者其他端解析数据时，格式不一致
     *
     * @param array $data
     * @param string|string $message
     * @return array
     */
    public function requestSuccess(array $data = [], string $message = '')
    {
        $returnData = [
            'type' => self::REQUEST_TYPE,
            'code' => self::REQUEST_SUCCESS_CODE,
        ];
        if ($message === '') {
            $returnData['message'] = self::REQUEST_SUCCESS_MESSAGE;
        } else {
            $returnData['message'] = $message;
        }
        if (empty($data)) {
            $returnData['data'] = (object)[];
        } else {
            $returnData['data'] = $data;
        }
        return $returnData;
    }

    /**
     * 设置默认的请求失败接口返回数据
     * 强制在data为空数据的时候，类型为对象
     * 避免在JS/iOS/Android或者其他端解析数据时，格式不一致
     *
     * @param string|string $message
     * @param array $data
     * @return mixed
     */
    public function requestFail(string $message = '', array $data = [])
    {
        $returnData = [
            'type' => self::REQUEST_TYPE,
            'code' => self::REQUEST_FAIL_CODE,
        ];
        if ($message === '') {
            $returnData['message'] = self::REQUEST_FAIL_MESSAGE;
        } else {
            $returnData['message'] = $message;
        }
        if (empty($data)) {
            $returnData['data'] = (object)$data;
        } else {
            $returnData['data'] = $data;
        }
        return $returnData;
    }

    /**
     * @param array $data
     * @param string|string $message
     * @return mixed
     */
    public function actionSuccess(array $data = [], string $message = '')
    {
        $returnData = [
            'type' => self::ACTION_TYPE,
            'code' => self::ACTION_SUCCESS_CODE,
        ];
        if ($message === '') {
            $returnData['message'] = self::ACTION_SUCCESS_MESSAGE;
        } else {
            $returnData['message'] = $message;
        }
        if (empty($data)) {
            $returnData['data'] = (object)[];
        } else {
            $returnData['data'] = $data;
        }
        return $returnData;
    }

    /**
     * @param string|string $message
     * @param array $data
     * @return mixed
     */
    public function actionFail(string $message = '', array $data = [])
    {
        $returnData = [
            'type' => self::ACTION_TYPE,
            'code' => self::ACTION_FAIL_CODE,
        ];
        if ($message === '') {
            $returnData['message'] = self::ACTION_FAIL_MESSAGE;
        } else {
            $returnData['message'] = $message;
        }
        if (empty($data)) {
            $returnData['data'] = (object)[];
        } else {
            $returnData['data'] = $data;
        }
        return $returnData;
    }

    /**
     * 请求未授权
     * @param string|string $message
     * @param array $data
     * @return array|mixed
     */
    public function requestUnauthorized(string $message = '', array $data = [])
    {
        $returnData = [
            'type' => self::REQUEST_TYPE,
            'code' => self::UNAUTHORIZED_CODE,
        ];
        if ($message === '') {
            $returnData['message'] = self::UNAUTHORIZED_MESSAGE;
        } else {
            $returnData['message'] = $message;
        }
        if (empty($data)) {
            $returnData['data'] = (object)[];
        } else {
            $returnData['data'] = $data;
        }
        return $returnData;
    }

    /**
     * @param array $data
     * @param string|string $message
     * @return mixed
     */
    public function success(array $data = [], string $message = '')
    {
        return $this->actionSuccess($data, $message);
    }

    /**
     * @param string|string $message
     * @param array $data
     * @return mixed
     */
    public function fail(string $message = '', array $data = [])
    {
        return $this->actionFail($message, $data);
    }

}
