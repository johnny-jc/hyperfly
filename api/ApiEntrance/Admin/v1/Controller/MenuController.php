<?php
declare(strict_types=1);

namespace Api\ApiEntrance\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiEntrance\Admin\Model\MenuModel;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * Class MenuController
 * @package Api\ApiEntrance\Admin\v1\Controller
 * @Controller(prefix="admin/v1/menu")
 */
class MenuController extends BaseController
{
    /**
     * @Inject
     * @var MenuModel
     */
    private $menuModel;

    /**
     * @param RequestInterface $request
     * @RequestMapping(path="createMenu", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function createMenu(RequestInterface $request)
    {
        return MenuModel::createMenu($request->all()) ?
            $this->success() :
            $this->fail();
    }
}
