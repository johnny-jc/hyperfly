<?php
declare(strict_types=1);

namespace Api\ApiApp\Admin\Model;

use Api\ApiActiveRecord\AdminActiveRecord;
use Api\ApiActiveRecord\AdminMenuActiveRecord;
use Api\ApiActiveRecord\RoleAdminActiveRecord;
use Api\ApiBase\BaseModel;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;

use Hyperf\Di\Annotation\Inject;

/**
 * 管理员模型
 * Class AdminModel
 * @package Api\ApiApp\Admin\Model
 */
class AdminModel extends BaseModel
{
    /**
     * access_token过期时间
     * 默认一周
     */
    const ADMIN_ACCESS_TOKEN_TTL = 7 * 24 * 60 * 60;

    /**
     * access_token_expire_time默认值
     */
    const DEFAULT_ACCESS_TOKEN_EXPIRE_TIME = '0000-00-00 00:00:00';

    /**
     * 管理员状态：正常
     */
    const ADMIN_STATUS_ENABLE = 1;

    /**
     * 管理员状态：禁用
     */
    const ADMIN_STATUS_DISABLE = 0;

    /**
     * 最后一次登录的access_token
     * 管理员access_token缓存前缀
     */
    const ADMIN_ACCESS_TOKEN_REDIS_KEY_PREFIX = 'ADMIN_ACCESS_TOKEN_UNIQUE_';

    /**
     * 开启多端登录
     * 用来保存所有所有客户端access_token的缓存前缀
     */
    const ADMIN_ACCESS_TOKEN_ALL_CACHE_REDIS_KEY_PREFIX = 'ADMIN_ALL_ACCESS_TOKEN_';

    /**
     * 默认的超级管理员账号
     */
    const DEFAULT_SUPER_ADMIN_USERNAME = 'super_admin';

    /**
     * @Inject
     * @var AdminMenuModel
     */
    private $adminMenuModal;

    /**
     * @return string
     */
    public function getTable()
    {
        return (new AdminActiveRecord())->getTable();
    }

    /**
     * @param array $data
     * @return array|bool
     */
    public function login(array $data = [])
    {
        //检验账号是否存在
        if (($model = $this->getLoginModelByUsername($data['username'])) === null) {
            return $this->setModelError('账号不存在或已被禁用');
        }

        //检验密码是否正确
        if (!$this->verifyPassword($data['password'], $model->password)) {
            return $this->setModelError('密码错误');
        }

        //生成access_token
        $accessToken = md5((string)time() . $data['username']);

        //更新数据库数据
        if (($model = $this->updateAfterLogin($model, $accessToken)) === false) {
            return $this->setModelError('登录失败，请联系管理员');
        }

        $modelArray = $model->toArray();

        //判断是否是单客户端登录
        if ($this->isSingleClientLogin()) {
            //单个客户端登录，删除旧access_token缓存
            $this->deleteOldAccessTokenByAdminId((int)$modelArray['id']);
        } else {
            //多客户端登录，保存access_token
            $this->setMultiAccessTokenAfterLogin((int)$modelArray['id'], $accessToken);
        }

        //设置新缓存
        if (!$this->setAccessTokenCache($accessToken, $this->getAdminCacheJson($modelArray))) {
            $this->throwApiException('登录失败，错误：保存缓存失败');
        }

        //设置管理员唯一的access_token缓存
        if (!$this->setAccessTokenUniqueCache($modelArray['id'], $accessToken)) {
            $this->throwApiException('登录失败，错误：设置管理员唯一缓存失败');
        }

        //返回数据
        unset($modelArray['password']);
        unset($modelArray['status']);
        unset($modelArray['access_token_expire_time']);
        $modelArray['create_time'] = strtotime($modelArray['create_time']);
        $modelArray['update_time'] = strtotime($modelArray['update_time']);

        return $modelArray;
    }

    /**
     * 获取要添加缓存的管理员Json数据
     * @param array $admin
     * @return false|string
     */
    public function getAdminCacheJson(array $admin)
    {
        return json_encode([
            'id' => $admin['id'],
        ]);
    }

    /**
     * 获取登录后的用户Model
     * @param string $username
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|null
     */
    public function getLoginModelByUsername(string $username)
    {
        return AdminActiveRecord::query()
            ->where('username', '=', $username)
            ->where('status', '=', self::ADMIN_STATUS_ENABLE)
            ->first();
    }

