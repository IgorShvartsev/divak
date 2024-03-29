<?php
namespace Kernel;

use \Kernel\Exception\RouteException;

/**
 * Router class
 *
 * @author Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class Router
{
    /**
     * Current controller
     *
     * @var string
     */
    public $controller = '';
    
    /**
     * Current action
     *
     * @var string
     */
    public $action = 'index';

    /**
     * Params
     *
     * @var array
     */
    public $params = [];

    /**
     * @var string
     */
    protected $moduleName = '';
    
    /**
     * Controller path
     *
     * @var string
     */
    protected $controllerDir = 'controllers';
    
    
    /**
     * Base Url
     *
     * @var string
     */
    protected $baseUrl = '';

    /**
     * HTTP method (GET, POST, PUT, DELETE)
     *
     * @var string
     */
    protected $httpMethod;

    /**
     * Middleware tag array that should be applied to the given route
     *
     * @var string
     */
    protected $middlewareTags = [];

    /**
     * Cache used for the given route
     * Mostly this cache is applied to http GET type
     *
     * @var array
     */
    protected $cacheSettings = [
        'enable' => false,
        'lifetime' => 3600
    ];
    
    /**
     * Constructor
     *
     */
    public function __construct()
    {
        if (!empty(\Config::get('app.base_url'))) {
            $baseUrl =  '/' . trim(\Config::get('app.base_url'), '/');
            $this->baseUrl = $baseUrl == '/' ? '' : $baseUrl;
        }
    }
    
    /**
     * Parses url string
     *
     * @param string $uri
     * @param string $httpMethod
     * 
     * @throws RouteException
     * 
     * @return boolean
     */
    public function parseUrl($uri, $httpRequestMethod)
    {
        // default param
        $action  = '';
        $module = '';
        $pattern = str_replace(
            ['/', '.', ',', ';'], 
            ['\\/', '\\.', '\\,', '\\;'], 
            $this->baseUrl
        );

        $uri = preg_replace('$' . $pattern . '$i', '', $uri);
        $uri = preg_replace('/\?.*$/', '', $uri);
        $uri = trim($uri, '/');

        // extract lang param
        if (($pos = strpos($uri, '/')) !== false && $pos === 2) {
            $this->params['lang'] = strtolower(substr($uri, 0, $pos));
            $uri = substr($uri, $pos + 1);
        } elseif (strlen($uri) === 2) {
            $this->params['lang'] = strtolower($uri);
            $uri = '';
        } else {
            $this->params['lang'] = strtolower(\Config::get('app.default_language'));
        }

        $this->params['uri'] = $uri;
           
        // handle routers from config/route.php
        $routes = !empty(\Config::get('route')) ? \Config::get('route') : [];
        $isFoundRoute = false;

        foreach ($routes as $key => $val) {
            $key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
 
            if (preg_match('#^' . $key . '$#', $uri)) {
                if (is_array($val)) {
                    foreach ($val as $httpMethod => $data) {
                        if (is_array($data)) {
                            // check if "action" key is set in array, it's required
                            if (!isset($data['action'])) {
                                throw new RouteException(
                                    'Key "action" is not defined for route `' . $key . ''
                                );
                            }

                            // check if "middleware" key is set in array with condition "before" and/or  "after"
                            // if there are no any conditions middleware is supposed to have condition "before"
                            // on default
                            if (array_key_exists('middleware', $data)) {
                                $isConditionAvailable = false;
                                foreach (['before', 'after'] as $mdlCondition) {
                                    if (
                                        is_array($data['middleware']) 
                                        && array_key_exists($mdlCondition, $data['middleware'])
                                    ) {
                                        if (!empty($data['middleware'][$mdlCondition])) {
                                            $this->middlewareTags[$mdlCondition] = $this->normalizeMiddlewareTags(
                                                $data['middleware'][$mdlCondition]
                                            );
                                        }
                                        $isConditionAvailable = true;
                                    }
                                }

                                if (!$isConditionAvailable && !empty($data['middleware'])) {
                                    $this->middlewareTags = $this->normalizeMiddlewareTags(
                                        $data['middleware']
                                    );
                                }
                            }

                            // check if there is cache settings for the given route
                            if (array_key_exists('cache', $data) && is_array($data['cache'])) {
                                foreach ($data['cache'] as $key => $v) {
                                    if (isset($this->cacheSettings[$key])) {
                                        $this->cacheSettings[$key] = $v;
                                    }
                                }
                            }
                            $val = $data['action'];
                        } else {
                            $val = $data;
                        }
                        
                        $httpMethod = strtoupper($httpMethod);
                        $this->httpMethod = $httpMethod;

                        if (strtoupper($httpRequestMethod) === $httpMethod) {
                            break;
                        }
                    }
                }

                if (strpos($val, '$') !== false && strpos($key, '(') !== false) {
                    $val = preg_replace('#^' . $key . '$#', $val, $uri);
                }

                $uri = $val;
                $isFoundRoute = true;

                break;
            }
        }

        if (!$isFoundRoute) {
            return false;
        }

        $this->params['page'] = '';
           
        $uriElements = array_values(array_filter(explode('/', $uri)));
        
        if (count($uriElements) > 0) {
            $controller = array_shift($uriElements);

            if (preg_match('#\[(.+)\]#', $controller, $matches)) {
                $this->controller = 'Modules\\' . ucfirst(strtolower($matches[1]));
                $this->moduleName = ucfirst(strtolower($matches[1]));
                $controllerPath = APP_PATH . DIRECTORY_SEPARATOR 
                    . 'modules' . DIRECTORY_SEPARATOR 
                    . $matches[1] . DIRECTORY_SEPARATOR
                    . $this->controllerDir . DIRECTORY_SEPARATOR;
            } else {
                $this->controller = ucfirst(strtolower($controller));
                $controllerPath = APP_PATH . DIRECTORY_SEPARATOR 
                    . $this->controllerDir . DIRECTORY_SEPARATOR;
            }

            $isFoundController = false;

            if (file_exists($controllerPath . $this->controller . 'Controller.php')) {
                $this->controller .= 'Controller';
                $isFoundController = true;

                if (!count($uriElements)) {
                    $action = $this->action;
                }
            } else {
                while (count($uriElements) > 0) {
                    $partName  = array_shift($uriElements);
                    $controllerPath .= ucfirst(strtolower($partName));

                    if (is_dir($controllerPath)) {
                        $controllerPath .= DIRECTORY_SEPARATOR;
                        $this->controller .= '\\' . ucfirst(strtolower($partName));
                        continue;
                    } elseif (file_exists($controllerPath . 'Controller.php')) {
                        $this->controller .= '\\' . ucfirst(strtolower($partName)) . 'Controller';
                        $isFoundController = true;
                        break;
                    } else {
                        array_unshift($uriElements, $partName);
                        break;
                    }
                }
            }

            if (!$isFoundController) {
                if (count($uriElements) > 0) {
                    $partName  = array_shift($uriElements);
                    $this->controller .= '\\' . ucfirst(strtolower($partName)) . 'Controller';
                } else {
                    $this->controller .= 'Controller.php';
                }
            }
               
            $action = !empty($action) ? $action : array_shift($uriElements);

            if (!empty($action)) {
                $this->action = strtolower(preg_replace('/[\'";!@#$%^&*()[]=|{}:;.,?`~<> ]/i', '', $action));
                // make camelcase name if there is "-" between words
                $this->action = preg_replace_callback(
                    '/[-]([a-z])/i', 
                    function ($matches) {
                        return ucfirst($matches[1]);
                    }, 
                    $this->action
                );
            }

            if (count($uriElements) > 0) {
                $uriElements = array_reverse($uriElements);
                if (count($uriElements) > 0  && preg_match('/\.htm|html$/i', $uriElements[0])) {
                    $page = array_shift($uriElements);
                    $this->params['page'] = str_replace(array('.html', '.htm'), '', $page);
                } else {
                    $this->params['page'] = $action;
                }

                $uriElements = array_reverse($uriElements);

                $i = 1;
                while (count($uriElements) > 0) {
                    $this->params['param' . $i] = array_shift($uriElements);
                    $i++;
                }
            } else {
                $this->params['page'] = $action;
            }
        } else {
            throw new RouteException(
                'Key "action" is empty. Please define properly "action" in route.php'
            );
        }

        $this->params['module'] = $this->moduleName;

        $this->params['pageid'] = md5($this->params['lang'] . $this->controller . $this->action 
            . $this->params['page'] . $this->params['module']);
        
        return true;
    }
    
    /**
     * Get base url (relative)
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Get middleware name array applied to given route
     *
     * @return array
     */
    public function getMiddlewareTags()
    {
        return $this->middlewareTags;
    }

    /**
     * Get HTTP method applied to given route
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * Get cache settings for the given route
     *
     * @return array
     */
    public function getCacheSettings()
    {
        return $this->cacheSettings;
    }

    /**
     * Normalize middleware tag list to array
     *
     * @param string|array  $middlewareTags - comma separated tags or array
     * @return array
     */
    protected function normalizeMiddlewareTags($middlewareTags)
    {
        return is_array($middlewareTags)
            ? $middlewareTags
            : array_map(
                function ($item) {
                    return trim($item);
                }, 
                explode(',', $middlewareTags)
            );
    }
}
