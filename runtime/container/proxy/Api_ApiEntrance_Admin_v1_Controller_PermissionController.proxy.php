<?php

declare (strict_types=1);
namespace Api\ApiEntrance\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiEntrance\Admin\Model\PermissionModel;
use Api\ApiEntrance\Admin\Model\RoleModel;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
/**
 * 权限管理控制器
 * Class PermissionController
 * @package Api\ApiEntrance\Admin\v1\Controller
 * @Controller(prefix="Admin/v1/Permission")
 */
class PermissionController extends BaseController
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
     * 文件夹分隔符
     */
    const DS = DIRECTORY_SEPARATOR;
    /**
     * 默认接口端入口
     * @var string
     */
    private $apiEntrance = 'Admin';
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
    private $baseApiEntranceNamespace = 'Api\\ApiEntrance\\';
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
        if (!isset($condition['apiEntrance']) || empty($condition['apiEntrance'])) {
            $condition['apiEntrance'] = $this->apiEntrance;
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
     * 生成对应接口端入口所有权限
     * @RequestMapping(path="generateFilePermissions", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function generateFilePermissions()
    {
        $requestData = $this->request->all();
        $this->apiEntrance = $requestData['apiEntrance'] ?? $this->apiEntrance;
        $this->apiVersion = $requestData['apiVersion'] ?? $this->apiVersion;
        if (!$this->isApiEntranceDirExists()) {
            return $this->fail('接口端入口:' . $requestData['apiEntrance'] . '不存在');
        }
        if (!$this->isApiVersionDirExists()) {
            return $this->fail('接口版本:' . $requestData['apiVersion'] . '不存在');
        }
        return $this->createPermission() ? $this->success() : $this->fail();
    }
    /**
     * 保存对应接口端的所有权限接口
     * @return bool
     */
    private function createPermission()
    {
        $functions = $this->getAllFunctions();
        $createData = [];
        foreach ($functions as $k => $v) {
            $i = explode('/', $v);
            $class = substr($i[0], 0, strlen($i[0]) - strlen('Controller'));
            $function = $i[1];
            $route = '/' . $this->apiEntrance . '/' . $this->apiVersion . '/' . $class . '/' . $function;
            $createData[] = ['api_entrance' => $this->apiEntrance, 'api_version' => $this->apiVersion, 'api_class' => $class, 'api_function' => $function, 'api_route' => $route];
        }
        return $this->permissionModel->createPermission($createData, $this->apiEntrance, $this->apiVersion);
    }
    /**
     * 获取所有业务方法
     * @return array
     */
    private function getAllFunctions()
    {
        $namespaces = $this->getControllerNamespaces();
        $methods = [];
        foreach ($namespaces as $namespace) {
            try {
                $reflectionObj = new \ReflectionClass($namespace);
                $methods[] = $reflectionObj->getMethods(\ReflectionMethod::IS_PUBLIC);
            } catch (\Exception $e) {
                $this->throwApiException('获取所有业务方法失败，错误：' . $e->getMessage());
            }
        }
        $functions = [];
        //过滤父级方法
        foreach ($methods as $method) {
            foreach ($method as $k => $v) {
                if (in_array($v->class, $namespaces)) {
                    //过滤构造函数
                    if ($v->name == '__construct') {
                        continue;
                    }
                    //获取接口类
                    $class = explode('\\', $v->class);
                    $class = end($class);
                    $functions[] = $class . '/' . $v->name;
                }
            }
        }
        return $functions;
    }
    /**
     * 检测接口端入口是否存在
     * @param string $apiEntrance
     * @return bool
     */
    private function isApiEntranceDirExists()
    {
        $path = BASE_PATH . self::DS . 'api' . self::DS . 'ApiEntrance' . self::DS . $this->apiEntrance;
        return is_dir($path);
    }
    /**
     * 检测接口版本是否存在
     * @param string $apiEntrance
     * @param string $apiVersion
     * @return bool
     */
    private function isApiVersionDirExists()
    {
        $path = BASE_PATH . self::DS . 'api' . self::DS . 'ApiEntrance' . self::DS . $this->apiEntrance . self::DS . $this->apiVersion;
        return is_dir($path);
    }
    /**
     * 设置接口端入口
     * @param string $entrance
     */
    public function setApiEntrance(string $entrance)
    {
        $this->apiEntrance = $entrance;
    }
    /**
     * 获取接口端入口
     * @return string
     */
    public function getApiEntrance()
    {
        return $this->apiEntrance;
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
        $namespace = $this->baseApiEntranceNamespace . $this->apiEntrance . '\\' . $this->apiVersion . '\\Controller\\';
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
        $path = BASE_PATH . self::DS . 'api' . self::DS . 'apiEntrance' . self::DS;
        $path .= $this->apiEntrance . self::DS . $this->apiVersion . self::DS . $this->controllerDirName;
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