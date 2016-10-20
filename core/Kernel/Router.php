<?php

namespace Kernel;

use \Kernel\Exception\RouteException;

/**
* Router class
*
* @author Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Router
{
    /**
    * Current controller
    *
    * @var string
    */
    public $controller;
    
    /**
    * Current action
    *
    * @var string
    */
    public $action = 'index';

    
    /**
    *  Current module
    *
    * @var string
    */
    public $module = '';
    
    /**
    * Params
    *
    * @var array
    */
    public $params = [];
    
    /**
    * Controller path
    *
    * @var string
    */
    protected $_controllerPath = '';
    
    
    /**
    * Base Url
    *
    * @var string
    */
    protected $_baseUrl = '';

    /**
    * HTTP method (GET, POST, PUT, DELETE)
    *
    * @var string
    */
    protected $_httpMethod;

    /**
    * Middleware tag array that should be applied to the given route
    *
    * @var string
    */
    protected $_middlewareTags = [];

    /**
    * Cache used for the given route
    * Mostly this cache is applied to http GET type
    *
    * @var array
    */
    protected $_cacheSettings = [
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
            $baseUrl =  '/'.trim(\Config::get('app.base_url'), '/');
            $this->_baseUrl = $baseUrl == '/' ? '' : $baseUrl;
        }
        $this->_controllerPath = APP_PATH . '/controllers/';
    }
    
    /**
    * Parses url string
    *
    * @param string $uri
    * @param string $httpMethod
    */
    public function parseUrl($uri, $httpRequestMethod)
    {
        // default param
        $action  = '';
        $pattern = str_replace(array('/', '.', ',', ';'), array('\\/', '\\.', '\\,', '\\;'), $this->_baseUrl);
        $uri = preg_replace('$'.$pattern.'$i', '', $uri);
        $uri = preg_replace('/\?.*$/', '', $uri);
        $uri = trim($uri, '/');

        // extract lang param
        if (($pos = strpos($uri, '/')) !== false && $pos == 2) {
            $this->params['lang'] = strtolower(substr($uri, 0, $pos));
            $uri = substr($uri, $pos + 1);
        } elseif (strlen($uri) == 2) {
            $this->params['lang'] = strtolower($uri);
            $uri = '';
        } else {
            $this->params['lang'] = strtolower(\Config::get('app.default_language'));
        }
           
        // handle routers from config/route.php
        $routes = !empty(\Config::get('route')) ? \Config::get('route') : [];
        $isFoundRoute = false;
        foreach ($routes as $key => $val) {
            $key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
 
            if (preg_match('#^'.$key.'$#', $uri)) {
                if (is_array($val)) {
                    foreach ($val as $httpMethod => $data) {
                        if (is_array($data)) {
                            // check if "action" key is set in array, it's required
                            if (!isset($data['action'])) {
                                throw new RouteException('Key "action" is not defined for route `' . $key . '');
                            }
                            // check if "middleware" key is set in array with condition "before" and/or  "after"
                            // if there are no any conditions middleware is supposed to have condition "before"
                            // on default
                            if (array_key_exists('middleware', $data)) {
                                $isConditionAvailable = false;
                                foreach (['before', 'after'] as $mdlCondition) {
                                    if (is_array($data['middleware']) && array_key_exists($mdlCondition, $data['middleware'])) {
                                        if (!empty($data['middleware'][$mdlCondition])) {
                                            $this->_middlewareTags[$mdlCondition] = $this->_normalizeMiddlewareTags($data['middleware'][$mdlCondition]);
                                        }
                                        $isConditionAvailable = true;
                                    }
                                }
                                if (!$isConditionAvailable && !empty($data['middleware'])) {
                                    $this->_middlewareTags = $this->_normalizeMiddlewareTags($data['middleware']);
                                }
                            }
                            // check if there is cache settings for the given route
                            if (array_key_exists('cache', $data) && is_array($data['cache'])) {
                                foreach ($data['cache'] as $key => $v) {
                                    if (isset($this->_cacheSettings[$key])) {
                                        $this->_cacheSettings[$key] = $v;
                                    }
                                }
                            }
                            $val = $data['action'];
                        } else {
                            $val = $data;
                        }
                        
                        $httpMethod = strtoupper($httpMethod);
                        $this->_httpMethod = $httpMethod;
                        if (strtoupper($httpRequestMethod) == $httpMethod) {
                            break;
                        }
                    }
                }

                if (strpos($val, '$') !== false && strpos($key, '(') !== false) {
                    $val = preg_replace('#^'.$key.'$#', $val, $uri);
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
            $controller = ucfirst(strtolower($controller));
            if (is_dir($this->_controllerPath.$controller)) {
                if (count($uriElements) > 0) {
                    $this->controller = $controller . '\\' . ucfirst(array_shift($uriElements)) .'Controller';
                } else {
                    $this->controller = $controller . '\IndexController';
                }
                $this->module = $controller;
            } else {
                $this->controller = $controller.'Controller';
                if (!file_exists($this->_controllerPath.$this->controller.'.php')) {
                    $this->controller = 'IndexController';
                    $action = $controller;
                }
            }
               
            $action = !empty($action) ? $action : array_shift($uriElements);
            if (!empty($action)) {
                $this->action = strtolower(preg_replace('/[\'";!@#$%^&*()[]=|{}:;.,?`~<> ]/i', '', $action));
                // make camelcase name it there is "_" between words
                $this->action = preg_replace_callback('/[-]([a-z])/i', function ($matches) {
                    return ucfirst($matches[1]);
                }, $this->action);
            }
            if (count($uriElements) > 0) {
                $uriElements = array_reverse($uriElements);
                if (count($uriElements) > 0  && preg_match('/\.htm|html$/i', $uriElements[0])) {
                    $page = array_shift($uriElements);
                    $this->params['page'] = str_replace(array('.html', '.htm'), '', $page);
                }
                $uriElements = array_reverse($uriElements);

                $i = 1;
                while (count($uriElements) > 0) {
                    $this->params['param'.$i] = array_shift($uriElements);
                    $i++;
                }
            } else {
                $this->params['page'] = 'index';
            }
        }
        $this->params['pageid']  = md5($this->params['lang'] . $this->controller . $this->action . $this->params['page']);
        
        return true;
    }
    
    /**
    * Get base url (relative)
    *
    * @return string
    */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /**
    * Get middleware name array applied to given route
    *
    * @return array
    */
    public function getMiddlewareTags()
    {
        return $this->_middlewareTags;
    }

    /**
    * Get HTTP method applied to given route
    *
    * @return string
    */
    public function getHttpMethod()
    {
        return $this->_httpMethod;
    }

    /**
    * Get cache settings for the given route
    *
    * @return array
    */
    public function getCacheSettings()
    {
        return $this->_cacheSettings;
    }

    /**
    * Normalize middleware tag list to array
    *
    * @param string|array  $middlewareTags - comma separated tags or array
    * @return array
    */
    protected function _normalizeMiddlewareTags($middlewareTags)
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
