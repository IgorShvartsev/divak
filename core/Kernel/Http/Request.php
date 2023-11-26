<?php
namespace Kernel\Http;

/**
 * Request class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
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
    private static $instance = null;
    /** @var [] */
    private $headers = [];
    /** @var [] */
    protected $post = [];
    /** @var [] */
    protected $get = [];
    /** @var [] */
    protected $cookie = [];
    /** @var [] */
    protected $request = [];
    /** @var [] */
    protected $json = [];
    /** @var [] */
    protected $params = [];
    /** @var string */
    protected $httpMethod;

    /* RESSTRICT methods for making singleton */
    private function __construct()
    {
    }
    protected function __clone()
    {
    }
   
    /**
     * Get array of json params
     *
     * @return array
     */
    public function getAllJson()
    {
        return $this->json;
    }

    /**
     * Gets cookie param
     *
     * @param string $name
     * @param string $default
     * 
     * @return mixed
     */
    public function getCookie($name, $default = null)
    {
        return isset($this->cookie[$name]) ? $this->cookie[$name] : $default;
    }

    /**
     * Get given header
     *
     * @param string $headerName
     * 
     * @return string | null
     */
    public function getHeader($headerName)
    {
        return $this->headerExists($headerName) ? $this->headers[$headerName] : null;
    }

    /**
     * Get all request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Gets param from $_REQUEST
     *
     * @param string $name
     * @param string $default
     * 
     * @return mixed
     */
    public function getFromRequest($name, $default = null)
    {
        return isset($this->request[$name]) ? $this->request[$name] : $default;
    }

    /**
     * Get object instance
     *
     * @return \Request
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Request();
        }

        return self::$instance;
    }

    /**
     * Gets JSON param
     *
     * @param string $name
     * @param string $default
     * 
     * @return mixed
     */
    public function getJson($name, $default = null)
    {
        return isset($this->json[$name]) ? $this->json[$name] : $default;
    }

    /**
     * Get HTTP method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->httpMethod;
    }
   
    /**
     * Gets GET param
     *
     * @param string $name
     * @param string $default
     * 
     * @return mixed
     */
    public function getQuery($name, $default = null)
    {
        return isset($this->get[$name]) ? $this->get[$name] : $default;
    }
   
    /**
     * Gets param
     *
     * @param string $name
     * @param string $default
     * 
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }

    /**
     * Get all GET parameters
     *
     * @return array
     */
    public function getQueryParams()
    {
        return $this->get;
    }

    /**
     * Get all POST parameters
     *
     * @return array
     */
    public function getPostParams()
    {
        return $this->post;
    }

    /**
     * Get all JSON parameters
     *
     * @return array
     */
    public function getJsonParams()
    {
        return $this->json;
    }

    /**
     * Get all parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Gets POST param
     *
     * @param string $name
     * @param string $default
     * 
     * @return mixed
     */
    public function getPost($name, $default = null)
    {
        return isset($this->post[$name]) ? $this->post[$name] : $default;
    }

    /**
     * Check if given header is available
     *
     * @param  string $headerName
     * 
     * @return boolean
     */
    public function headerExists($headerName)
    {
        return array_key_exists($headerName, $this->headers);
    }

    /**
     * Checks if request is GET
     *
     * @return boolean
     */
    public function isGet()
    {
        return $this->getMethod() === 'GET';
    }
   
    /**
     * Checks if request is POST
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() === 'POST';
    }
    
    /**
     * Checks if request is XmlHttp
     *
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    }

    /**
     * Set data for specific request type
     *
     * @throws \Exception
     */
    public function set($data, $type)
    {
        switch ($type) {
            case self::HTTP_TYPE_GET:
                $this->get = (array)$data;
                break;
            case self::HTTP_TYPE_POST:
                $this->post = (array)$data;
                break;
            case self::HTTP_TYPE_COOKIE:
                $this->cookie = (array)$data;
                break;
            case self::HTTP_TYPE_REQUEST:
                $this->request = (array)$data;
                break;
            case self::HTTP_TYPE_JSON:
                $this->json = (array)$data;
                break;
            case self::HTTP_TYPE_PARAMS:
                $this->params = (array)$data;
                break;
            default:
                throw new \Exception('Request type is unknown');
        }
    }

    /**
     * Set headers
     *
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * Set HTTP method
     *
     * @param string $httpMethod
     */
    public function setMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;
    }
}
