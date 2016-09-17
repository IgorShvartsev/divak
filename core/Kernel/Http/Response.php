<?php

namespace Kernel\Http;

/**
* Response class
* 
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Response
{
    /** @var \Response */
	private static $_instance = null;

    /** @var array */
    protected $_cookies = [];

    /** @var array */
    protected $_headers = [];

    /** @var array */
    protected $_body = [];
    
    /** @var array */
    protected $_codes = [
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily',
        400 => 'Bad Request',
        401 => 'Authorization Required',
        403 => 'Access forbidden',
        404 => 'Page not found', 
        405 => 'Method Not Allowed',
        408 => 'Request Timed Out',
        415 => 'Unsupported Media Type',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];
	
    /**
    * Get object instance
    *
    * @return \Response
    */
	public static function getInstance()
    {
        if (!self::$_instance) {
           self::$_instance = new Response();
        }
        return self::$_instance;
    }

    /* RESSTRICT methods for making singleton */
    private function __construct(){}
    protected function __clone(){}

    /**
    * Set response header with code
    * 
    * @param int $code
    */
    public function responseCodeHeader($code)
    {
       $sapi_name = php_sapi_name();
       if (isset($this->_codes[$code])) {
            if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
                header('Status: ' . $this->_codes[$code]);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' ' . $code . ' ' . $this->_codes[$code]);
            }
       }
    }

    /**
    * Get response code description
    * 
    * @param int $code
    * @return string;
    */
    public function getResponseCodeDescription($code) 
    {
        if (isset($this->_codes[$code])) {
            $description = trim(str_replace($code, '', $this->_codes[$code]));
            return $description; 
        }
        return '';
    }

    /**
    * Set header
    *  
    * @param string $key 
    * @param string $value 
    */
    public function setHeader($key, $value)
    {
        $key = str_replace('_', '-', strtolower($key));
        $this->_headers[$key] = $value;
    }

    /**
    * Get header entry
    * 
    * @param string $key
    * @return string
    */
    public function getHeader($key)
    {
        $key = str_replace('_', '-', strtolower($key));
        return array_key_exists($key, $this->_headers) ? $this->_headers[$key] : null; 
    }

    /**
    * Set array of headers
    *
    * @param array $headers
    */
    public function setHeaders($headers)
    {
        foreach($headers as $key => $val) {
            $this->setHeader($key, $val);
        }
    }

    /**
    * Get all headers
    *
    * @return array
    */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
    * Set cookie
    *
    * @param string $name
    * @param string $value
    * @param int $expire
    * @param string $path
    * @param string $domain
    * @param boolean $secure
    * @param boolean $httponly 
    */
    public function setCookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
       $data = compact('name', 'value', 'expire', 'path', 'domain', 'secure', 'httponly');
       $this->_cookies[$data['name']] = $data;
    }

    /**
    * Get all cookies to be send
    *
    * @return array
    */
    public function getCookies()
    {
        return $this->_cookies;
    }

    /**
    * Set rendered content into response body
    * 
    * @param string $content
    */
    public function setBody($content)
    {
        $this->_body[] = $content;
    }

    /**
    * Get body
    *
    * @return array
    */
    public function getBody()
    {
        return $this->_body;
    }

    /**
    * Set content body as JSON
    */
    public function json($content)
    {
        if (is_array($content)) {
            $this->setHeader('Content-Type', 'application/json');
            $this->_body[] = json_encode($content);
        }
        return $content;
    }

    /**
    * Redirects
    * 
    * @param string $url
    */
    public function redirect($url)
    {
        $this->responseCodeHeader(301);
        header('Location: ' . \Config::get('app.base_url') . '/' .ltrim($url, '/'));
        exit(0);
    }
}
