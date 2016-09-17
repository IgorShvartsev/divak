<?php

namespace Kernel;

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
    protected $controllerPath = '';
    
    
    /**
    * Base Url
    * 
    * @var string
    */
    protected $baseUrl = '';
    
    /**
    * Constructor
    * 
    */
    public function __construct()
    {
        if (!empty(\Config::get('app.default_controller')))
        {
        	$controller = str_ireplace('Controller', '', \Config::get('app.default_controller'));
            $this->controller = ucfirst($controller).'Controller';    
        }
        if (!empty(\Config::get('app.base_url'))) {
            $baseUrl =  '/'.trim(\Config::get('app.base_url'), '/'); 
            $this->baseUrl = $baseUrl == '/' ? '' : $baseUrl; 
        }
        $this->controllerPath = APP_PATH . '/controllers/';
    }
    
    /**
    * Parses url string
    * 
    * @param string $uri
    */
    public function parseUrl($uri)
    {
        // default param 
        $action  = '';
        $pattern = str_replace(array('/', '.', ',' ,';'),array('\\/', '\\.', '\\,', '\\;'), $this->baseUrl);
        $uri = preg_replace('$'.$pattern.'$i', '', $uri);
        $uri = preg_replace('/\?.*$/', '', $uri);
        $uri = trim($uri, '/');

        // extract lang param
        if (($pos = strpos($uri, '/')) !== false && $pos == 2) {
            $this->params['lang'] = strtolower(substr($uri, 0, $pos));
            $uri = substr($uri, $pos + 1);
        } else if (strlen($uri) == 2) {
            $this->params['lang'] = strtolower($uri);
            $uri = '';  
        } else {
            $this->params['lang'] = strtolower(\Config::get('app.default_language'));
        }
           
        // regular expression uri mask  
        $routes = !empty(\Config::get('route')) ? \Config::get('route') : [];        
        if (!isset($routes[$uri])) {
            foreach ($routes as $key => $val)
            {                        
                $key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
                if (preg_match('#^'.$key.'$#', $uri)) {           
                    if (strpos($val, '$') !== false && strpos($key, '(') !== false) {
                        $val = preg_replace('#^'.$key.'$#', $val, $uri);
                    }
                    $uri = $val;        
                    break;
                }
            }            
        } else {
            $uri = $routes[$uri];
        }

        $this->params['page'] = '';
           
        $uriElements = array_values(array_filter(explode('/', $uri)));
        if (count($uriElements) > 0 ) {
            $controller = array_shift($uriElements);
            $controller = ucfirst(strtolower($controller));
            if (is_dir($this->controllerPath.$controller)) {
                if (count($uriElements) > 0) {
                    $this->controller = $controller . '\\' . ucfirst(array_shift($uriElements)) .'Controller';
                } else {
                    $this->controller = $controller . '\IndexController';
                }
                $this->module = $controller;
            } else {
                $this->controller = $controller.'Controller';
                if (!file_exists($this->controllerPath.$this->controller.'.php')) {
                    $this->controller = 'IndexController';
                    $action = $controller;
                }
            }
               
            $action = !empty($action) ? $action : array_shift($uriElements);
            if (!empty($action)) {
                $this->action = strtolower(preg_replace('/[\'";!@#$%^&*()[]=|{}:;.,?`~<> ]/i','', $action));
                // make camelcase name it there is "_" between words
                $this->action = preg_replace_callback('/_([a-z])/i', function($matches){
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
                while(count($uriElements) > 0) {
                    $this->params['param'.$i] = array_shift($uriElements);
                    $i++;
                }
            } else {
                $this->params['page'] = 'index';
            }
        }
        $this->params['pageid']  = md5($this->params['lang'] . $this->controller . $this->action . $this->params['page']);
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
}
