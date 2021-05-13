<?php
declare(strict_types=1);

namespace Api\ApiApp\Admin\Model;

use Api\ApiActiveRecord\PermissionActiveRecord;
use Api\ApiBase\BaseModel;
use Hyperf\DbConnection\Db;

use Hyperf\Di\Annotation\Inject;

/**
 * 权限模型
 * Class PermissionModel
 * @package Api\ApiApp\Admin\Model
 */
class PermissionModel extends BaseModel
{
    /**
     * 接口白名单
     * @param string $requestPath
     * @return bool
     */
    public function isInAuthWhitelist(string $requestPath)
    {
        $whitelist = [
            '/Admin/v1/Admin/login',
        ];
        return in_array($requestPath, $whitelist);
    }

    /**
     * 接口白名单
     * @param string $requestPath
     * @return bool
     */
    public function isInPermissionWhitelist(string $requestPath)
    {
        $whitelist = [
            '/Admin/v1/Admin/login',
            '/Admin/v1/Admin/isAccessTokenEffective',
        ];
        return in_array($requestPath, $whitelist);
    }

    /**
     * 获取所有权限
     * @param array $condition
     * @return array
     */
    public function getAllPermission(array $condition)
    {
        return PermissionActiveRecord::andFilterWhere('api_app', 'like', '%' . $condition['apiApp'] . '%')
            ->andFilterWhere('api_version', '=', $condition['apiVersion'])
            ->andFilterWhere('api_class', 'like', '%' . $condition['apiClass'] . '%')
            ->andFilterWhere('api_function', 'like', '%' . $condition['apiFunction'] . '%')
            ->orderBy('api_app', 'asc')
            ->orderBy('api_version', 'asc')
            ->orderBy('api_class', 'asc')
            ->orderBy('api_function', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * 获取权限列表
     * @param array $condition
     * @return array
     */
    public function getPermissionList(array $condition)
    {
        $model = PermissionActiveRecord::andFilterWhere('api_app', 'like', '%' . $condition['apiApp'] . '%')
            ->andFilterWhere('api_version', '=', $condition['apiVersion'])
            ->andFilterWhere('api_class', 'like', '%' . $condition['apiClass'] . '%')
            ->andFilterWhere('api_function', 'like', '%' . $condition['apiFunction'] . '%');
        $count = $model->count('api_route');
        $list = $model->offset($condition['offset'])->limit($condition['limit'])->get()->toArray();
        return [
            'count' => $count,
            'list' => $list,
        ];
    }

    /**
     * 添加权限
     * @param array $data
     * @param string $apiApp
     * @param string $apiVersion
     * @return bool
     */
    public function createPermission(array $data, string $apiApp, string $apiVersion)
    {
        Db::beginTransaction();
        try {
            PermissionActiveRecord::where('api_app', '=', $apiApp)
                ->where('api_version', '=', $apiVersion)
                ->delete();
            PermissionActiveRecord::insert($data);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            $this->throwApiException('添加权限失败，错误：' . $e->getMessage());
            return false;
        }
    }

}
