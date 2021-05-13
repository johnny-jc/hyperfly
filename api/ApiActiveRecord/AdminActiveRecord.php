<?php

declare(strict_types=1);

namespace Api\ApiActiveRecord;

use Api\ApiBase\BaseActiveRecord;
use Api\ApiApp\Admin\Model\AdminModel;

/**
 * Class AdminActiveRecord
 * @package Api\ApiActiveRecord
 */
class AdminActiveRecord extends BaseActiveRecord
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
    protected $table = 'rbac_admin';

    /**
     * 使用的数据库连接
     * @var string
     */
    protected $connection = 'default';
    /**
     * 属性默认值
     * @var array
     */
    protected $attributes = [
        'status' => AdminModel::ADMIN_STATUS_ENABLE,
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'username',
        'password',
        'status',
        'create_time',
        'update_time',
        'access_token',
        'access_token_expire_time',
    ];

    /**
     * timestamp
     * @var bool
     */
    public $timestamps = true;

    /**
     * @return string
     */
    public function getTable()
    {
        return parent::getTable();
    }

}
