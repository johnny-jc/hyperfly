<?php

declare(strict_types=1);

namespace Api\ApiActiveRecord;

use Api\ApiBase\BaseActiveRecord;

/**
 * Class PermissionActiveRecord
 * @package Api\ApiActiveRecord
 */
class PermissionActiveRecord extends BaseActiveRecord
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * 表名称
     * @var string
     */
    protected $table = 'rbac_permission';

    /**
     * @var string[]
     */
    protected $fillable = [
        'api_app',
        'api_version',
        'api_class',
        'api_function',
        'api_sort',
        'api_name',
    ];

    /**
     * @return string
     */
    public function getTable()
    {
        return parent::getTable();
    }

}