    /**
     * 校验密码
     * @param string $password
     * @param string $passwordHash
     * @return false|string|null
     */
    public function verifyPassword(string $password, string $passwordHash)
    {
        return password_verify($password, $passwordHash);
    }

    /**
     * 登录后更新数据access_token以及过期时间
     * @param Model $model
     * @param string $accessToken
     * @return bool|Model
     */
    public function updateAfterLogin(AdminActiveRecord $model, string $accessToken)
    {
        $model->access_token = $accessToken;
        $model->access_token_expire_time = date('Y-m-d H:i:s', time() + self::ADMIN_ACCESS_TOKEN_TTL);
        return $model->save() ? $model : false;
    }

    /**
     * 删除旧缓存
     * @param int $adminId
     * @return bool
     */
    public function deleteOldAccessTokenByAdminId(int $adminId)
    {
        if (($oldAccessToken = $this->getAccessTokenUniqueCache($adminId)) !== false) {
            //删除旧缓存
            $this->deleteAccessTokenCache($oldAccessToken);
        }
        return true;
    }

    /**
     * 如果是多端登录，保存多个access_token
     * @param int $adminId
     * @param string $accessToken
     * @return bool
     */
    public function setMultiAccessTokenAfterLogin(int $adminId, string $accessToken)
    {
        if (!$this->setMultiAccessTokenCache($adminId, $accessToken)) {
            $this->throwApiException('登录失败，错误：保存多客户端access_token缓存失败');
        }
        return true;
    }

    /**
     * 多客户端登录
     * 设置所有客户端的access_token缓存
     * @param int $adminId
     * @param string $accessToken
     * @return bool
     */
    public function setMultiAccessTokenCache(int $adminId, string $accessToken)
    {
        $key = self::ADMIN_ACCESS_TOKEN_ALL_CACHE_REDIS_KEY_PREFIX . (string)$adminId;
        if (($cache = $this->redis->get($key)) === false) {
            $cacheArr = [];
        } else {
            $cacheArr = json_decode($cache, true);
        }
        $cacheArr[] = $accessToken;
        return ($this->redis->set($key, json_encode($cacheArr)) && $this->redis->expire($key, self::ADMIN_ACCESS_TOKEN_TTL));
    }

    /**
     * 多客户端登录
     * 获取所有客户端的access_token缓存
     * @param int $adminId
     * @return bool|mixed
     */
    public function getMultiAccessTokenCache(int $adminId)
    {
        $key = self::ADMIN_ACCESS_TOKEN_ALL_CACHE_REDIS_KEY_PREFIX . (string)$adminId;
        if (($cache = $this->redis->get($key)) === false) {
            return false;
        }
        return json_decode($cache, true);
    }

    /**
     * 多客户端登录
     * 删除所有客户端access_token缓存
     * @param int $adminId
     * @return bool
     */
    public function deleteMultiAccessTokenCache(int $adminId)
    {
        //循环删除所有access_token
        if (($accessTokenArr = $this->getMultiAccessTokenCache($adminId)) === false) {
            return true;
        }
        foreach ($accessTokenArr as $accessToken) {
            $this->deleteAccessTokenCache($accessToken);
        }
        //删除保存所有access_token的缓存
        $key = self::ADMIN_ACCESS_TOKEN_ALL_CACHE_REDIS_KEY_PREFIX . (string)$adminId;
        $this->redis->del($key);
        return true;
    }

    /**
     * 设置管理员唯一的access_token缓存
     * @param int $adminId
     * @param string $accessToken
     * @return bool
     */
    public function setAccessTokenUniqueCache(int $adminId, string $accessToken)
    {
        $key = self::ADMIN_ACCESS_TOKEN_REDIS_KEY_PREFIX . (string)$adminId;
        return ($this->redis->set($key, $accessToken) && $this->redis->expire($key, self::ADMIN_ACCESS_TOKEN_TTL));
    }

    /**
     * 判断管理员唯一的access_token缓存是否存在
     * 用于判断是否是单个客户端登录
     * @param int $adminId
     * @return bool
     */
    public function hasAccessTokenUniqueCache(int $adminId)
    {
        $key = self::ADMIN_ACCESS_TOKEN_REDIS_KEY_PREFIX . (string)$adminId;
        return $this->redis->exists($key) !== 0;
    }

