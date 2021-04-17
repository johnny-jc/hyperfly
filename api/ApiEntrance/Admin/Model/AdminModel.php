<?php
declare(strict_types=1);

namespace Api\ApiEntrance\Admin\Model;

use Api\ApiActiveRecord\AdminActiveRecord;
use Api\ApiBase\BaseModel;
use Api\ApiTool\Exception\ApiException;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine;

/**
 * Class AdminModel
 * @package Api\ApiEntrance\Admin\Model
 */
class AdminModel extends BaseModel
{
    /**
     * access_token过期时间
     * 默认一周
     */
    const ADMIN_ACCESS_TOKEN_TTL = 7 * 24 * 60 * 60;

    /**
     * @param array $data
     * @return array|bool
     */
    public function login(array $data = [])
    {
        //检验账号是否存在
        $model = AdminActiveRecord::query()->where('username', $data['username'])->first();
        if ($model === null) {
            return $this->setModelError('账号不存在');
        }
        //检验密码是否正确
        if (!password_verify($data['password'], $model->password)) {
            return $this->setModelError('密码错误');
        }
        //如果检验成功，更新数据库access_token以及过期时间
        $accessToken = md5((string)time() . $data['username']);
        $model->access_token = $accessToken;
        $model->access_token_expire_time = date('Y-m-d H:i:s', time() + self::ADMIN_ACCESS_TOKEN_TTL);
        if (!$model->save()) {
            return $this->setModelError('登录失败，请联系管理员');
        }
        //清空旧缓存
        $this->deleteAccessTokenCache($accessToken);
        //设置新缓存
        $adminInfo = json_encode($model->toArray());
        if (!$this->setAccessTokenCache($accessToken, $adminInfo)) {
            throw new ApiException('登录失败');
        }
        //更新缓存过期时间
        if (!$this->refreshAccessToken($accessToken)) {
            throw new ApiException('登录失败');
        }

        $modelArray = $model->toArray();
        unset($modelArray['password']);
        unset($modelArray['status']);
        unset($modelArray['access_token_expire_time']);
        $modelArray['create_time'] = strtotime($modelArray['create_time']);
        $modelArray['update_time'] = strtotime($modelArray['update_time']);

        return $modelArray;
    }

    /**
     * @param string $accessToken
     * @param string $adminInfo
     * @return bool
     */
    private function setAccessTokenCache(string $accessToken, string $adminInfo)
    {
        return $this->redis->set($accessToken, $adminInfo);
    }

    /**
     * @param string $accessToken
     * @return bool
     */
    private function deleteAccessTokenCache(string $accessToken): bool
    {
        $this->redis->del($accessToken);
        return true;
    }

    /**
     * @param string $accessToken
     * @return bool
     */
    private function refreshAccessToken(string $accessToken): bool
    {
        if ($this->redis->exists($accessToken) === 0) {
            return false;
        }
        return ($this->redis->expire($accessToken, self::ADMIN_ACCESS_TOKEN_TTL));
    }

    /**
     * @param string $accessToken
     * @return bool
     */
    public static function logout(string $accessToken): bool
    {
        //退出需要删除access_token缓存
        //退出操作本质上就是让access_token失效
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);
        //删除数据库access_token和重置access_token_expire_time
        //TODO 业务逻辑还不够完善
        AdminActiveRecord::query()->where('access_token', $accessToken)->update(['access_token' => '', 'access_token_expire_time' => '0000-00-00 00:00:00']);

        return $redis->del($accessToken) === 0 ? false : true;
    }

}
