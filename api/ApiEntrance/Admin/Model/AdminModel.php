<?php
declare(strict_types=1);

namespace Api\ApiEntrance\Admin\Model;

use Api\ApiActiveRecord\AdminActiveRecord;
use Api\ApiBase\BaseModel;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

/**
 * Class AdminModel
 * @package Api\ApiEntrance\Admin\Model
 */
class AdminModel extends BaseModel
{

    /**
     * @param array $data
     * @return array|bool
     */
    public static function login(array $data = [])
    {
        $model = AdminActiveRecord::query()->where('username', $data['username'])->first();
        if ($model === null) {
            return false;
        }
        $md5String = (string)time() . $data['username'];
        $accessToken = md5($md5String);

        //账号密码验证失败返回false
        if (!password_verify($data['password'], $model->password)) {
            return false;
        }

        //如果成功，更新access_token并且更新时间
        $model->access_token = $accessToken;
        $model->access_token_expire_time = date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60);
        if (!$model->save()) {
            return false;
        }

        //设置缓存、清空旧缓存、更新有效时间
        self::setAccessTokenCache($model->toArray());

        $modelArray = $model->toArray();
        unset($modelArray['password']);
        unset($modelArray['status']);
        unset($modelArray['access_token_expire_time']);
        $modelArray['create_time'] = strtotime($modelArray['create_time']);
        $modelArray['update_time'] = strtotime($modelArray['update_time']);

        return $modelArray;
    }

    /**
     * 设置access_token缓存
     * @param array $user
     */
    private static function setAccessTokenCache(array $user)
    {
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);
        $accessTokenRedisKey = $user['access_token'];
        //清空旧缓存
        $redis->del($accessTokenRedisKey);
        //设置access_token缓存并更新缓存时间
        unset($user['password']);
        $redis->set($accessTokenRedisKey, json_encode($user));
        $redis->expire($accessTokenRedisKey, 7 * 24 * 60 * 60);
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
