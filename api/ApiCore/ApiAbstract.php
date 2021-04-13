<?php
declare(strict_types=1);

namespace Api\ApiCore;

/**
 * Class ApiAbstract
 * @package Api\ApiCore
 */
abstract class ApiAbstract implements ApiRequestResultInterface, ApiActionResultInterface
{
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