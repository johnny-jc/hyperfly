<?php

declare(strict_types=1);

namespace Api\ApiApp\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiApp\Admin\Model\PermissionModel;
use Doctrine\Common\Annotations\AnnotationReader;

use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use Api\ApiService\Middleware\AuthMiddleware;
use Api\ApiService\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * 权限管理控制器
 * Class PermissionController
 * @package Api\ApiApp\Admin\v1\Controller
 * @Controller(prefix="Admin/v1/Permission")
 * @Middlewares({
 *     @Middleware(AuthMiddleware::class),
 *     @Middleware(PermissionMiddleware::class)
 *     })
 */
class PermissionController extends BaseController
{

    /**
     * 文件夹分隔符
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * 默认接口端入口
     * @var string
     */
    private $apiApp = 'Admin';

    /**
     * 默认接口版本
     * @var string
     */
    private $apiVersion = 'v1';

    /**
     * 控制器文件夹名称
     * @var string
     */
    private $controllerDirName = 'Controller';

    /**
     * 默认接口端入口命名空间
     * @var string
     */
    private $baseApiAppNamespace = 'Api\ApiApp\\';

    /**
     * @Inject
     * @var PermissionModel
     */
    private $permissionModel;

    /**
     * 获取权限列表
     * @RequestMapping(path="getPermissionList", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getPermissionList()
    {
        $condition = $this->request->all();
        if (!isset($condition['apiApp']) || empty($condition['apiApp'])) {
            $condition['apiApp'] = $this->apiApp;
        }
        if (!isset($condition['apiVersion']) || empty($condition['apiVersion'])) {
            $condition['apiVersion'] = $this->apiVersion;
        }
        $condition['apiClass'] = $condition['apiClass'] ?? '';
        $condition['apiFunction'] = $condition['apiFunction'] ?? '';
        $condition['offset'] = $condition['offset'] ?? self::OFFSET;
        $condition['limit'] = $condition['limit'] ?? self::LIMIT;
        return $this->success($this->permissionModel->getPermissionList($condition));
    }

    /**
     * 生成对应应用程序入口所有权限
     * @RequestMapping(path="generateFilePermissions", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function generateFilePermissions()
    {
        $requestData = $this->request->all();
        if (!isset($requestData['apiApp']) && !empty($requestData['apiApp'])) {
            $this->apiApp = $requestData['apiApp'];
        }
        if (!isset($requestData['apiVersion']) && !empty($requestData['apiVersion'])) {
            $this->apiVersion = $requestData['apiVersion'];
        }
        if (!$this->isApiAppDirExists()) {
            return $this->fail('应用程序入口:' . $requestData['apiApp'] . '不存在');
        }
        if (!$this->isApiVersionDirExists()) {
            return $this->fail('接口版本:' . $requestData['apiVersion'] . '不存在');
        }
        return $this->createPermission() ?
            $this->success() :
            $this->fail();
    }

    /**
     * 保存对应应用程序的所有权限接口
     * @return bool
     */
    private function createPermission()
    {
        $functions = $this->getAllFunctions();
        $createData = [];
        foreach ($functions as $k => $v) {
            $i = explode('/', $v);
            $class = $i[0];
            $function = $i[1];
            $route = '/' . $this->apiApp . '/' . $this->apiVersion . '/' . $class . '/' . $function;
            $createData[] = [
                'api_app' => $this->apiApp,
                'api_version' => $this->apiVersion,
                'api_class' => $class,
                'api_function' => $function,
                'api_route' => $route,
            ];
        }
        return $this->permissionModel->createPermission($createData, $this->apiApp, $this->apiVersion);
    }

    /**
     * 获取所有业务方法
     * @return array
     */
    private function getAllFunctions()
    {
        $reader = new AnnotationReader();
        $namespaces = $this->getControllerNamespaces();
        $functions = [];
        foreach ($namespaces as $namespace) {
            try {
                $reflectionObj = new \ReflectionClass($namespace);
                $annotationClasses = $reader->getClassAnnotations($reflectionObj);
                foreach ($annotationClasses as $annotationClass) {
                    $methods = $reflectionObj->getMethods(\ReflectionMethod::IS_PUBLIC);
                    if (!is_object($annotationClass) ||
                        (property_exists($annotationClass, 'prefix') !== true)) {
                        continue;
                    }
                    $prefix = $annotationClass->prefix;
                    $prefixExplode = explode('/', $prefix);
                    if (count($prefixExplode) !== 3) {
                        $this->throwApiException('非法的Controller注解路由');
                    }
                    $controller = $prefixExplode[2];
                    foreach ($methods as $method) {
                        $annotationMethod = $reader->getMethodAnnotations($method);
                        if (empty($annotationMethod) || !isset($annotationMethod[0])) {
                            continue;
                        }
                        $annotationMethodObject = $annotationMethod[0];
                        if (!is_object($annotationMethodObject) ||
                            (property_exists($annotationMethodObject, 'path') !== true)) {
                            $this->throwApiException('业务方法属性:`path`不存在');
                        }
                        $functions[] = $controller . '/' . $annotationMethod[0]->path;
                    }
                }
            } catch (\Exception $e) {
                $this->throwApiException('获取所有业务方法失败，错误：' . $e->getMessage());
            }
        }
        return $functions;
    }

    /**
     * 检测接口端入口是否存在
     * @param string $apiApp
     * @return bool
     */
    private function isApiAppDirExists()
    {
        $path = BASE_PATH . self::DS . 'api' . self::DS . 'ApiApp' . self::DS . $this->apiApp;
        return is_dir($path);
    }

    /**
     * 检测接口版本是否存在
     * @param string $apiApp
     * @param string $apiVersion
     * @return bool
     */
    private function isApiVersionDirExists()
    {
        $path = BASE_PATH . self::DS . 'api' . self::DS . 'ApiApp' . self::DS . $this->apiApp . self::DS .
            $this->apiVersion;
        return is_dir($path);
    }

    /**
     * 设置接口端入口
     * @param string $app
     */
    public function setApiApp(string $app)
    {
        $this->apiApp = $app;
    }

    /**
     * 获取接口端入口
     * @return string
     */
    public function getApiApp()
    {
        return $this->apiApp;
    }

    /**
     * 设置接口版本
     * @param string $version
     * @return string
     */
    public function setApiVersion(string $version)
    {
        return $this->apiVersion = $version;
    }

    /**
     * 获取接口版本
     *
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * 获取对应入口端控制器所有命名空间
     * @return array
     */
    private function getControllerNamespaces()
    {
        $controllers = $this->getControllers();
        $controllerNamespaces = [];
        $namespace = $this->baseApiAppNamespace . $this->apiApp . '\\' . $this->apiVersion . '\Controller\\';
        foreach ($controllers as $k => $v) {
            $controllerNamespaces[] = $namespace . $v;
        }
        return $controllerNamespaces;
    }

    /**
     * 获取对应接口端版本所有控制器名称
     * @return array
     */
    private function getControllers()
    {
        $path = BASE_PATH . self::DS . 'api' . self::DS . 'apiApp' . self::DS;
        $path .= $this->apiApp . self::DS . $this->apiVersion . self::DS . $this->controllerDirName;
        $controllerFiles = scandir($path);
        if ($controllerFiles === false) {
            return [];
        }
        $controllers = [];
        foreach ($controllerFiles as $k => $v) {
            if ($v == '.' || $v == '..') {
                continue;
            }
            $fileStrLen = strlen($v) - 4;
            $controllers[] = substr($v, 0, $fileStrLen);
        }
        return $controllers;
    }

}
