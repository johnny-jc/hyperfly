<?php

declare(strict_types=1);

namespace Api\ApiActiveRecord;

use Api\ApiBase\BaseActiveRecord;

/**
 * Class MenuActiveRecord
 * @package Api\ApiActiveRecord
 */
class MenuActiveRecord extends BaseActiveRecord
{
    /**
     * 表名称
     * @var string
     */
    protected $table = 'rbac_menu';

    /**
     * 自动管理时间
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'parent_id',
        'create_time',
        'update_time',
        'sort',
    ];

}
