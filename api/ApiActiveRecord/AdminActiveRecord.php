<?php

declare(strict_types=1);

namespace Api\ApiActiveRecord;

use Api\ApiBase\BaseActiveRecord;

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
     * timestamp
     * @var bool
     */
    public $timestamps = false;

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

}
