<?php
declare(strict_types=1);

namespace Api\ApiEntrance\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiEntrance\Admin\Model\AdminModel;

use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;

/**
 * @Controller(prefix="admin/v1/admin")
 *
 * Class AdminController
 * @package Api\ApiEntrance\Admin\v1\Controller
 */
class AdminController extends BaseController
{
    /**
     * @RequestMapping(path="login", methods="post")
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function login()
    {
        return AdminModel::login($this->request->all()) ?
            $this->success() :
            $this->fail();
    }

    /**
     * @RequestMapping(path="logout", methods="post")
     *
     * @param RequestInterface $request
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function logout()
    {
        $accessToken = (string)$this->request->input('admin_access_token');
        return AdminModel::logout($accessToken) ?
            $this->success() :
            $this->fail();
    }

}