<?php
declare(strict_types=1);

namespace Api\ApiApp\Admin\Model;

use Api\ApiActiveRecord\RoleActiveRecord;
use Api\ApiActiveRecord\RoleAdminActiveRecord;
use Api\ApiActiveRecord\RolePermissionActiveRecord;
use Api\ApiBase\BaseModel;
use Hyperf\DbConnection\Db;

/**
 * 角色模型
 * Class RoleModel
 * @package Api\ApiApp\Admin\Model
 */
class RoleModel extends BaseModel
{

    /**
     * 状态：正常
     */
    const STATUS_ENABLE = 1;

    /**
     * 状态：禁用
     */
    const STATUS_DISABLE = 0;

    /**
     * 默认父级ID
     */
    const DEFAULT_PARENT_ID = 0;

    /**
     * 获取表名称
     * @return string
     */
    public function getTable()
    {
        return (new RoleActiveRecord())->getTable();
    }

    /**
     * 添加角色
     * @param array $data
     * @return bool
     */
    public function createRole(array $data = [])
    {
        try {
            RoleActiveRecord::create($data);
            return true;
        } catch (\Exception $e) {
            $this->throwApiException('添加角色失败，错误：' . $e->getMessage());
        }
    }

    /**
     * 获取角色列表数据
     * @param array $condition
     * @return array
     */
    public function getRoleList(array $condition = [])
    {
        $query = RoleActiveRecord::query();
        $query = $query->andFilterWhere('name', 'like', '%' . $condition['name'] . '%');
        $count = $query->count('id');
        $list = $query->offset($condition['offset'])
            ->limit($condition['limit'])
            ->get(['id', 'name', 'create_time', 'update_time', 'status'])
            ->toArray();
        return [
            'count' => $count,
            'list' => $list,
        ];
    }

    /**
     * 获取所有角色
     * @param array|string[] $columns
     * @return array
     */
    public function getAllRole(array $columns = ['*'])
    {
        return RoleActiveRecord::get($columns)->toArray();
    }

    /**
     * 更新角色状态
     * @param array $condition
     * @param array $data
     * @return int
     */
    public function updateRoleStatus(array $condition = [], array $data = [])
    {
        return RoleActiveRecord::where('id', '=', $condition['id'])->update($data);
    }

    /**
     * 更新角色
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateRoleById(int $id, array $data)
    {
        return RoleActiveRecord::where('id', '=', $id)
            ->update($data) < 0 ? false : true;
    }

    /**
     * 删除角色
     * @param int $id
     * @return bool
     */
    public function deleteRoleById(int $id)
    {
        Db::beginTransaction();
        try {
            RoleActiveRecord::where('id', '=', $id)->delete();
            RoleAdminActiveRecord::where('role_id', '=', $id)->delete();
            RolePermissionActiveRecord::where('role_id', '=', $id)->delete();
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            $this->throwApiException('删除失败，错误：' . $e->getMessage());
            return false;
        }
    }

}
