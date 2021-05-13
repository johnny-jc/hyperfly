<?php
declare(strict_types=1);

namespace Api\ApiBase;

/**
 * Interface ApiRequestResultInterface
 * @package Api\ApiBase
 */
interface ApiRequestResultInterface
{
    /**
     * 请求成功标示码
     */
    const REQUEST_SUCCESS_CODE = 1;

    /**
     * 请求失败标示码
     */
    const REQUEST_FAIL_CODE = 0;

    /**
     * 未经授权状态码
     */
    const UNAUTHORIZED_CODE = 401;

    /**
     * 请求成功默认信息
     */
    const REQUEST_SUCCESS_MESSAGE = '请求成功';

    /**
     * 请求失败默认信息
     */
    const REQUEST_FAIL_MESSAGE = '请求失败';

    /**
     * 未经授权默认信息
     */
    const UNAUTHORIZED_MESSAGE = '请求未授权';

    /**
     * 接口类型：请求接口
     */
    const REQUEST_TYPE = 'request';

    /**
     * 请求成功返回方法
     * @return mixed
     */
    public function requestSuccess();

    /**
     * 请求失败返回方法
     * @return mixed
     */
    public function requestFail();

    /**
     * 请求未授权返回方法
     * @return mixed
     */
    public function requestUnauthorized();

}
