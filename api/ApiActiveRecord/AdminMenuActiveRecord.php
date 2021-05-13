<?php

declare(strict_types=1);

namespace Api\ApiActiveRecord;

use Api\ApiBase\BaseActiveRecord;

/**
 * Class RoleMenuActiveRecord
 * @package Api\ApiActiveRecord
 */
class AdminMenuActiveRecord extends BaseActiveRecord
{

    /**
     * @var string
     */
    protected $table = 'rbac_admin_menu';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * 关联菜单表
     * @return \Hyperf\Database\Model\Relations\HasOne
     */
    public function getMenu()
    {
        return $this->hasOne(MenuActiveRecord::class, 'id', 'menu_id');
    }

    /**
     * 获取子菜单
     * @return \Hyperf\Database\Model\Relations\HasMany
     */
    public function childMenu()
    {
        return $this->hasMany(self::class, 'parent_id', 'menu_id');
    }
}


