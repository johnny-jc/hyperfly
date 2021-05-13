<?php

declare (strict_types=1);
namespace Api\ApiApp\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiApp\Admin\Model\AdminMenuModel;
use Api\ApiApp\Admin\Model\AdminModel;
use Api\ApiApp\Admin\Model\MenuModel;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
/**
 * 管理员菜单关联控制器
 * Class AdminController
 * @Controller(prefix="Admin/v1/AdminMenu")
 * @package Api\ApiApp\Admin\v1\Controller
 */
class AdminMenuController extends BaseController
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct(...func_get_args());
        }
        $this->__handlePropertyHandler(__CLASS__);
    }
    /**
     * @Inject
     * @var AdminMenuModel
     */
    private $adminMenuModel;
    /**
     * @Inject
     * @var AdminModel
     */
    private $adminModel;
    /**
     * 分配菜单给管理员
     * @RequestMapping(path="assignAdminMenu", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function assignAdminMenu()
    {
        $requestData = $this->request->all();
        if (!isset($requestData['adminId']) || empty($requestData['adminId'])) {
            return $this->fail('参数adminId不存在');
        }
        $adminId = (int) $requestData['adminId'];
        if (!isset($requestData['menuStr']) || empty($requestData['menuStr'])) {
            return $this->fail('参数menuStr不存在');
        }
        $menuArr = json_decode($requestData['menuStr'], true);
        if ($menuArr === null) {
            return $this->fail('参数menuStr不是合法的json格式');
        }
        if (empty($menuArr)) {
            $this->adminMenuModel->deleteAdminMenu($adminId);
            return $this->success();
        }
        $formatArr = $this->formatInsertMenuData($adminId, $menuArr);
        return $this->adminMenuModel->assignAdminMenu($formatArr, $adminId) ? $this->success() : $this->fail();
    }
    /**
     * 格式化成要保存的数据格式
     * @param int $adminId
     * @param array $menuArr
     * @return array
     */
    private function formatInsertMenuData(int $adminId, array $menuArr)
    {
        $arr = [];
        foreach ($menuArr as $k => $v) {
            $v = explode('|', $v);
            if (count($v) !== 5) {
                $this->throwApiException('保存失败,错误：菜单格式错误');
            }
            $tmp = ['admin_id' => $adminId, 'menu_id' => $v[0], 'path' => $v[1], 'level' => $v[2], 'parent_id' => $v[3], 'sort' => $v[4]];
            $arr[] = $tmp;
        }
        return $arr;
    }
    /**
     * 登录
     * @RequestMapping(path="getAdminMenuByAdminId", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getAdminMenuByAdminId()
    {
        $rules = ['adminId' => ['required', 'exists:' . $this->adminModel->getTable() . ',id']];
        $message = ['adminId.required' => '参数:attribute不存在', 'adminId.numeric' => '参数:attribute的值不存在'];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $requestData = $this->request->all();
        $columns = ['id', 'name', 'parent_id', 'level', 'sort', 'path'];
        $allMenu = $this->adminMenuModel->getAllMenu($columns);
        $adminMenu = $this->adminMenuModel->getAdminMenu((int) $requestData['adminId']);
        $adminMenuUnique = $this->getAdminMenuUnique($adminMenu);
        $tree = $this->getTreeMenu($allMenu, MenuModel::DEFAULT_PARENT_ID, $adminMenuUnique);
        return $this->success($tree);
    }
    /**
     * 获取树形菜单
     * @param array $menu
     * @param int $parentId
     * @param array $adminMenuUnique
     * @return array
     */
    private function getTreeMenu(array $menu, int $parentId, array $adminMenuUnique)
    {
        $tree = [];
        foreach ($menu as $k => $v) {
            if ($v['parent_id'] == $parentId) {
                $tmp = [];
                $str = (string) $v['id'] . $v['path'] . (string) $v['level'];
                if (in_array($str, $adminMenuUnique)) {
                    $tmp['checkArr'] = ['type' => 0, 'checked' => 1];
                } else {
                    $tmp['checkArr'] = ['type' => 0, 'checked' => 0];
                }
                $tmp['title'] = $v['name'];
                $tmp['id'] = $v['id'];
                $tmp['basicData'] = ['path' => $v['path'], 'level' => $v['level'], 'sort' => $v['sort']];
                $tmp['parentId'] = $v['parent_id'];
                $tmp['children'] = $this->getTreeMenu($menu, $v['id'], $adminMenuUnique);
                $tree[] = $tmp;
            }
        }
        return $tree;
    }
    /**
     * 获取管理员菜单的唯一值数组
     * @param array $adminMenu
     * @return array
     */
    private function getAdminMenuUnique(array $adminMenu)
    {
        $adminMenuArr = [];
        foreach ($adminMenu as $k => $v) {
            $str = (string) $v['menu_id'] . $v['path'] . (string) $v['level'];
            $adminMenuArr[] = $str;
        }
        return $adminMenuArr;
    }
}