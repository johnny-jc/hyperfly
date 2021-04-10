<?php

declare (strict_types=1);
namespace Api\ApiTool;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface;
class ApiToolMethodAllowMiddleware
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        $this->__handlePropertyHandler(__CLASS__);
    }
    private $request;
    /**
     * @Inject
     * @var ResponseInterface
     */
    private $response;
}