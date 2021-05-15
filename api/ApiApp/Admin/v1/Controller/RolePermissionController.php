<?php

declare(strict_types=1);

namespace Api\ApiApp\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiApp\Admin\Model\PermissionModel;
use Api\ApiApp\Admin\Model\RoleModel;
use Api\ApiApp\Admin\Model\RolePermissionModel;

use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use Api\ApiService\Middleware\AuthMiddleware;
use Api\ApiService\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * 角色权限管理控制器
 * Class RolePermissionController
 * @package Api\ApiApp\Admin\v1\Controller
 * @Controller(prefix="Admin/v1/RolePermission")
 * @Middlewares({
 *     @Middleware(AuthMiddleware::class),
 *     @Middleware(PermissionMiddleware::class)
 *     })
 */
class RolePermissionController extends BaseController
{
    /**
     * @Inject
     * @var PermissionModel
     */
    private $permissionModel;

    /**
     * @Inject
     * @var RolePermissionModel
     */
    private $rolePermissionModel;

    /**
     * @Inject
     * @var PermissionController
     */
    private $permissionController;

    /**
     * @Inject
     * @var RoleModel
     */
    private $roleModel;

    /**
     * 删除权限
     * @RequestMapping(path="deleteRolePermissionByRoutes", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function deleteRolePermissionByRoutes()
    {
        $rules = [
            'roleId' => [
                'required',
                'exists:' . $this->roleModel->getTable() . ',id',
            ],
            'apiRoutes' => [
                'required',
            ],
        ];
        $message = [
            //role id
            'roleId.required' => '参数:attribute不存在',
            'roleId.exists' => '参数:attribute的值不存在',
            //apiRoutes
            'apiRoutes.required' => '参数:attribute不存在',
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $requestData = $this->request->all();
        $roleId = (int)$requestData['roleId'];
        $apiRoutes = json_decode($requestData['apiRoutes'], true);
        if (empty($apiRoutes)) {
            return $this->success();
        }
        return $this->rolePermissionModel->deleteRolePermission($roleId, $apiRoutes) ?
            $this->success() :
            $this->fail($this->rolePermissionModel->getModelError());
    }

    /**
     * 分配权限
     * @RequestMapping(path="assignRolePermission", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function assignRolePermission()
    {
        $rules = [
            'roleId' => [
                'required',
                'exists:' . $this->roleModel->getTable() . ',id',
            ],
            'apiRoutes' => [
                'required',
            ],
        ];
        $message = [
            //role id
            'roleId.required' => '参数:attribute不存在',
            'roleId.exists' => '参数:attribute的值不存在',
            //apiRoutes
            'apiRoutes.required' => '参数:attribute不存在',
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }

        $requestData = $this->request->all();
        $roleId = (int)$requestData['roleId'];
        $apiRoutes = json_decode($requestData['apiRoutes'], true);
        if (empty($apiRoutes)) {
            return $this->success();
        }
        return $this->rolePermissionModel->assignRolePermission($roleId, $apiRoutes) ?
            $this->success() :
            $this->fail($this->rolePermissionModel->getModelError());
    }

    /**
     * 获取未分配权限以及已分配权限
     * @RequestMapping(path="getRolePermissionByRoleId", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getRolePermissionByRoleId()
    {
        $roleId = $this->request->input('roleId');
        if ($roleId === null) {
            return $this->fail('参数roleId不存在');
        }
        $condition = $this->request->all();
        if (!isset($condition['apiApp']) || empty($condition['apiApp'])) {
            $condition['apiApp'] = $this->permissionController->getApiApp();
        }
        if (!isset($condition['apiVersion']) || empty($condition['apiVersion'])) {
            $condition['apiVersion'] = $this->permissionController->getApiVersion();
        }
        $condition['apiClass'] = $condition['apiClass'] ?? '';
        $condition['apiFunction'] = $condition['apiFunction'] ?? '';
        $allPermission = $this->permissionModel->getAllPermission($condition);
        $rolePermission = $this->rolePermissionModel->getRolePermission(['id' => $roleId]);
        $returnData = [
            'rolePermission' => $rolePermission,
            'noAssignPermission' => $this->getNoAssignPermission($allPermission, $rolePermission),
        ];
        return $this->success($returnData);
    }

    /**
     * 获取未分配的权限
     * @param array $allPermission
     * @param array $rolePermission
     * @return array
     */
    private function getNoAssignPermission(array $allPermission, array $rolePermission)
    {
        $allPermissionArr = [];
        $rolePermissionArr = [];
        foreach ($allPermission as $k => $v) {
            $allPermissionArr[$v['api_route']] = $v;
        }
        foreach ($rolePermission as $k => $v) {
            $rolePermissionArr[$v['api_route']] = $v;
        }
        $diff = array_diff_key($allPermissionArr, $rolePermissionArr);
        $noAssignPermission = [];
        foreach ($diff as $k => $v) {
            $noAssignPermission[] = $v;
        }
        return $noAssignPermission;
    }

}
