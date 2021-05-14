<?php

declare(strict_types=1);

namespace Api\ApiService\Middleware;

use Api\ApiActiveRecord\RoleAdminActiveRecord;
use Api\ApiActiveRecord\RolePermissionActiveRecord;
use Api\ApiBase\Base;
use Api\ApiApp\Admin\Model\AdminModel;
use Api\ApiApp\Admin\Model\PermissionModel;
use Api\ApiApp\Admin\Model\RoleModel;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

use Hyperf\Di\Annotation\Inject;

/**
 * 验证RBAC权限中间件
 *
 * Class PermissionMiddleware
 * @package Api\ApiService\Middleware
 */
class PermissionMiddleware extends Base implements MiddlewareInterface
{

    /**
     * @Inject
     * @var PermissionModel
     */
    private $permissionModel;

    /**
     * @Inject
     * @var AdminModel
     */
    private $adminModel;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->permissionModel->isInPermissionWhitelist($this->request->getUri()->getPath())) {
            return $handler->handle($request);
        }
        if (($authentication = $this->getAuthenticationCode()) === false) {
            return $this->requestFail('没有授权码');
        }
        if (($admin = $this->adminModel->getAccessTokenCache($authentication)) === false) {
            return $this->requestFail('获取不到授权相关数据');
        }
        $adminRole = $this->getAdminRole($admin['id']);
        if ($adminRole === false) {
            return $this->requestFail('没有权限');
        }
        $rolePermission = $this->getRolePermission($adminRole);
        if (!$this->hasPermission($this->request->getUri()->getPath(), $rolePermission)) {
            return $this->requestFail('没有权限');
        }
        return $handler->handle($request);
    }

    /**
     * 获取头部授权码
     * @return bool|string
     */
    private function getAuthenticationCode()
    {
        return $this->getHeaderAuthenticationCode() === '' ? false : $this->getHeaderAuthenticationCode();
    }

    /**
     * 获取管理员id
     * @param string $accessToken
     * @return mixed
     */
    private function getAdminId(string $accessToken)
    {
        $adminInfo = $this->redis->get($accessToken);
        $adminInfoArr = json_decode($adminInfo, true);
        return $adminInfoArr['id'];
    }

    /**
     * 获取角色id
     *
     * @param int $adminId
     * @return array|bool
     */
    private function getAdminRole(int $adminId)
    {
        $adminRole = RoleAdminActiveRecord::where('admin_id', '=', $adminId)
            ->with(['getRole' => function ($query) {
                $query->where('status', '=', RoleModel::STATUS_ENABLE);
            }])
            ->get()
            ->toArray();
        $roleIds = [];
        foreach ($adminRole as $k => $v) {
            if (empty($v['get_role'])) {
                continue;
            }
            $roleIds[] = $v['get_role']['id'];
        }
        if (empty($roleIds)) {
            return false;
        }
        return $roleIds;
    }

    /**
     * 获取角色拥有的权限
     *
     * @param array $roleIds
     * @return array|bool
     */
    private function getRolePermission(array $roleIds)
    {
        $rolePermission = RolePermissionActiveRecord::whereIn('role_id', $roleIds)
            ->get()
            ->toArray();
        if (empty($rolePermission)) {
            return [];
        }
        $allowedPermission = [];
        foreach ($rolePermission as $k => $v) {
            $allowedPermission[] = $v['api_route'];
        }
        return $allowedPermission;
    }

    /**
     * 判断是否拥有权限
     *
     * @param string $requestUri
     * @param array $rolePermission
     * @return bool
     */
    private function hasPermission(string $requestUri, array $rolePermission)
    {
        return in_array($requestUri, $rolePermission);
    }
}
