<?php
declare(strict_types=1);

namespace Api\ApiEntrance\Admin\v1\Controller;

use Api\ApiBase\ApiBaseController;
use Api\ApiBaseModel\RbacAdminModel;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * @package Api\ApiEntrance\Admin\v1\Controller
 * @Controller(prefix="admin/v1/admin")
 */
class AdminController extends ApiBaseController
{
    /**
     * @Inject
     * @var RbacAdminModel
     */
    private $rbacAdminModel;

    /**
     * @param RequestInterface $request
     * @RequestMapping(path="login", methods="post")
     * @return string
     */
    public function login(RequestInterface $request)
    {
        $login = $this->rbacAdminModel->login($request->all());
        return $login ?
            $this->success(['user' => $login]) :
            $this->fail('账号或密码错误');
    }

    /**
     * @param RequestInterface $request
     * @RequestMapping(path="logout", methods="post")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function logout(RequestInterface $request)
    {
        return $this->rbacAdminModel->logout((string)$request->input('admin_access_token'))
            ? $this->success()
            : $this->fail();
    }

    /**
     * @RequestMapping(path="getMenu", methods="post")
     */
    public function getMenu()
    {
        return $this->success();
    }

    /**
     * @RequestMapping(path="initAdmin", methods="post")
     * @return string
     */
    private function initAdmin()
    {
        $data = [
            'username' => 'super_admin',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'status' => 1,
            'access_token' => md5('auth'),
            'access_token_expire_time' => date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60),
        ];
        $this->rbacAdminModel->insertSingle($data);
        return $this->success();
    }
}