    /**
     * 获取管理员唯一的access_token缓存
     * @param int $adminId
     * @return bool|mixed|string
     */
    public function getAccessTokenUniqueCache(int $adminId)
    {
        $key = self::ADMIN_ACCESS_TOKEN_REDIS_KEY_PREFIX . (string)$adminId;
        return $this->redis->get($key);
    }

    /**
     * 删除管理员唯一的access_token缓存
     * @param int $adminId
     * @return bool
     */
    public function deleteAccessTokenUniqueCache(int $adminId)
    {
        //删除access_token
        $accessToken = $this->getAccessTokenUniqueCache($adminId);
        if ($accessToken === false) {
            return false;
        }
        $this->deleteAccessTokenCache($this->getAccessTokenUniqueCache($adminId));
        //删除保存access_token的缓存
        $key = self::ADMIN_ACCESS_TOKEN_REDIS_KEY_PREFIX . (string)$adminId;
        $this->redis->del($key);

        return true;
    }

    /**
     * 刷新管理员唯一的access_token缓存过期时间
     * @param int $adminId
     * @return bool
     */
    public function refreshAccessTokenUniqueCache(int $adminId)
    {
        $key = self::ADMIN_ACCESS_TOKEN_REDIS_KEY_PREFIX . (string)$adminId;
        if ($this->redis->exists($key) === 0) {
            return false;
        }
        return $this->redis->expire($key, self::ADMIN_ACCESS_TOKEN_TTL);
    }

    /**
     * 设置access_token缓存
     * @param string $accessToken
     * @param string $adminInfo
     * @return bool
     */
    public function setAccessTokenCache(string $accessToken, string $adminInfo)
    {
        return ($this->redis->set($accessToken, $adminInfo) && $this->redis->expire($accessToken, self::ADMIN_ACCESS_TOKEN_TTL));
    }

    /**
     * 获取access_token缓存
     * @param string $accessToken
     * @return bool|mixed
     */
    public function getAccessTokenCache(string $accessToken)
    {
        $admin = json_decode($this->redis->get($accessToken), true);
        return empty($admin) ? false : $admin;
    }

    /**
     * 判断access_token是否存在
     * @param string $accessToken
     * @return bool
     */
    public function hasAccessTokenCache(string $accessToken)
    {
        return $this->redis->exists($accessToken) !== 0;
    }

    /**
     * 删除access_token缓存
     * @param string $accessToken
     * @return bool
     */
    public function deleteAccessTokenCache(string $accessToken): bool
    {
        $this->redis->del($accessToken);
        return true;
    }

    /**
     * 刷新access_token缓存
     * @param string $accessToken
     * @return bool
     */
    public function refreshAccessTokenCache(string $accessToken): bool
    {
        if ($this->redis->exists($accessToken) === 0) {
            return false;
        }
        return ($this->redis->expire($accessToken, self::ADMIN_ACCESS_TOKEN_TTL));
    }

    /**
     * 退出登录
     * @param string $adminAccessToken
     * @return bool|string
     */
    public function logoutByAdminAccessToken(string $adminAccessToken)
    {
        //判断是否是单客户端登录，如果不是，退出只需要删除缓存
        if (!$this->isSingleClientLogin()) {
            //删除缓存
            $this->deleteAccessTokenCache($adminAccessToken);
            return true;
        }
        //如果是单客户端登录，还需要删除数据库access_token数据
        $model = AdminActiveRecord::where('access_token', '=', $adminAccessToken)->first();
        if ($model === null) {
            return $this->setModelError('管理员数据不存在');
        }
        $model->access_token = '';
        $model->access_token_expire_time = self::DEFAULT_ACCESS_TOKEN_EXPIRE_TIME;
        if (!$model->save()) {
            $this->throwApiException('退出失败：更新管理员access_token失败');
        }

        return true;
    }

