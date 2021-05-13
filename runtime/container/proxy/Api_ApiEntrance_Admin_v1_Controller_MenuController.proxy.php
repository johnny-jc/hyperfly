<?php

declare (strict_types=1);
namespace Api\ApiEntrance\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiEntrance\Admin\Model\MenuModel;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
/**
 * 菜单管理控制器
 * Class MenuController
 * @package Api\ApiEntrance\Admin\v1\Controller
 * @Controller(prefix="Admin/v1/Menu")
 */
class MenuController extends BaseController
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
     * @var MenuModel
     */
    private $menuModel;
    /**
     * 添加菜单
     * @param RequestInterface $request
     * @RequestMapping(path="createMenu", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function createMenu(RequestInterface $request)
    {
        $rules = ['name' => ['required'], 'sort' => ['numeric'], 'parentMenuId' => [
            //'required',
            'numeric',
            'exists:' . $this->menuModel->getTable() . ',id',
        ]];
        $message = ['name.required' => '参数:attribute不存在', 'sort.numeric' => '参数:attribute只能是数字', 'parentMenuId.numeric' => '参数:attribute只能是数字', 'parentMenuId.exists' => '参数:attribute的值不存在'];
        $validate = $this->validator($request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $requestData = $this->request->all();
        if (isset($requestData['sort']) && empty($requestData['sort'])) {
            $requestData['sort'] = MenuModel::DEFAULT_SORT;
        }
        if (!isset($requestData['parentMenuId']) || empty($requestData['parentMenuId'])) {
            $requestData['parentMenuId'] = MenuModel::DEFAULT_PARENT_ID;
        }
        $parentMenu = $this->menuModel->getMenuDetail(['id' => $requestData['parentMenuId']]);
        //$insertData = $requestData;
        $insertData['name'] = $requestData['name'];
        if ($parentMenu === false) {
            $insertData['parent_id'] = $requestData['parentMenuId'];
            $insertData['level'] = MenuModel::DEFAULT_LEVEL;
            $insertData['path'] = MenuModel::DEFAULT_LEVEL;
        } else {
            $parentMenu = $parentMenu['menu'];
            $insertData['parent_id'] = $parentMenu['id'];
            $insertData['level'] = $parentMenu['level'] + 1;
            $insertData['path'] = $parentMenu['path'] . ',' . $parentMenu['id'];
        }
        return $this->menuModel->createMenu($insertData) ? $this->success() : $this->fail();
    }
    /**
     * 获取菜单列表
     * @RequestMapping(path="getMenuList", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getMenuList()
    {
        $condition = $this->request->all();
        $condition['name'] = $condition['name'] ?? '';
        if (isset($condition['type']) && $condition['type'] == 'reset') {
            $condition['isParent'] = true;
            $condition['name'] = '';
        } else {
            $condition['isParent'] = false;
        }
        $condition['offset'] = $condition['offset'] ?? self::OFFSET;
        $condition['limit'] = $condition['limit'] ?? self::LIMIT;
        return $this->success($this->menuModel->getMenuList($condition));
    }
    /**
     * 获取子菜单列表
     * @RequestMapping(path="getChildMenuList", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getChildMenuList()
    {
        $condition = $this->request->all();
        $condition['parentId'] = (int) $condition['parentId'] ?? 0;
        return $this->success($this->menuModel->getChildMenuList($condition['parentId']));
    }
    /**
     * 删除菜单
     * @RequestMapping(path="deleteMenuById", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function deleteMenuById()
    {
        $condition = $this->request->all();
        $condition['id'] = $condition['id'] ?? null;
        return $this->menuModel->deleteMenuById($condition) ? $this->success() : $this->fail($this->menuModel->getModelError());
    }
    /**
     * 获取菜单详情
     * @RequestMapping(path="getMenuDetail", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getMenuDetail()
    {
        $condition = $this->request->all();
        $condition['id'] = $condition['menuId'] ?? null;
        $detail = $this->menuModel->getMenuDetail($condition);
        return $detail ? $this->success($detail) : $this->fail($this->menuModel->getModelError());
    }
    /**
     * 更新菜单
     * @RequestMapping(path="updateMenu", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function updateMenu()
    {
        $rules = ['id' => ['required'], 'name' => ['required'], 'sort' => ['numeric'], 'parentMenuId' => [
            //'required',
            'numeric',
            'exists:' . $this->menuModel->getTable() . ',id',
        ]];
        $message = ['id.required' => '参数:attribute不存在', 'name.required' => '参数:attribute', 'sort.numeric' => '参数:attribute只能是数字', 'parentMenuId.numeric' => '参数:attribute只能是数字', 'parentMenuId.exists' => '参数:attribute的值不存在'];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $requestData = $this->request->all();
        $updateData = ['id' => $requestData['id'], 'name' => $requestData['name'], 'sort' => $requestData['sort']];
        if (!isset($requestData['parentMenuId']) || $requestData['parentMenuId'] == '') {
            $updateData['parent_id'] = MenuModel::DEFAULT_PARENT_ID;
        } else {
            if ($requestData['parentMenuId'] != $requestData['id']) {
                $updateData['parent_id'] = $requestData['parentMenuId'];
            }
        }
        return $this->menuModel->updateMenu($updateData) ? $this->success() : $this->fail();
    }
    /**
     * 查询父级菜单
     * @RequestMapping(path="searchParentMenu", method="post")
     * @return array
     */
    public function searchParentMenu()
    {
        $condition = $this->request->all();
        $condition['name'] = $condition['name'] ?? '';
        $condition['offset'] = $condition['offset'] ?? self::OFFSET;
        $condition['limit'] = $condition['limit'] ?? self::LIMIT;
        $parentMenus = $this->menuModel->searchParentMenu($condition);
        return $this->success($parentMenus);
    }
}