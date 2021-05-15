<?php

declare(strict_types=1);

namespace Api\ApiApp\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiApp\Admin\Model\RoleModel;
use Hyperf\Validation\Rule;

use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use Api\ApiService\Middleware\AuthMiddleware;
use Api\ApiService\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * 角色管理控制器
 * Class RoleController
 * @package Api\ApiApp\Admin\v1\Controller
 * @Controller(prefix="Admin/v1/Role")
 * @Middlewares({
 *     @Middleware(AuthMiddleware::class),
 *     @Middleware(PermissionMiddleware::class)
 *     })
 */
class RoleController extends BaseController
{
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
        $rules = [
            'name' => [
                'required',
                'max:10',
                'unique:' . $this->roleModel->getTable() . ',name',
            ],
            'status' => [
                'in:' . RoleModel::STATUS_DISABLE . ',' . RoleModel::STATUS_ENABLE,
            ],
        ];
        $message = [
            'name.required' => '参数:attribute不存在',
            'name.max' => '参数:attribute最多10位字符',
            'name.unique' => '参数:attribute的值已被占用',
            'status.in' => '参数:attribute的值只能是' . RoleModel::STATUS_DISABLE . '或' . RoleModel::STATUS_ENABLE,
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $createData = $this->request->inputs(['name', 'status']);
        return $this->roleModel->createRole($createData) ?
            $this->success() :
            $this->fail();
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
     * 更新角色状态
     * @RequestMapping(path="updateRoleStatusById", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function updateRoleStatusById()
    {
        $rules = [
            'id' => [
                'required',
                'exists:' . $this->roleModel->getTable() . ',id',
            ],
            'status' => [
                'in:' . RoleModel::STATUS_DISABLE . ',' . RoleModel::STATUS_ENABLE,
            ],
        ];
        $message = [
            'name.required' => '参数:attribute不存在',
            'id.exists' => '参数:attribute的值不存在',
            'status.in' => '参数:attribute的值只能是' . RoleModel::STATUS_DISABLE . '或' . RoleModel::STATUS_ENABLE,
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $requestData = $this->request->all();
        $condition = [
            'id' => $requestData['id'],
        ];
        $data = [
            'status' => $requestData['status'],
        ];
        return $this->roleModel->updateRoleStatus($condition, $data) ?
            $this->success() :
            $this->fail();
    }

    /**
     * 更新角色
     * @RequestMapping(path="updateRoleById", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function updateRoleById()
    {
        $rules = [
            'id' => [
                'required',
                'exists:' . $this->roleModel->getTable() . ',id',
            ],
            'name' => [
                'required',
                Rule::unique($this->roleModel->getTable())->ignore($this->request->input('id')),
            ],
        ];
        $message = [
            'id.required' => '参数:attribute不存在',
            'id.exists' => '参数:attribute的值不存在',
            'name.required' => '参数:attribute不存在',
            'name.unique' => '参数:attribute的值已被占用',
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $roleId = (int)$this->request->input('id');
        $data = ['name' => $this->request->input('name')];
        return $this->roleModel->updateRoleById($roleId, $data) ?
            $this->success() :
            $this->fail();
    }

    /**
     * 更新角色
     * @RequestMapping(path="deleteRoleById", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function deleteRoleById()
    {
        $rules = [
            'id' => [
                'required',
                'exists:' . $this->roleModel->getTable() . ',id',
            ],
        ];
        $message = [
            'id.required' => '参数:attribute不存在',
            'id.exists' => '参数:attribute的值不存在',
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $roleId = (int)$this->request->input('id');
        return $this->roleModel->deleteRoleById($roleId) ?
            $this->success() :
            $this->fail();
    }

}
