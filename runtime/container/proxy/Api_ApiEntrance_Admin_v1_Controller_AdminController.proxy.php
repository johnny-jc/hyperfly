<?php

declare (strict_types=1);
namespace Api\ApiEntrance\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiEntrance\Admin\Model\AdminModel;
use Hyperf\Redis\Redis;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
/**
 * @Controller(prefix="admin/v1/admin")
 *
 * Class AdminController
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
    private $model;
    /**
     * @RequestMapping(path="login", method="post")
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function login()
    {
        $login = $this->model->login($this->request->all());
        return $login ? $this->success(['d']) : $this->fail($this->model->getModelError());
    }
    /**
     * @RequestMapping(path="logout", method="post")
     *
     * @param RequestInterface $request
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function logout()
    {
        $accessToken = (string) $this->request->input('admin_access_token');
        return AdminModel::logout($accessToken) ? $this->success() : $this->fail();
    }
}