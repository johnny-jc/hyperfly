<?php

declare(strict_types=1);

namespace Api\ApiActiveRecord;

use Api\ApiBase\BaseActiveRecord;

/**
 * Class RoleAdminActiveRecord
 * @package Api\ApiActiveRecord
 */
class RoleAdminActiveRecord extends BaseActiveRecord
{
    /**
     * 表名称
     * @var string
     */
    protected $table = 'rbac_role_admin';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * 关联角色表
     * @return \Hyperf\Database\Model\Relations\HasOne
     */
    public function getRole()
    {
        return $this->hasOne(RoleActiveRecord::class, 'id', 'role_id');
    }

}
