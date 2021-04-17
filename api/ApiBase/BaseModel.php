<?php
declare(strict_types=1);

namespace Api\ApiBase;

/**
 * Class BaseModel
 * @package Api\ApiBase
 */
class BaseModel extends Base
{
    /**
     * 错误信息
     */
    private $errorMessage;

    /**
     * @param string $errorMessage
     * @return string
     */
    public function setModelError($errorMessage = '[未设置Model错误信息]')
    {
        $this->errorMessage = $errorMessage;
        return false;
    }

    /**
     * @return string
     */
    public function getModelError()
    {
        return $this->errorMessage;
    }

}
