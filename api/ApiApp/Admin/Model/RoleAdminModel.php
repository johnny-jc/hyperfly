<?php
declare(strict_types=1);

namespace Api\ApiApp\Admin\Model;

use Api\ApiActiveRecord\AdminActiveRecord;
use Api\ApiActiveRecord\RoleAdminActiveRecord;
use Api\ApiBase\BaseModel;
use Hyperf\DbConnection\Db;

/**
 * 角色管理员关联模型
 * Class RoleAdminModel
 * @package Api\ApiApp\Admin\Model
 */
class RoleAdminModel extends BaseModel
{

    /**
     * 获取管理员所分配的角色
     * @param int $adminId
     * @return array
     */
    public function getAdminRole(int $adminId)
    {
        return RoleAdminActiveRecord::where('admin_id', '=', $adminId)
            ->with(['getRole'])
            ->get()
            ->toArray();
    }

    /**
     * 删除分配的角色
     * @param int $adminId
     * @param array $roleIds
     * @return bool|string
     */
    public function deleteAdminRole(int $adminId, array $roleIds)
    {
        $adminModel = AdminActiveRecord::find($adminId);
        if ($adminModel === null) {
            return $this->setModelError('管理员不存在');
        }
        RoleAdminActiveRecord::whereIn('role_id', $roleIds)
            ->where('admin_id', '=', $adminId)
            ->delete();
        return true;
    }

    /**
     * 分配角色给管理员
     * @param int $adminId
     * @param array $roleIds
     * @return bool|string
     */
    public function assignAdminRole(int $adminId, array $roleIds)
    {
        $adminModel = AdminActiveRecord::find($adminId);
        if ($adminModel === null) {
            return $this->setModelError('管理员不存在');
        }
        $adminRoles = [];
        foreach ($roleIds as $roleId) {
            $tmp = [
                'admin_id' => $adminId,
                'role_id' => $roleId,
            ];
            $adminRoles[] = $tmp;
        }
        Db::beginTransaction();
        try {
            RoleAdminActiveRecord::insert($adminRoles);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->throwApiException($e->getMessage());
            return $this->setModelError('分配角色失败，错误：' . $e->getMessage());
        }
    }

}
