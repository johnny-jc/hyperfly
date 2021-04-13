<?php
declare(strict_types=1);

namespace Api\ApiCore;

/**
 * Interface ApiRequestResultInterface
 * @package Api\ApiCore
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
     * 请求成功默认信息
     */
    const REQUEST_SUCCESS_MESSAGE = '请求成功';

    /**
     * 请求失败默认信息
     */
    const REQUEST_FAIL_MESSAGE = '请求失败';

    /**
     * 接口类型：请求接口
     */
    const REQUEST_TYPE = 'request';

    /**
     * 请求成功返回方法
     */
    public function requestSuccess();

    /**
     * 请求失败返回方法
     */
    public function requestFail();

}
