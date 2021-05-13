<?php

declare(strict_types=1);

namespace Api\ApiApp\Admin\v2\Controller;

use Api\ApiBase\BaseController;

use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;

/**
 * Test Controller
 * Class AdminController
 * @Controller(prefix="Admin/v1/Admin")
 * @package Api\ApiApp\Admin\v1\Controller
 */

/**
 * Class TestController
 * @Controller(prefix="Admin/v2/Test")
 * @package Api\ApiApp\Admin\v2\Controller
 */
class TestController extends BaseController
{
    /**
     * test function
     * @RequestMapping(path="testFunction", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function testFunction()
    {
        return $this->success();
    }

}
