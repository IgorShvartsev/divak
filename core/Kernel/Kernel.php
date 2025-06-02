<?php
namespace Kernel;

use \Kernel\Exception\KernelException;
use \Kernel\Exception\ResponseException;
use \Kernel\Container\ContainerInterface;
use \Kernel\Container\Container;

/**
 * Kernel class
 *
 * @author Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class Kernel extends Container
{
    /** 
     * @var \Kernel\Kernel 
     */
    private static $instance;

    /** 
     * @var boolean 
     */
    private $run = false;


    /**
     * Get instance of Kernel class
     *
     * @return \Kernel\Kernel
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Kernel();
            self::$instance->initContainer();
        }

        return self::$instance;
    }

    /** Disable methods for singleton */
    private function __construct()
    {
    }
    
    private function __clone()
    {
    }

    /**
     * Run application
     * 
     * @param Closure $callback
     */
    public function run(?\Closure $callback = null)
    {
        if ($this->run) {
            return;
        }

        $this->run = true;

        \Config::init();
        date_default_timezone_set(\Config::get('app.timezone'));
        $this->bindCoreClasses();

        if (!defined('CLI_MODE')) {
            set_error_handler('\Kernel\Error::errorHandler');
            set_exception_handler('\Kernel\Error::exceptionHandler');
        }
        
        $this->initDbConnection();
        
        if ($callback) {
            call_user_func_array($callback, [$this]);
        }

        if (defined('CLI_MODE')) {
            return;
        }
        
        $this->initSession($this->make(\Session::class), \Config::get('session'));
        $this->initEvents();
        $this->handleRequest();
        $this->dispatch();
    }

    /**
    * Initialize Session
    *
    * @param \Session $session
    * @param [] $config
    */
    protected function initSession(\Session $session, array $config)
    {
        \Session\SessionManager::setHandler($config['type']);
        
        $session->setCookieParams(
            !empty($config['lifetime']) ? $config['lifetime'] : 0,
            !empty($config['path']) ? $config['path'] : '/',
            !empty($config['domain']) ? $config['domain'] : null,
            isset($config['secure']) ? $config['secure'] : false,
            isset($config['http_only']) ? $config['http_only'] : false,
            !empty($config['samesite']) ? $config['samesite'] : 'Lax'
        );

        if (defined('STORAGE_PATH')) {
            $session->setStoragePath(STORAGE_PATH . '/session');
        }

        $session->start($config['name']);
    }

    protected function initEvents()
    {
        $eventDispatcher = $this->make(\Kernel\EventDispatcher::class);
        $eventDispatcher->provide(\Config::get('event.provider'));
    }
    
    /**
     * Initialize Database connection
     * 
     * @throws KernelException
     */
    protected function initDBConnection()
    {
        $config = \Config::get('database');

        if (empty($config['default'])) {
            return;
        }

        if (!isset($config[$config['default']])) {
            throw new KernelException(
                'DB credentials are not found in database config for "' . $config['default'] . '"'
            );
        }

        $dbManager = $this->make(\Db\Manager::class);
        $dbParams = $config[$config['default']];
        $dbManager->connect($dbParams, $config['default']);
    }
    
    /**
     * Bind core classes into service container
     */
    protected function bindCoreClasses()
    {
        $coreClasses = [
            [
                'className' => \Kernel\Http\Request::class ,   
                'classImplementation' => '\Kernel\Http\Request',    
                'type' => ContainerInterface::BIND_SHARE,
            ],
            [
                'className' => \Kernel\Http\Response::class ,  
                'classImplementation' => '\Kernel\Http\Response',   
                'type' => ContainerInterface::BIND_SHARE,
            ],
            //['className' => \Kernel\Http\MiddlewareManager::class, 'classImplementation' => '\Kernel\Http\MiddlewareManager', 'type' => ContainerInterface::BIND_SHARE],
            [
                'className' => \Db\Manager::class,             
                'classImplementation' => '\Db\Manager',             
                'type' => ContainerInterface::BIND_SHARE,
            ],
            [
                'className' => \Kernel\Router::class,          
                'classImplementation' => '\Kernel\Router',          
                'type' => ContainerInterface::BIND_SHARE,
            ],
            [
                'className' => \Session::class,                
                'classImplementation' => '\Session',                
                'type' => ContainerInterface::BIND_SHARE,
            ],
            [
                'className' => \Kernel\EventDispatcher::class,             
                'classImplementation' => '\Kernel\EventDispatcher',             
                'type' => ContainerInterface::BIND_SHARE,
            ],
            [
                'className' => \Controller::class,             
                'classImplementation' => '\Controller',             
                'type' => ContainerInterface::BIND_FACTORY,
            ],
        ];

        foreach ($coreClasses  as $item) {
            $this->bind($item['className'], $item['classImplementation'], $item['type']);
        }

        $this->bindInstance(
            \Kernel\Http\MiddlewareManager::class, 
            new \Kernel\Http\MiddlewareManager(\Config::get('middleware'))
        );

        if (defined('STORAGE_PATH')) {
            $this->bindInstance(
                \Kernel\Log::class, 
                new Log(STORAGE_PATH . '/log/log-' . date('Y-m-d') . '.txt')
            );
        }
    }

    /**
     * Handle resuest
     *
     */
    protected function handleRequest()
    {
        $request = $this->make(\Kernel\Http\Request::class);
        
        $getData = $this->tidyInput($_GET);
        $postData = $this->tidyInput($_POST);
        $cookieData = $this->tidyInput($_COOKIE);
        $requestData = $this->tidyInput($_REQUEST);

        $getData = $this->securexss($getData);
        $postData = $this->securexss($postData);
        $cookieData = $this->securexss($cookieData);
        $requestData = $this->securexss($requestData);

        $request->set($getData, $request::HTTP_TYPE_GET);
        $request->set($postData, $request::HTTP_TYPE_POST);
        $request->set($cookieData, $request::HTTP_TYPE_COOKIE);
        $request->set($requestData, $request::HTTP_TYPE_REQUEST);

        $jsonParams = file_get_contents("php://input");
        $jsonData = json_decode($jsonParams, true);

        if (json_last_error() == JSON_ERROR_NONE) {
            $request->set($this->tidyInput($jsonData), $request::HTTP_TYPE_JSON);
        }

        $headers = [];

        if (!function_exists('getallheaders')) {
            foreach ($_SERVER as $name => $value) {
                if (strtolower(substr($name, 0, 5)) === 'http_') {
                    $headers[
                        str_replace(
                            ' ', 
                            '-', 
                            ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))
                        )
                    ] = $value;
                }
            }
        } else {
            $headers = getallheaders();
        }

        $request->setHeaders($headers);
        $request->setMethod($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Dispatch process
     * 
     * @throws ResponseException
     */
    protected function dispatch()
    {
        $request  = $this->make(\Kernel\Http\Request::class);
        $response = $this->make(\Kernel\Http\Response::class);
        $router   = $this->make(\Kernel\Router::class);
        $middlewareManager = $this->make(\Kernel\Http\MiddlewareManager::class);

        $isFoundRoute = $router->parseUrl($_SERVER['REQUEST_URI'], $request->getMethod());

        if (!$isFoundRoute) {
            throw new ResponseException(\Response::getResponseCodeDescription(404), 404);
        }

        if ($router->action) {
            $request->set($this->tidyInput($router->params), $request::HTTP_TYPE_PARAMS);
            
            // run middlewares before
            $middlewareManager->handleBefore($request, $response);

            $controller = (new \Resolver)->resolve($router->controller);
            if ($controller instanceof \Controller) {
                $options = array(
                    'baseUrl' => $router->getBaseUrl()
                );

                $controller->setOptions($options);
                $reflection = new \ReflectionClass($controller);

                try {
                    $method = $reflection->getMethod($router->action);

                    if ($method->isPublic() && !$method->isAbstract()) {
                        // handle allowed HTTP method for the given route
                        $allowedHttpMethod = $router->getHttpMethod();

                        if (
                            !empty($allowedHttpMethod) 
                            && strpos($allowedHttpMethod, $request->getMethod()) === false 
                        ) {
                            throw new ResponseException('Method ' . $allowedHttpMethod . ' is only allowed', 405);
                        }

                        // handle route middlewares
                        $routeMiddlewareTags = $router->getMiddlewareTags();

                        if (
                            array_key_exists('before', $routeMiddlewareTags) 
                            || array_key_exists('after', $routeMiddlewareTags)
                        ) {
                            if (!empty($routeMiddlewareTags['before']) && count($routeMiddlewareTags['before']) > 0) {
                                $middlewareManager->handleWithTag(
                                    $routeMiddlewareTags['before'], 
                                    $request, 
                                    $response
                                );
                            }
                        } elseif (count($routeMiddlewareTags) > 0) {
                            $middlewareManager->handleWithTag(
                                $routeMiddlewareTags, 
                                $request, 
                                $response
                            );
                        }

                        $this->launchControlAction($controller, $method);
                    } else {
                        throw new ResponseException(\Response::getResponseCodeDescription(404), 404);
                    }
                } catch (\ReflectionException $e) {
                    throw new ResponseException(
                        'Method "' . $router->action . '" does not exist in "'
                        . $router->controller . '" controller', 
                        404
                    );
                }
            } else {
                throw new KernelException('Controller ' . $router->controller . ' does not exist');
            }
        } else {
            throw new KernelException('Action is not defined');
        }
    }

    /**
     * Tides input params
     *
     * @param mixed $input
     * 
     * @return string|array
     */
    protected function tidyInput($input)
    {
        if (is_array($input)) {
            $handled = [];

            foreach ($input as $key => $val) {
                $handled[$key] = $this->tidyInput($val);
            }

            return $handled;
        } elseif (is_string($input)) {
            // xss clean
            $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/';    // 00-08, 11, 12, 14-31, 127

            do {
                $str = preg_replace($non_displayables, '', $input, -1, $count);
            } while ($count);

            //$input = str_replace("\t", ' ', $input);
            $input = str_replace(['"'], ["'"], $input);

            return stripslashes($input);
        } else {
            return $input;
        }
    }

    /**
     * SecureXss
     * 
     * @param mixed $value
     * 
     * @return mixed
     */ 
    protected function securexss($value)
    {
        static $cleanup = [
            '&quot;' => '&#38;', 
            '"' => '&quot;', 
            "'" => '&#039;', 
            '<' => '&lt;', 
            '>' => '&gt;', 
            '`' => '&#96;'
        ];

        if (is_array($value)) {
            $new = [];

            foreach ($value as $key => $val) {
                $new[$key] = $this->securexss($val);
            }

            return $new;
        }

        $value = preg_replace(
            ['/javascript:/i', '/\0/'], 
            ['java script:', ''], 
            $value
        );

        $value = preg_replace('/javascript:/i', 'java script:', $value);

        return str_replace(array_keys($cleanup), array_values($cleanup), $value);
    }

    /**
     *  Launch Controller method
     *
     * @param \Controller
     * @param \ReflectionMethod
     */
    protected function launchControlAction(\Controller $controller, \ReflectionMethod $method)
    {
        $cache = null;
        $request  = $this->make(\Kernel\Http\Request::class);
        $response = $this->make(\Kernel\Http\Response::class);
        $router   = $this->make(\Kernel\Router::class);
        $middlewareManager = $this->make(\Kernel\Http\MiddlewareManager::class);

        // using cache if it's enabled for given route
        // check cache hit
        $cacheSettings = $router->getCacheSettings();
        if (
            $cacheSettings['enable'] 
            && $request->getMethod() === 'GET' 
            && count($request->getQueryParams()) === 0
        ) {
            $options = \Config::get('cache.options');
            $options['lifetime'] = $cacheSettings['lifetime'];
            $cache = \Cache::factory(\Config::get('cache.type'), $options);
            $cacheKey = $this->getCacheKey($request, \Session::getInstance());

            if ($data = $cache->load($cacheKey)) {
                if ($data) {
                    $data = unserialize($data);
                }

                foreach ($data['headers'] as $key => $value) {
                    header("$key:$value");
                }

                $response->setBody($data['output']);
                $output  = implode('', $response->getBody());
                file_put_contents('php://output', $output);
                exit();
            }
        }

        $controllerName = strtolower(
            str_ireplace(
                'Controller', 
                '', 
                str_replace('\\', '/', get_class($controller))
            )
        );

        $moduleName = $router->getModuleName();

        $controllerName = str_replace(
            'modules/' . strtolower($moduleName) . '/',
            '',
            $controllerName
        );
        
        $layout = !empty(\Config::get('app.default_layout')) ? \Config::get('app.default_layout') : null;

        // add \View object to controller
        $controller->setView(
            new \View($controllerName, $layout, $router->action, $router->params['lang'], $moduleName)
        );
        
        $controller->view->setBaseUrl($router->getBaseUrl());

        if (method_exists($controller, 'beforeActionStart')) {
            $controller->beforeActionStart();
        }
       
        ob_start();

        // invoke contol metod (ACTION)
        $method->invoke($controller);

        // route middleware after
        $routeMiddlewareTags = $router->getMiddlewareTags();
        if (
            array_key_exists('after', $routeMiddlewareTags) 
            && count($routeMiddlewareTags['after']) > 0
        ) {
            $middlewareManager->handleWithTag($routeMiddlewareTags['after'], $request, $response);
        }

        // run middlewares after
        $middlewareManager->handleAfter($request, $response);

        // output headers first
        $headers = $response->getHeaders();
        foreach ($headers as $key => $value) {
            header("$key:$value");
        }
        
        // then output content
        $out = ob_get_clean();
        $response->setBody($out);
        $output  = implode('', $response->getBody());

        // if cache is enabled save results to it
        if ($cache) {
            $cache->save(serialize([
                'headers' => $headers,
                'output'  => $output
            ]), $cacheKey);
        }

        file_put_contents('php://output', $output);
    }

    /**
     * Get unique cache key
     *
     * @param \Kernel\Http\Request $request
     * @param \Session $session
     * 
     * @return string
     */
    protected function getCacheKey($request, $session)
    {
        return serialize($request->getParams())
            . serialize($session->getAll());
    }
}
