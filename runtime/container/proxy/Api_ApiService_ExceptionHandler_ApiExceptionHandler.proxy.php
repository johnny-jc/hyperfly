<?php

declare (strict_types=1);
namespace Api\ApiService\ExceptionHandler;

use Api\ApiBase\Base;
use Api\ApiService\Exception\ApiException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\Di\Annotation\Inject;
/**
 * Class ApiExceptionHandler
 * @package Api\ApiService\ExceptionHandler
 */
class ApiExceptionHandler extends ExceptionHandler
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
     * @var Base
     */
    private $base;
    /**
     * @param Throwable $throwable
     * @param ResponseInterface $response
     * @return mixed|ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $timestamp = (string) date('Y-m-d H:i:s', time());
        if ($throwable instanceof ApiException) {
            $this->stopPropagation();
            //输出错误位置
            $this->echoExceptionPosition($throwable, $timestamp);
        } else {
            //输出错误信息
            $this->echoExceptionStream($throwable, $timestamp);
        }
        return $this->base->requestFail('系统错误');
    }
    /**
     * @param Throwable $throwable
     * @return bool
     */
    public function isValid(Throwable $throwable) : bool
    {
        return true;
    }
    /**
     * 输出Exception错误信息
     * @param Throwable $throwable
     * @param string $errorTimestamp
     * @return bool
     */
    private function echoExceptionStream(Throwable $throwable, string $errorTimestamp)
    {
        echo PHP_EOL;
        echo '=======================Log Start[' . $errorTimestamp . ']==================';
        $trace = 0;
        foreach ($throwable->getTrace() as $k => $v) {
            if (!isset($v['file'])) {
                $v['file'] = '';
            }
            if (!isset($v['line'])) {
                $v['line'] = '';
            }
            if (!isset($v['function'])) {
                $v['function'] = '';
            }
            if (!isset($v['class'])) {
                $v['class'] = '';
            }
            if (!isset($v['type'])) {
                $v['type'] = '';
            }
            echo PHP_EOL;
            /*
            echo 'Trace[' . (string)$trace . ']:' . PHP_EOL;
            echo '     File:' . ' ' . $this->shortFilePath($v['file']) . PHP_EOL;
            echo '     Line:' . ' ' . $v['line'] . PHP_EOL;
            echo '     Function:' . ' ' . $v['function'] . PHP_EOL;
            echo '     Class:' . ' ' . $v['class'] . PHP_EOL;
            echo '     Type:' . ' ' . $v['type'] . PHP_EOL;
            */
            echo 'Trace[' . (string) $trace . ']:' . PHP_EOL;
            echo '     [File]' . '' . $this->shortFilePath($v['file']) . '#';
            echo '[Line]' . '' . $v['line'] . '#';
            echo '[Function]' . '' . $v['function'] . '#';
            echo '[Class]' . '' . $v['class'] . '#';
            echo '[Type]' . '' . $v['type'];
            $trace++;
        }
        echo PHP_EOL;
        echo PHP_EOL;
        echo '#######################################' . PHP_EOL;
        echo '#                                     #' . PHP_EOL;
        echo '#' . ' ' . 'Error Time:' . ' ' . $errorTimestamp . '     ' . '#' . PHP_EOL;
        echo '#                                     #' . PHP_EOL;
        echo '#######################################' . PHP_EOL;
        echo '#' . PHP_EOL;
        echo '#' . ' ' . 'Error File:' . ' ' . $this->shortFilePath($throwable->getFile()) . PHP_EOL;
        echo '#' . PHP_EOL;
        echo '#' . ' ' . 'Error Line:' . ' ' . $throwable->getLine() . PHP_EOL;
        echo '#' . PHP_EOL;
        echo '#' . ' ' . 'Error Message:' . ' ' . $throwable->getMessage() . PHP_EOL;
        echo '#' . PHP_EOL;
        echo '=======================Log End[' . $errorTimestamp . ']====================';
        echo PHP_EOL;
        return true;
    }
    /**
     * 输出Exception触发位置以及信息
     * @param Throwable $throwable
     * @param string $errorTimestamp
     * @return bool
     */
    private function echoExceptionPosition(Throwable $throwable, string $errorTimestamp)
    {
        echo PHP_EOL;
        echo '=======================Log Start[' . $errorTimestamp . ']==================' . PHP_EOL;
        echo '#';
        echo PHP_EOL;
        echo '#######################################' . PHP_EOL;
        echo '#                                     #' . PHP_EOL;
        echo '#' . ' ' . 'Exception Time:' . ' ' . $errorTimestamp . ' ' . '#' . PHP_EOL;
        echo '#                                     #' . PHP_EOL;
        echo '#######################################' . PHP_EOL;
        echo '#' . PHP_EOL;
        echo '#' . ' ' . 'Exception File:' . ' ' . $this->shortFilePath($throwable->getFile()) . PHP_EOL;
        echo '#' . PHP_EOL;
        echo '#' . ' ' . 'Exception Line:' . ' ' . $throwable->getLine() . PHP_EOL;
        echo '#' . PHP_EOL;
        echo '#' . ' ' . 'Exception Message:' . ' ' . $throwable->getMessage() . PHP_EOL;
        echo '#' . PHP_EOL;
        echo '=======================Log End[' . $errorTimestamp . ']==================' . PHP_EOL;
        return true;
    }
    /**
     * 截取错误文件路径短路径
     * @param string $originPath
     * @return false|string
     */
    private function shortFilePath(string $originPath)
    {
        $len = strlen(BASE_PATH);
        return substr($originPath, $len);
    }
}