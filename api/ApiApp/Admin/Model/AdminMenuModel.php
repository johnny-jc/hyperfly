<?php
declare(strict_types=1);

namespace Api\ApiApp\Admin\Model;

use Api\ApiActiveRecord\AdminMenuActiveRecord;
use Api\ApiActiveRecord\MenuActiveRecord;
use Api\ApiBase\BaseModel;
use Hyperf\DbConnection\Db;

/**
 * 管理员菜单模型
 * Class RoleMenuModel
 * @package Api\ApiApp\Admin\Model
 */
class AdminMenuModel extends BaseModel
{

    /**
     * 分配菜单给管理员
     * @param array $menuArr
     * @param int $adminId
     * @return bool|string
     */
    public function assignAdminMenu(array $menuArr, int $adminId)
    {
        Db::beginTransaction();
        try {
            AdminMenuActiveRecord::where('admin_id', '=', $adminId)->delete();
            AdminMenuActiveRecord::insert($menuArr);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            $this->throwApiException('分配菜单失败，错误：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 删除所有管理员的菜单
     * @param int $adminId
     * @return bool
     */
    public function deleteAdminMenu(int $adminId)
    {
        AdminMenuActiveRecord::where('admin_id', '=', $adminId)->delete();
        return true;
    }

    /**
     * 获取所有菜单
     * @param array|string[] $columns
     * @return array
     */
    public function getAllMenu(array $columns = ['*'])
    {
        return MenuActiveRecord::get($columns)
            ->toArray();
    }

    /**
     * 获取管理员所有菜单
     * @param int $adminId
     * @param array|string[] $columns
     * @return array
     */
    public function getAdminMenu(int $adminId, array $columns = ['*'])
    {
        return AdminMenuActiveRecord::where('admin_id', '=', $adminId)
            ->with([
                'getMenu' => function ($query) {
                    $query->select(['id', 'name', 'href']);
                }
            ])
            ->orderBy('sort')
            ->get($columns)
            ->toArray();
    }

}
