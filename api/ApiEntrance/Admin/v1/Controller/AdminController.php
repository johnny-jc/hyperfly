<?php

namespace Api\ApiEntrance\Admin\v1\Controller;

use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;

/**
 * @package Api\ApiEntrance\Admin\v1\Controller
 * @Controller(prefix="admin/v1/admin")
 */
class AdminController
{
    /**
     * @param RequestInterface $request
     * @RequestMapping(path="login", methods="post")
     * @return string
     */
    public function login(RequestInterface $request)
    {
        return 'success';
    }
}