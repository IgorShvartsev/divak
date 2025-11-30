<?php
namespace Library\Api\Rest\Client;

use GuzzleHttp\Client;
use ReflectionMethod;
use RuntimeException;

/**
 * RestClient class
 * 
 * Example:
 *   $client = new RestClient(
 *       'Example',  // folder where controller classes exist, e.g. Library\Api\Rest\Client\Example
 *       ['base_uri' => 'http://sample-domain.com/api/']
 *   );
 *
 *   $result = $client->query(
 *       'user',    // controller class Library\Api\Rest\Client\Example\User
 *       'signin',  // method of Library\Api\Rest\Client\Example\User class
 *       [
 *           'login' => 'admin@test.com',
 *           'password' => '12345678',
 *       ]
 *   );
 * 
 * @author Igor Shvartsev <igor.shvartsev@xloo.com>
 */
class RestClient
{   
    /**
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var array
     */
    protected $controllerInstances = [];

    /**
     * @var array
     */
    protected $fixedPayload = [];

    /**
     * Constructor
     * 
     * @param string $type
     * @param array $options see https://docs.guzzlephp.org/en/stable/request-options.html
     * @throws RuntimeException
     */
    public function __construct($type, array $options = [])
    {
        $this->type = ucfirst($type);
        
        if (empty($options['base_uri'])) {
            throw new RuntimeException('"base_uri" is not defined in $options parameter');
        }
        
        $this->baseUri = $options['base_uri'];

        $defaultOptions = [
            'allow_redirects' => [
                'max' => 2,
                'strict' => true,
            ],
            'timeout' => 60,
        ];
        $options = array_merge($defaultOptions, $options);
        $this->client = new Client($options);
    }

    /**
     * Query
     * 
     * @param string $controller class 
     * @param string $action class method
     * @param array $payload data to send
     * @param array $options additional as "headers", "auth", "body", "cert", "stream" etc.
     * 
     * @return mixed
     * @throws RuntimeException
     */
    public function query($controller, $action, array $payload = [], array $options = [])
    {
        $class = $this->getControllerClass($controller);

        if (!array_key_exists($class, $this->controllerInstances)) {
            if (!class_exists($class)) {
                throw new RuntimeException('Class ' . $class . ' not found');
            } 

            $this->controllerInstances[$class] = new $class($this->client);
        }
        
        $instance = $this->controllerInstances[$class];

        if (!method_exists($instance, $action)) {
            throw new RuntimeException('Class ' . $class . ' method ' . $action . ' not found');
        }

        $reflection = new ReflectionMethod($instance, $action);

        if (!$reflection->isPublic()) {
            throw new RuntimeException('The called method ' . $action . ' is not public.');
        }

        $payload = array_merge($this->fixedPayload, $payload);
        
        return $instance->$action($payload, $options);
    }

    /**
     * Set additional payload that will be fixed for every request
     * 
     * @param array $payload
     */
    public function setFixedPayload(array $payload = [])
    {
        $this->fixedPayload = $payload;
    }

    /**
     * Get base uri
     * 
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }   

    /**
     * Delete additional payload 
     * 
     */
    public function deleteFixedPayload()
    {
        $this->fixedPayload = [];
    }

    /**
     * Get Controller class
     * 
     * Can be overidden to have your own path to controller class
     * 
     * @param string $module
     * @return string
     */
    protected function getControllerClass($controller)
    {
        return '\\Library\\Api\\Rest\\Client\\' . $this->type . '\\' . ucfirst($controller);
    }
}
