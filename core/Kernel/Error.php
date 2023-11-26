<?php
namespace Kernel;

/**
 * Error class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class Error
{
    /**
     * @var array $handlers
     */ 
    protected static $handlers = [];

    /**
     * Constructor 
     */
    private function __construct()
    {
    }

    /**
     *  Clone
     */ 
    private function __clone()
    {
    }

    /**
     *  Error Handler
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @param string $errcontext
     * 
     * @return boolean
     * 
     * @throws \ErrorException 
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = '')
    {
        $l = error_reporting();

        if ($l & $errno) {
            switch ($errno) {
                case E_USER_ERROR:
                    $type = 'Fatal Error';
                    break;
                case E_USER_WARNING:
                case E_WARNING:
                    $type = 'Warning';
                    break;
                case E_USER_NOTICE:
                case E_NOTICE:
                case @E_STRICT:
                    $type = 'Notice';
                    break;
                case @E_RECOVERABLE_ERROR:
                    $type = 'Catchable';
                    break;
                default:
                    $type = 'Unknown Error';
                    break;
            }

            $exception = new \ErrorException($type . ': ' . $errstr, 0, $errno, $errfile, $errline);
            static::exceptionHandler($exception);
        }

        return false;
    }

    /**
     * Exception handler
     *
     * @param \Exception|\Throwable $e
     */
    public static function exceptionHandler($e)
    {
        $log = $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
            
        if (ini_get('log_errors')) {
            //error_log($log, 0);
        }

        if (count(static::$handlers) > 0) {
            foreach (static::$handlers as $handleFunc) {
                call_user_func($handleFunc, $e);
            }
            exit();
        }

        $description = sprintf(
            "%s:%d\n%s\n[%s]\n%s\n", 
            $e->getFile(), 
            $e->getLine(), 
            $e->getMessage(), 
            get_class($e), 
            $e->getTraceAsString()
        );

        if (get_class($e) === 'Kernel\Exception\ResponseException') {
            \Response::responseCodeHeader($e->getCode());
        } elseif (get_class($e) === 'Kernel\Exception\KernelException' || get_class($e) === 'ErrorException') {
            \Response::responseCodeHeader(401);
        }

        if (\Config::get('app.show_errors')) {
            \View::quickRender('error', ['description' => nl2br($description)]);
        }
        
        exit(0);
    }

    /**
     * Add custom implementation of Exception Handler to collection
     * At least added one handler overrides existing one
     *
     * @param Closure $callback
     */
    public static function addCustomExceptionHandler(\Closure $callback)
    {
        static::$handlers[] = $callback;
    }
}
