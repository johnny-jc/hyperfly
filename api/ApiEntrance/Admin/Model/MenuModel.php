<?php
declare(strict_types=1);

namespace Api\ApiEntrance\Admin\Model;

use Api\ApiActiveRecord\MenuActiveRecord;
use Api\ApiBase\BaseModel;

/**
 * Class MenuModel
 * @package Api\ApiEntrance\Admin\Model
 */
class MenuModel extends BaseModel
{

    /**
     * @param array $data
     * @return bool
     */
    public static function createMenu(array $data = [])
    {
        try {
            MenuActiveRecord::create($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
