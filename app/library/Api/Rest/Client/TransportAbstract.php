<?php
namespace Library\Api\Rest\Client;

use Library\Api\Rest\Client\Enum\ResponseTypeEnum;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Utils;
use RuntimeException;

/**
 * TransportAbstract class
 * 
 */
abstract class TransportAbstract
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * @var string one of \GuzzleHttp\RequestOptions
     */
    protected $requestType;

    /**
     * @var string one of Library\Api\Rest\Client\Enum\ResponseTypeEnum
     */
    protected $responseType;

    /**
     * @var string one of Library\Api\Rest\Client\Enum\ResponseMethodEnum
     */
    protected $requestMethod;

    /**
     * Constructor
     * 
     * @param ClientInterface $client
     * @param string $url
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->initProperties();
    }

    /**
     * @var array
     */
    protected $possibleRequestTypes = [
        RequestOptions::FORM_PARAMS,
        RequestOptions::JSON,
        RequestOptions::MULTIPART,
        RequestOptions::QUERY,
    ];

    /**
     * Send request
     * 
     * @param string $uri
     * @param array $payload
     * @param array $options
     * @return mixed 
     */
    public function sendRequest($uri, array $payload = [], array $options = [], array $requiredParams = [])
    {
        $this->checkProperties();
        $this->checkRequiredParams($requiredParams, $payload);

        $params = [];
        $params[$this->requestType] = $payload;
        $params = array_merge($options, $params);

        $response = $this->client->request($this->requestMethod, $uri, $params);

        $result = $response->getBody()->getContents();

        if ($this->responseType === ResponseTypeEnum::JSON) {
            $result = Utils::jsonDecode($result, true);
        }
        
        return $result;
    }

    /**
     * Check this class properties
     * 
     * @throws RuntimeException;
     */
    protected function checkProperties()
    {
        if (empty($this->client)) {
            throw new RuntimeException('Property $client is not defined');
        }

        if (empty($this->requestType)) {
            throw new RuntimeException('Property $requestType is not defined');
        }

        if (!in_array($this->requestType, $this->possibleRequestTypes)) {
            throw new RuntimeException('Property $requestType = ' . $this->requestType . ' is not valid');
        }

        if (empty($this->responseType)) {
            throw new RuntimeException('Property $responseType is not defined');
        }

        if (empty($this->requestMethod)) {
            throw new RuntimeException('Property $requestMethod is not defined');
        }
    }

    /**
     * Validate required parameters
     * 
     * @param array $params
     * @param array $payload
     * @throws RuntimeException;
     */
    protected function checkRequiredParams(array $params = [], array $payload = [])
    {
        $missedParams = [];

        foreach ($params as $param) {
            if (!array_key_exists($param, $payload)) {
                $missedParams[] = $param;
            }
        }

        if (!empty($missedParams)) {
            throw new RuntimeException('Missed params: ' . implode(',', $missedParams));
        }
    }

    /**
     * Init properties
     */
    abstract protected function initProperties();
}