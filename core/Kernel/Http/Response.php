<?php
namespace Kernel\Http;

/**
 * Response class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
class Response
{
    /** @var \Response */
    private static $instance = null;

    /** @var array */
    protected $cookies = [];

    /** @var array */
    protected $headers = [];

    /** @var array */
    protected $body = [];
    
    /** @var array */
    protected $codes = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];
    
    /**
    * Get object instance
    *
    * @return \Response
    */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Response();
        }

        return self::$instance;
    }

    /* RESSTRICT methods for making singleton */
    private function __construct()
    {
    }

    protected function __clone()
    {
    }

    /**
    * Set response header with code
    *
    * @param int $code
    */
    public function responseCodeHeader($code)
    {
        $sapi_name = php_sapi_name();

        if (isset($this->codes[$code])) {
            if ($sapi_name === 'cgi' || $sapi_name === 'cgi-fcgi') {
                header('Status: ' . $this->codes[$code]);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' ' . $code . ' ' . $this->codes[$code]);
            }
        }
    }

    /**
    * Get response code description
    *
    * @param int $code
    * 
    * @return string;
    */
    public function getResponseCodeDescription($code)
    {
        if (isset($this->codes[$code])) {
            $description = trim(str_replace($code, '', $this->codes[$code]));
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
        $this->headers[$key] = $value;
    }

    /**
    * Get header entry
    *
    * @param string $key
    * 
    * @return string
    */
    public function getHeader($key)
    {
        $key = str_replace('_', '-', strtolower($key));

        return array_key_exists($key, $this->headers) ? $this->headers[$key] : null;
    }

    /**
    * Set array of headers
    *
    * @param array $headers
    */
    public function setHeaders($headers)
    {
        foreach ($headers as $key => $val) {
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
        return $this->headers;
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
    public function setCookie(
        $name, 
        $value, 
        $expire = 0, 
        $path = '/', 
        $domain = '', 
        $secure = false, 
        $httponly = false,
        $samesite = 'Lax'
    ) {
        $data = compact('name', 'value', 'expire', 'path', 'domain', 'secure', 'httponly', 'samesite');
        $this->cookies[$data['name']] = $data;
    }

    /**
    * Get all cookies to be send
    *
    * @return array
    */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
    * Set rendered content into response body
    *
    * @param string $content
    */
    public function setBody($content)
    {
        $this->body[] = $content;
    }

    /**
    * Get body
    *
    * @return array
    */
    public function getBody()
    {
        return $this->body;
    }

    /**
    * Set content body as JSON
    */
    public function json($content)
    {
        if (is_array($content)) {
            $this->setHeader('Content-Type', 'application/json');
            $this->body[] = json_encode($content);
        }
        
        return $content;
    }

    /**
    * Redirects
    *
    * @param string $url
    */
    public function redirect($url = null)
    {
        $this->responseCodeHeader(301);

        if (!$url) {
            header('Location: index.php');
        } elseif (preg_match('#^http#i', $url)) {
            header('Location: ' . $url);
        } else {
            header('Location: ' . \Config::get('app.base_url') . '/' . ltrim($url, '/'));
        }

        exit(0);
    }
}
