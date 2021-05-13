<?php

declare (strict_types=1);
namespace Api\ApiEntrance\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiEntrance\Admin\Model\AdminModel;
use Hyperf\Validation\Rule;
use Hyperf\Redis\Redis;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
/**
 * 管理员管理控制器
 * Class AdminController
 * @Controller(prefix="Admin/v1/Admin")
 * @package Api\ApiEntrance\Admin\v1\Controller
 */
class AdminController extends BaseController
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
     * @var AdminModel
     */
    private $adminModel;
    /**
     * 登录
     * @RequestMapping(path="login", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function login()
    {
        $rules = ['username' => ['required'], 'password' => ['required']];
        $message = [
            //username start
            'username.required' => '参数:attribute不存在',
            //password start
            'password.required' => '参数:attribute不存在',
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $login = $this->adminModel->login($this->request->all());
        return $login ? $this->success(['user' => $login]) : $this->fail($this->adminModel->getModelError());
    }
    /**
     * 退出
     *
     * 需要传递adminAccessToken。改参数由登录的时候返回的
     * 如果没有该参数，需要传递adminId
     * 退出操作需要把缓存和数据中的access_token字段清空，
     * 并且把数据库中的access_token_expire_time过期时间设置为0000-00-00 00:00:00
     *
     * @RequestMapping(path="logout", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function logout()
    {
        return $this->adminModel->logoutByAdminAccessToken($this->getAdminAccessToken()) ? $this->success() : $this->fail();
    }
    /**
     * 添加管理员
     * @RequestMapping(path="createAdmin", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function createAdmin()
    {
        $rules = ['username' => ['required', 'regex:/^[0-9a-zA-Z_]*$/', 'min:4', 'max:16', 'unique:' . $this->adminModel->getTable() . ',username'], 'password' => ['required', 'regex:/^[0-9a-zA-Z_]*$/', 'min:6', 'max:16'], 'status' => ['required', 'integer', 'in:' . AdminModel::ADMIN_STATUS_ENABLE . ',' . AdminModel::ADMIN_STATUS_DISABLE]];
        $message = [
            //username start
            'username.required' => '参数:attribute不存在',
            'username.regex' => '参数:attribute只能是数字字母下划线',
            'username.min' => '参数:attribute最少4位字符',
            'username.max' => '参数:attribute最多16位字符',
            'username.unique' => '参数:attribute的值已被占用',
            //password start
            'password.required' => '参数:attribute不存在',
            'password.regex' => '参数:attribute只能是数字字母',
            'password.min' => '参数:attribute最少6位字符',
            'password.max' => '参数:attribute最多16位字符',
            //status start
            'status.required' => '参数:attribute不存在',
            'status.integer' => '参数:attribute只能是数字',
            'status.in' => '参数:attribute的值只能是' . AdminModel::ADMIN_STATUS_DISABLE . '或' . AdminModel::ADMIN_STATUS_ENABLE,
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $createData = $this->request->inputs(['username', 'password', 'status']);
        $createData['password'] = password_hash($createData['password'], PASSWORD_DEFAULT);
        return $this->adminModel->createAdmin($createData) ? $this->success() : $this->fail();
    }
    /**
     * 获取管理员列表
     *
     * @RequestMapping(path="getAdminList", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getAdminList()
    {
        $condition = $this->request->all();
        $condition['username'] = $condition['username'] ?? '';
        $condition['offset'] = $condition['offset'] ?? self::OFFSET;
        $condition['limit'] = $condition['limit'] ?? self::LIMIT;
        return $this->success($this->adminModel->getAdminList($condition));
    }
    /**
     * 更新管理员
     *
     * @RequestMapping(path="updateAdminStatus", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function updateAdminStatus()
    {
        $rules = ['id' => ['required', 'exists:' . $this->adminModel->getTable() . ',id'], 'status' => ['required', 'integer', 'in:' . AdminModel::ADMIN_STATUS_DISABLE . ',' . AdminModel::ADMIN_STATUS_ENABLE]];
        $message = ['id.required' => '参数:attribute不存在', 'id.exists' => '参数:attribute的值不存在', 'status.required' => '参数:attribute不存在', 'status.integer' => '参数:attribute的值只能是数字', 'status.in' => '参数:attribute的值只能是' . AdminModel::ADMIN_STATUS_ENABLE . '或' . AdminModel::ADMIN_STATUS_DISABLE];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $condition = ['id' => (int) $this->request->input('id')];
        $updateData = ['status' => (int) $this->request->input('status')];
        return $this->adminModel->updateAdminStatus($condition, $updateData) <= 0 ? $this->fail() : $this->success();
    }
    /**
     * 删除管理员
     * @RequestMapping(path="deleteAdminById", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function deleteAdminById()
    {
        $rules = ['id' => ['required', 'exists:' . $this->adminModel->getTable() . ',id']];
        $message = ['id.required' => '参数:attribute不存在', 'id.exists' => '参数:attribute的值不存在'];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        return $this->adminModel->deleteAdminById((int) $this->request->input('id')) ? $this->success() : $this->fail();
    }
    /**
     * 获取管理员详情
     * @RequestMapping(path="getAdminById", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getAdminById()
    {
        $rules = ['id' => ['required', 'exists:' . $this->adminModel->getTable() . ',id']];
        $message = ['id.required' => '参数:attribute不存在', 'id.exists' => '参数:attribute的值不存在'];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        return $this->success($this->adminModel->getAdminById((int) $this->request->input('id')));
    }
    /**
     * 更新管理员数据
     * @RequestMapping(path="updateAdminById", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function updateAdminById()
    {
        $rules = ['id' => ['required', 'exists:' . $this->adminModel->getTable() . ',id'], 'username' => ['required', 'regex:/^[0-9a-zA-Z_]*$/', 'min:4', 'max:16', Rule::unique($this->adminModel->getTable())->ignore($this->request->input('id'))], 'password' => ['regex:/^[0-9a-zA-Z_]*$/', 'min:6', 'max:16'], 'status' => ['required', 'integer', 'in:' . AdminModel::ADMIN_STATUS_ENABLE . ',' . AdminModel::ADMIN_STATUS_DISABLE]];
        $message = [
            //id start
            'id.required' => '参数:attribute不存在',
            'id.exists' => '参数:attribute不存在',
            //username start
            'username.required' => '参数:attribute不存在',
            'username.regex' => '参数:attribute只能是数字字母下划线',
            'username.min' => '参数:attribute最少4位字符',
            'username.max' => '参数:attribute最多16位字符',
            'username.unique' => '参数:attribute的值已被占用',
            //password start
            'password.regex' => '参数:attribute只能是数字字母',
            'password.min' => '参数:attribute最少6位字符',
            'password.max' => '参数:attribute最多16位字符',
            //status start
            'status.required' => '参数:attribute不存在',
            'status.integer' => '参数:attribute只能是数字',
            'status.in' => '参数:attribute只能是' . AdminModel::ADMIN_STATUS_DISABLE . '或' . AdminModel::ADMIN_STATUS_ENABLE,
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $id = (int) $this->request->input('id', null);
        $updateData = $this->request->inputs(['username', 'password', 'status']);
        if ($updateData['password'] === null) {
            unset($updateData['password']);
        } else {
            $updateData['password'] = password_hash($updateData['password'], PASSWORD_DEFAULT);
        }
        return $this->adminModel->updateAdminById($id, $updateData) ? $this->success() : $this->fail($this->adminModel->getModelError());
    }
}