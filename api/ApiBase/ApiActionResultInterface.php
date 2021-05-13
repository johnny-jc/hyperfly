<?php
declare(strict_types=1);

namespace Api\ApiBase;

/**
 * Interface ApiActionResultInterface
 * @package Api\ApiBase
 */
interface ApiActionResultInterface
{
    /**
     * 操作成功标示码
     */
    const ACTION_SUCCESS_CODE = 1;

    /**
     * 操作失败标示码
     */
    const ACTION_FAIL_CODE = 0;

    /**
     * 操作成功默认返回信息
     */
    const ACTION_SUCCESS_MESSAGE = '操作成功';

    /**
     * 操作失败默认返回信息
     */
    const ACTION_FAIL_MESSAGE = '操作失败';

    /**
     * 接口类型：业务接口
     */
    const ACTION_TYPE = 'action';

    /**
     * 业务操作成功返回方法
     * @return array
     */
    public function actionSuccess();

    /**
     * 业务操作失败返回方法
     * @return array
     */
    public function actionFail();
}
