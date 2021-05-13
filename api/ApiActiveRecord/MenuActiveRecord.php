<?php

declare(strict_types=1);

namespace Api\ApiActiveRecord;

use Api\ApiBase\BaseActiveRecord;
use Api\ApiApp\Admin\Model\MenuModel;

/**
 * Class MenuActiveRecord
 * @package Api\ApiActiveRecord
 */
class MenuActiveRecord extends BaseActiveRecord
{
    /**
     * 添加时间字段
     */
    const CREATED_AT = 'create_time';

    /**
     * 更新时间字段
     */
    const UPDATED_AT = 'update_time';

    /**
     * 表名称
     * @var string
     */
    protected $table = 'rbac_menu';

    /**
     * 自动管理时间
     * @var bool
     */
    public $timestamps = true;

    /**
     * 属性默认值
     * @var array
     */
    protected $attributes = [
        'parent_id' => MenuModel::DEFAULT_PARENT_ID,
        'sort' => MenuModel::DEFAULT_SORT,
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'parent_id',
        'create_time',
        'update_time',
        'sort',
        'path',
        'level',
        'href',
    ];

    /**
     * @return string
     */
    public function getTable()
    {
        return parent::getTable();
    }

    /**
     * 获取子菜单
     *
     * @return \Hyperf\Database\Model\Relations\HasMany
     */
    public function childMenu()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    /**
     * 获取父级菜单
     *
     * @return \Hyperf\Database\Model\Relations\HasOne
     */
    public function parentMenu()
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }

}
