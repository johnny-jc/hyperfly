<?php

declare (strict_types=1);
namespace Api\ApiApp\Admin\v1\Controller;

use Api\ApiApp\Admin\Model\AdminMenuModel;
use Api\ApiBase\BaseController;
use Api\ApiApp\Admin\Model\AdminModel;
use Hyperf\Validation\Rule;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
/**
 * 管理员管理控制器
 * Class AdminController
 * @Controller(prefix="Admin/v1/Admin")
 * @package Api\ApiApp\Admin\v1\Controller
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
     * @Inject
     * @var AdminMenuModel
     */
    private $adminMenuModel;
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
     * 退出登录
     * @RequestMapping(path="logout", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function logout()
    {
        return $this->adminModel->logoutByAdminAccessToken($this->getAdminAccessToken()) ? $this->success() : $this->fail($this->adminModel->getModelError());
    }
    /**
     * 获取菜单
     * @RequestMapping(path="getMenu", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getMenu()
    {
        if (($adminCache = $this->adminModel->getAccessTokenCache($this->getAdminAccessToken())) === false) {
            return $this->fail('获取菜单失败');
        }
        if (!isset($adminCache['id'])) {
            return $this->fail('获取菜单失败');
        }
        return $this->success($this->adminModel->getMenu((int) $adminCache['id']));
    }
    /**
     * 验证access_token是否有效
     * @RequestMapping(path="isAccessTokenEffective", method="post")
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function isAccessTokenEffective()
    {
        if (($accessToken = $this->getAdminAccessToken()) === '') {
            return $this->requestUnauthorized();
        }
        if (!$this->adminModel->hasAccessTokenCache($accessToken)) {
            return $this->requestUnauthorized();
        }
        return $this->success();
    }
    /**
     * 获取头部access_token
     * @return string
     */
    private function getAdminAccessToken()
    {
        return $this->getHeaderAuthenticationCode();
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
        $condition['id'] = $condition['id'] ?? '';
        $condition['offset'] = $condition['offset'] ?? self::OFFSET;
        $condition['limit'] = $condition['limit'] ?? self::LIMIT;
        return $this->success($this->adminModel->getAdminList($condition));
    }
    /**
     * 更新管理员
     *
     * @RequestMapping(path="updateAdminStatusById", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function updateAdminStatusById()
    {
        $rules = ['id' => ['required', 'exists:' . $this->adminModel->getTable() . ',id'], 'status' => ['required', 'integer', 'in:' . AdminModel::ADMIN_STATUS_DISABLE . ',' . AdminModel::ADMIN_STATUS_ENABLE]];
        $message = ['id.required' => '参数:attribute不存在', 'id.exists' => '参数:attribute的值不存在', 'status.required' => '参数:attribute不存在', 'status.integer' => '参数:attribute的值只能是数字', 'status.in' => '参数:attribute的值只能是' . AdminModel::ADMIN_STATUS_ENABLE . '或' . AdminModel::ADMIN_STATUS_DISABLE];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $condition = ['id' => (int) $this->request->input('id')];
        $updateData = ['status' => (int) $this->request->input('status')];
        return $this->adminModel->updateAdminStatus($condition, $updateData) ? $this->success() : $this->fail($this->adminModel->getModelError());
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
        return $this->adminModel->deleteAdminById((int) $this->request->input('id')) ? $this->success() : $this->fail($this->adminModel->getModelError());
    }
    /**
     * 获取管理员详情
     * @RequestMapping(path="getAdminDetailById", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getAdminDetailById()
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
        $isLogout = false;
        if ($updateData['password'] === null) {
            unset($updateData['password']);
        } else {
            $updateData['password'] = password_hash($updateData['password'], PASSWORD_DEFAULT);
            $isLogout = true;
        }
        if ($updateData['status'] == AdminModel::ADMIN_STATUS_DISABLE) {
            $isLogout = true;
        }
        if (!$this->adminModel->updateAdminById($id, $updateData)) {
            return $this->fail($this->adminModel->getModelError());
        }
        //如果修改了密码获取禁用，则强制退出
        if ($isLogout) {
            //删除最后登录的access_token
            $this->adminModel->deleteAccessTokenUniqueCache($id);
            //删除多客户端登录后保存的所有access_token
            $this->adminModel->deleteMultiAccessTokenCache($id);
        }
        return $this->success();
    }
    /**
     * 获取管理员信息
     * @RequestMapping(path="getAdminByAccessToken", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getAdminByAccessToken()
    {
        if (($adminCacheArr = $this->adminModel->getAccessTokenCache($this->getHeaderAuthenticationCode())) === false) {
            return $this->fail();
        }
        $adminId = (int) $adminCacheArr['id'];
        $adminInfo = $this->adminModel->getAdminById($adminId);
        return $this->success(['admin' => ['username' => $adminInfo['username']]]);
    }
    /**
     * 更新管理员密码
     * @RequestMapping(path="updateAdminPasswordByAccessToken", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function updateAdminPasswordByAccessToken()
    {
        $rules = ['password' => ['required', 'regex:/^[0-9a-zA-Z_]*$/', 'min:6', 'max:16']];
        $message = [
            //password start
            'password.required' => '参数:attribute不存在',
            'password.regex' => '参数:attribute只能是数字字母',
            'password.min' => '参数:attribute最少6位字符',
            'password.max' => '参数:attribute最多16位字符',
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        if (($adminCacheArr = $this->adminModel->getAccessTokenCache($this->getHeaderAuthenticationCode())) === false) {
            return $this->fail();
        }
        $adminId = (int) $adminCacheArr['id'];
        $updateData = ['password' => password_hash($this->request->input('password'), PASSWORD_DEFAULT)];
        if (!$this->adminModel->updateAdminById($adminId, $updateData)) {
            return $this->fail();
        }
        //删除最后登录的access_token
        $this->adminModel->deleteAccessTokenUniqueCache($adminId);
        //删除多客户端登录后保存的所有access_token
        $this->adminModel->deleteMultiAccessTokenCache($adminId);
        return $this->success();
    }
}