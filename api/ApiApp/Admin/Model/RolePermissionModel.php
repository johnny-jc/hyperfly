<?php
declare(strict_types=1);

namespace Api\ApiApp\Admin\Model;

use Api\ApiActiveRecord\RoleActiveRecord;
use Api\ApiActiveRecord\RolePermissionActiveRecord;
use Api\ApiBase\BaseModel;
use Hyperf\DbConnection\Db;

/**
 * 角色权限关联模型
 * Class RolePermissionModel
 * @package Api\ApiApp\Admin\Model
 */
class RolePermissionModel extends BaseModel
{

    /**
     * 正向授权
     */
    const AUTH_TYPE_WITH = 1;

    /**
     * 反向授权
     */
    const AUTH_TYPE_WITHOUT = 0;

    /**
     * 获取角色的所有权限
     * @param array $condition
     * @return array
     */
    public function getRolePermission(array $condition)
    {
        return RolePermissionActiveRecord::where('role_id', '=', $condition['id'])
            ->orderBy('api_app', 'asc')
            ->orderBy('api_version', 'asc')
            ->orderBy('api_class', 'asc')
            ->orderBy('api_function', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * 删除权限
     * @param int $roleId
     * @param array $permissions
     * @return bool|string
     */
    public function deleteRolePermission(int $roleId, array $permissions)
    {
        $roleModel = RoleActiveRecord::find($roleId);
        if ($roleModel === null) {
            return $this->setModelError('角色不存在');
        }
        RolePermissionActiveRecord::whereIn('api_route', $permissions)
            ->where('role_id', '=', $roleId)
            ->delete();
        return true;
    }

    /**
     * 分配权限给角色
     * @param int $roleId
     * @param array $permissions
     * @return bool|string
     */
    public function assignRolePermission(int $roleId, array $permissions)
    {
        $roleModel = RoleActiveRecord::find($roleId);
        if ($roleModel === null) {
            return $this->setModelError('角色不存在');
        }
        $rolePermissions = [];
        foreach ($permissions as $permission) {
            $permission = explode('/', $permission);
            if ($permission === false || count($permission) != 5) {
                continue;
            }
            $tmp = [
                'role_id' => $roleId,
                'api_app' => $permission[1],
                'api_version' => $permission[2],
                'api_class' => $permission[3],
                'api_function' => $permission[4],
                'api_route' => implode('/', $permission),
                'auth_type' => self::AUTH_TYPE_WITH,
            ];
            $rolePermissions[] = $tmp;
        }
        Db::beginTransaction();
        try {
            RolePermissionActiveRecord::insert($rolePermissions);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            $this->throwApiException($e->getMessage());
            return $this->setModelError('分配权限失败，错误：' . $e->getMessage());
        }
    }

}
