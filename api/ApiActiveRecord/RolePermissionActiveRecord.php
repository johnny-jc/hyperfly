<?php

declare(strict_types=1);

namespace Api\ApiActiveRecord;

use Api\ApiBase\BaseActiveRecord;
use Api\ApiApp\Admin\Model\RolePermissionModel;

/**
 * Class RolePermissionActiveRecord
 * @package Api\ApiActiveRecord
 */
class  RolePermissionActiveRecord extends BaseActiveRecord
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'rbac_role_permission';

    /**
     * 属性默认值
     * @var array
     */
    protected $attributes = [
        'auth_type' => RolePermissionModel::AUTH_TYPE_WITH,
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'role_id',
        'api_app',
        'api_version',
        'api_class',
        'api_function',
        'api_route',
        'auth_type',
    ];

}
