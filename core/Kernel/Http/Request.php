<?php
namespace Kernel\Http;

/**
* Request class
* 
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Request
{	
    /** REQUEST TYPES */
    const HTTP_TYPE_GET      = 1;
    const HTTP_TYPE_POST     = 2;
    const HTTP_TYPE_COOKIE   = 3;
    const HTTP_TYPE_REQUEST  = 4;
    const HTTP_TYPE_JSON     = 5;
    const HTTP_TYPE_PARAMS   = 6;

    /** @var \Request */
	  private static $_instance = null;
    /** @var [] */
    protected $_post = [];
    /** @var [] */
    protected $_get = [];
    /** @var [] */
    protected $_cookie = [];
    /** @var [] */
    protected $_request = [];
    /** @var [] */
    protected $_json = [];
    /** @var [] */
    protected $_params = [];


    /* RESSTRICT methods for making singleton */
    private function __construct(){}
    protected function __clone(){}
   
    /**
    * Get array of json params
    *
    * @return array
    */
    public function getAllJson()
    {
        return $this->_json;
    }

    /**
    * Gets cookie param
    * 
    * @param string $name
    * @param string $default
    * @return mixed
    */
    public function getCookie($name, $default = null)
    {
        return isset($this->_cookie[$name]) ? $this->_cookie[$name] : $default; 
    }

    /**
    * Gets param from $_REQUEST
    * 
    * @param string $name
    * @param string $default
    * @return mixed
    */
    public function getFromRequest($name, $default = null)
    {
        return isset($this->_request[$name]) ? $this->_request[$name] : $default; 
    }

    /**
    * Get object instance
    *
    * @return \Request
    */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new Request();
        }
        return self::$_instance;
    }

    /**
    * Gets JSON param 
    * 
    * @param string $name
    * @param string $default
    * @return mixed
    */
    public function getJson($name, $default = null)
    {
        return isset($this->_json[$name]) ? $this->_json[$name] : $default; 
    }
   
    /**
    * Gets GET param
    * 
    * @param string $name
    * @param string $default
    * @return mixed
    */
    public function getQuery( $name, $default = null)
    {
        return isset($this->_get[$name]) ? $this->_get[$name] : $default; 
    }
   
    /**
    * Gets param
    * 
    * @param string $name
    * @param string $default
    * @return mixed
    */
    public function getParam( $name, $default = null)
    {
        return isset($this->_params[$name]) ? $this->_params[$name] : $default; 
    }

    /**
    * Get all parameters
    *
    * @return array 
    */
    public function getParams() 
    {
        return $this->_params;
    }

    /**
    * Gets POST param
    * 
    * @param string $name
    * @param string $default
    * @return mixed
    */
    public function getPost( $name, $default = null)
    {
        return isset($this->_post[$name]) ? $this->_post[$name] : $default; 
    }

    /**
    * Checks if request is GET
    * 
    * @return boolean
    */
    public function isGet()
    {
        return count($this->_get) > 0;  
    }
   
    /**
    * Checks if request is POST
    * 
    * @return boolen
    */
    public function isPost()
    {
        return count($this->_post) > 0;  
    }
    
    /**
    * Checks if request is XmlHttp
    * 
    * @return boolean
    */
    public function isXmlHttpRequest()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    /**
    * Set data for specific request type
    * 
    * @return void
    */
    public function set($data, $type)
    {    
      switch($type) {
        case self::HTTP_TYPE_GET:
            $this->_get = (array)$data;
            break;
        case self::HTTP_TYPE_POST:
            $this->_post = (array)$data;
            break;
        case self::HTTP_TYPE_COOKIE:
            $this->_cookie = (array)$data;
            break;
        case self::HTTP_TYPE_REQUEST:
            $this->_request = (array)$data;
            break;
        case self::HTTP_TYPE_JSON:
            $this->_json = (array)$data;
            break;
        case self::HTTP_TYPE_PARAMS:
            $this->_params = (array)$data;
            break;
        default:
            throw new \Exception('Request type is unknown');
      }
   }
}
