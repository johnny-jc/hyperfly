<?php
declare(strict_types=1);

namespace Api\ApiApp\Admin\Model;

use Api\ApiActiveRecord\AdminMenuActiveRecord;
use Api\ApiActiveRecord\MenuActiveRecord;
use Api\ApiBase\BaseModel;
use Hyperf\DbConnection\Db;

use Hyperf\Di\Annotation\Inject;

/**
 * 菜单模型
 * Class MenuModel
 * @package Api\ApiApp\Admin\Model
 */
class MenuModel extends BaseModel
{
    /**
     * 默认顶级菜单的父级ID为0
     */
    const DEFAULT_PARENT_ID = 0;

    /**
     * 默认排序值为0
     */
    const DEFAULT_SORT = 0;

    /**
     * 默认菜单等级
     */
    const DEFAULT_LEVEL = 0;

    /**
     * 添加菜单
     * @param array $data
     * @return bool
     */
    public function createMenu(array $data = [])
    {
        try {
            MenuActiveRecord::create($data);
            return true;
        } catch (\Exception $e) {
            $this->throwApiException('添加菜单失败，错误：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 搜索菜单
     * @param array $condition
     * @return array
     */
    public function searchParentMenu(array $condition = [])
    {
        $query = MenuActiveRecord::select(['id', 'name', 'parent_id'])
            ->andFilterWhere('name', 'like', '%' . $condition['name'] . '%');
        $count = $query->count('id');
        $query = $query->offset($condition['offset'])->limit($condition['limit']);
        $menus = $query->get()->toArray();
        return [
            'count' => $count,
            'parentMenus' => $menus,
        ];
    }

    /**
     * 获取菜单列表
     * @param array $condition
     * @return array
     */
    public function getMenuList(array $condition = [])
    {
        $query = MenuActiveRecord::query()
            ->andFilterWhere('name', 'like', '%' . $condition['name'] . '%')
            ->andFilterWhere('id', '=', $condition['id'])
            ->orderBy('create_time');
        if ($condition['isParent']) {
            $query = $query->where('parent_id', '=', self::DEFAULT_PARENT_ID);
        }
        $count = $query->count('id');
        $query = $query->offset($condition['offset'])->limit($condition['limit']);
        return [
            'count' => $count,
            'list' => $query->get()->toArray(),
        ];
    }

    /**
     * 删除单个菜单
     * @param array $condition
     * @return bool
     */
    public function deleteMenuById(array $condition = [])
    {
        $model = MenuActiveRecord::find($condition['id']);
        if ($model === null) {
            return $this->setModelError('未查询到要删除的数据');
        }
        $child = $model->childMenu()->get()->toArray();
        if (!empty($child)) {
            return $this->setModelError('请先删除子菜单');
        }
        Db::beginTransaction();
        try {
            //同时删除分配给管理员的菜单
            AdminMenuActiveRecord::where('menu_id', '=', $condition['id'])->delete();
            $model->delete();
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            $this->throwApiException('删除失败，错误：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取菜单详情
     * @param array $condition
     * @return array[]|bool
     */
    public function getMenuDetail(array $condition = [])
    {
        $model = MenuActiveRecord::find($condition['id']);
        $returnData = [
            'menu' => [],
            'parentMenu' => [],
        ];
        if ($model === null) {
            return $this->setModelError('未查询到数据');
        }
        $returnData['menu'] = $model->toArray();
        $parentMenu = $model->parentMenu()->first();
        if ($parentMenu !== null) {
            $returnData['parentMenu'] = $parentMenu->toArray();
        }
        return $returnData;
    }

    /**
     * 获取子级菜单列表
     * @param int $parentId
     * @return array
     */
    public function getChildMenuList(int $parentId)
    {
        $parentMenu = MenuActiveRecord::find($parentId);
        if ($parentMenu === null) {
            return [];
        }
        return $parentMenu->childMenu()->get()->toArray();
    }

    /**
     * 更新菜单
     * @param array $data
     * @return bool|int
     */
    public function updateMenu(array $data = [])
    {
        Db::beginTransaction();
        try {
            //更新菜单数据
            MenuActiveRecord::where('id', '=', $data['id'])->update($data);
            //更新管理员菜单关联表数据[排序]
            AdminMenuActiveRecord::where('menu_id', '=', $data['id'])->update(['sort' => $data['sort']]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            $this->throwApiException('更新菜单失败，错误：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取菜单
     * @param int $id
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|null
     */
    public function getMenuById(int $id)
    {
        return MenuActiveRecord::where('id', '=', $id)->first();
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return (new MenuActiveRecord())->getTable();
    }

}
