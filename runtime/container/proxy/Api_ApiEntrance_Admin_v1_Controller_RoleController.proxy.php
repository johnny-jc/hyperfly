<?php

declare (strict_types=1);
namespace Api\ApiEntrance\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiEntrance\Admin\Model\RoleModel;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
/**
 * 角色管理控制器
 * Class RoleController
 * @Controller(prefix="Admin/v1/Role")
 * @package Api\ApiEntrance\Admin\v1\Controller
 */
class RoleController extends BaseController
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
     * @var RoleModel
     */
    private $roleModel;
    /**
     * 添加角色
     * @RequestMapping(path="createRole", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function createRole()
    {
        $rules = ['name' => ['required', 'max:10', 'unique:' . $this->roleModel->getTable() . ',name'], 'status' => ['in:' . RoleModel::STATUS_DISABLE . ',' . RoleModel::STATUS_ENABLE]];
        $message = ['name.required' => '参数:attribute不存在', 'name.max' => '参数:attribute最多10位字符', 'name.unique' => '参数:attribute的值已被占用', 'status.in' => '参数:attribute的值只能是' . RoleModel::STATUS_DISABLE . '或' . RoleModel::STATUS_ENABLE];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $createData = $this->request->inputs(['name', 'status']);
        return $this->roleModel->createRole($createData) ? $this->success() : $this->fail();
    }
    /**
     * 获取角色列表
     * @RequestMapping(path="getRoleList", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getRoleList()
    {
        $condition = $this->request->all();
        $condition['name'] = $condition['name'] ?? '';
        $condition['offset'] = $condition['offset'] ?? self::OFFSET;
        $condition['limit'] = $condition['limit'] ?? self::LIMIT;
        return $this->success($this->roleModel->getRoleList($condition));
    }
    /**
     * 更新管理员状态
     * @RequestMapping(path="updateRoleStatus", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function updateRoleStatus()
    {
        $rules = ['id' => ['required', 'exists:' . $this->roleModel->getTable() . ',id'], 'status' => ['in:' . RoleModel::STATUS_DISABLE . ',' . RoleModel::STATUS_ENABLE]];
        $message = ['name.required' => '参数:attribute不存在', 'id.exists' => '参数:attribute的值不存在', 'status.in' => '参数:attribute的值只能是' . RoleModel::STATUS_DISABLE . '或' . RoleModel::STATUS_ENABLE];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $requestData = $this->request->all();
        $condition = ['id' => $requestData['id']];
        $data = ['status' => $requestData['status']];
        return $this->roleModel->updateRoleStatus($condition, $data) ? $this->success() : $this->fail();
    }
}