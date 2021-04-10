<?php
declare(strict_types=1);

namespace Api\ApiActiveRecord;

use Hyperf\DbConnection\Model\Model;

/**
 * Class Rent
 * @package Api\ApiActiveRecord
 */
class RbacAdminActiveRecord extends Model
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
    public $timestamps = true;

}