    /**
     * 添加管理员
     * @param array $data
     * @return bool
     */
    public function createAdmin(array $data = [])
    {
        try {
            AdminActiveRecord::create($data);
            return true;
        } catch (\Exception $e) {
            $this->throwApiException('添加管理员失败，错误：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取管理员列表
     * @param array $condition
     * @return array
     */
    public function getAdminList(array $condition = [])
    {
        $model = AdminActiveRecord::select(['id', 'username', 'create_time', 'update_time', 'status', 'access_token', 'access_token_expire_time']);
        $model = $model->andFilterWhere('username', 'like', '%' . $condition['username'] . '%')
            ->andFilterWhere('id', '=', $condition['id'])
            ->orderBy('create_time');
        if ($this->isHideSuperAdmin()) {
            $model = $model->where('username', '!=', self::DEFAULT_SUPER_ADMIN_USERNAME);
        }
        $count = $model->count('id');
        $query = $model->offset($condition['offset'])->limit($condition['limit']);
        return [
            'count' => $count,
            'list' => $query->get()->toArray(),
        ];
    }

    /**
     * 更新管理员状态
     * @param array $condition
     * @param array $data
     * @return int
     */
    public function updateAdminStatus(array $condition = [], array $data = [])
    {
        //修改管理员的状态
        $model = AdminActiveRecord::where('id', '=', $condition['id'])->first();
        if ($model === null) {
            return $this->setModelError('查询不到管理员数据');
        }
        if ($model->username === self::DEFAULT_SUPER_ADMIN_USERNAME && $data['status'] === self::ADMIN_STATUS_DISABLE) {
            return $this->setModelError('该账号禁止禁用');
        }
        //如是禁用，则强制退出登录[包括多端登录]
        if ($data['status'] === self::ADMIN_STATUS_DISABLE) {
            if ($this->hasAccessTokenUniqueCache($condition['id'])) {
                if (!$this->deleteAccessTokenUniqueCache($condition['id'])) {
                    $this->throwApiException('更新管理员状态失败，错误：删除缓存失败');
                }
            }
            if (!$this->getMultiAccessTokenCache($condition['id'])) {
                if (!$this->deleteMultiAccessTokenCache($condition['id'])) {
                    $this->throwApiException('更新管理员状态失败，错误：删除缓存失败');
                }
            }
        }

        $model->status = $data['status'];
        if (!$model->save()) {
            return $this->setModelError('更新管理员状态失败');
        }
        return true;
    }

    /**
     * 删除管理员
     * @param int $id
     * @return int
     */
    public function deleteAdminById(int $id)
    {
        $model = AdminActiveRecord::where('id', '=', $id)->first();
        if ($model === null) {
            return $this->setModelError('查询不到管理员数据');
        }
        if ($model->username === self::DEFAULT_SUPER_ADMIN_USERNAME) {
            return $this->setModelError('该账号不可删除');
        }
        Db::beginTransaction();
        try {
            //删除管理员
            AdminActiveRecord::where('id', '=', $id)->delete();
            //删除管理员关联的菜单
            AdminMenuActiveRecord::where('admin_id', '=', $id)->delete();
            //删除管理员关联的角色
            RoleAdminActiveRecord::where('admin_id', '=', $id)->delete();
            //删除最后登录的access_token
            $this->deleteAccessTokenUniqueCache($id);
            //删除多客户端登录后保存的所有access_token
            $this->deleteMultiAccessTokenCache($id);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            $this->throwApiException('删除管理员失败，错误：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取管理员详情
     * @param int $id
     * @return array
     */
    public function getAdminById(int $id)
    {
        $fields = [
            'id',
            'username',
            'status',
            'create_time',
            'update_time',
            'access_token',
            'access_token_expire_time'
        ];
        return AdminActiveRecord::where('id', '=', $id)->first($fields)->toArray();
    }

    /**
     * 更新管理员数据
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateAdminById(int $id, array $data = [])
    {
        $model = AdminActiveRecord::where('id', '=', $id)->first();
        if ($model === null) {
            return $this->setModelError('管理员数据不存在');
        }
        if ($model->username === self::DEFAULT_SUPER_ADMIN_USERNAME && $data['status'] == self::ADMIN_STATUS_DISABLE) {
            return $this->setModelError('该账号禁止禁用');
        }
        return $model->update($data);
    }

    /**
     * 校验是否是单个客户端登录
     * @param int $adminId
     * @return bool
     */
    public function isSingleClientLogin()
    {
        //TODO 由配置实现
        return false;
    }

    /**
     * 是否隐藏超级管理员账号
     * 隐藏后将不显示在管理员列表
     * @return bool
     */
    public function isHideSuperAdmin()
    {
        //TODO 由配置实现
        return false;
    }

    /**
     * 获取菜单
     * @param int $adminId
     * @return array
     */
    public function getMenu(int $adminId)
    {
        return $this->adminMenuModal->getAdminMenu($adminId);
    }
}
