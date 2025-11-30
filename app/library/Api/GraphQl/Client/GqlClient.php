<?php
namespace Library\Api\GraphQl\Client;

use Library\Api\GraphQl\Client\Contract\ClientInterface;
use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GuzzleHttp\Exception\RequestException;
use RuntimeException;

/**
 * GraphQl Client class
 * 
 * Example:
 *  $client = new GqlClient(
 *      'Example', // defined folder path, e.g. Library\Api\GraphQl\Client\Example
 *      [
 *          'endpointUrl' => 'http://sample-domain.com/api/graphql',
 *          'htmlOptions' => []
 *      ]
 *  );
 *
 *  try {
 *      $result = $client->mutation(
 *          'login', 
 *          [
 *              'login' => 'test@dxloo.com', 
 *              'password' => '12345678'
 *          ]
 *      );
 *  } catch (\GraphQL\Exception\QueryError $exception) {
 *      \Log::fatal($exception->getErrorDetails());
 *      exit;
 *  }
 * 
 * @see https://github.com/mghoneimy/php-graphql-client
 * @author Igor Shvartsev <igor.shvartsev@xloo.com>
 */
class GqlClient extends Client implements ClientInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * Constructor
     * 
     * @param string $type defines classes path 
     *    \GraphQl\Client\$type\Query
     *    \GraphQl\Client\$type\Mutation
     * @param array $config  Config should contain 2 required keys: 
     *    $config['endpointUrl'] and $config['htmlOptions']
     *    About htmlOptions see https://docs.guzzlephp.org/en/latest/request-options.html
     * @throws RuntimeException
     */
    public function __construct($type = '', array $config = [])
    {
        $this->type = ucfirst($type);
        $endpointUrl = $config['endpointUrl'] ?? '';
        $htmlOptions = $config['htmlOptions'] ?? [];
        
        if (empty($endpointUrl)) {
            throw new RuntimeException('$config["endpointUrl"] is not defined');
        }

        parent::__construct($endpointUrl, [], $htmlOptions, null, 'POST', null);
    }

    /**
     * Query
     * 
     * @param string $method
     * @param array $payload
     * 
     * @return mixed
     */
    public function query($method, array $payloads = [])
    {
        $class = $this->getQueryClass($method);
        return $this->sendRequest($class, $payloads);
    }

    /**
     * Mutation
     * 
     * @param string $method
     * @param array $payload
     * 
     * @return mixed
     */
    public function mutation($method, array $payloads = [])
    {
        $class = $this->getMutationClass($method);
        return $this->sendRequest($class, $payloads);
    }

    /**
     * Set Bearer token
     * 
     * @param string $token
     */
    public function setBearerToken($token)
    {
        if (!empty($token)) {
            $this->httpHeaders['Authorization'] = 'Bearer ' . $token;
        }
    }

    /**
     * Send request
     * 
     * @param string $class
     * @param array $payloads 
     * @return mixed array - good result, false - Query error, null - Runtime error
     */
    protected function sendRequest($class, array $payloads = [])
    {   

        list($gql, $variables) = $this->generateQueryParams($class, $payloads);

        try {
            $result = $this->runQuery($gql, true, $variables);
        } catch (QueryError $e) {
            \Log::error(__METHOD__ . '[' . __LINE__ . ']: ' . $e->getErrorDetails()['message']);
            return false;
        } catch (RequestException $e) {
            \Log::error(__METHOD__ . '[' . __LINE__ . ']: ' . $e->getMessage());
            return;
        }

        $result->reformatResults(true);
        
        return $result->getData();
    }

    /**
     * Get query class
     * 
     * @param string $method
     * @return string
     */
    protected function getQueryClass($method)
    {
        return '\\Library\\Api\\GraphQl\\Client\\' . $this->type . '\\Query\\' . ucfirst($method);
    }
    
    /**
     * Get mutation class
     * 
     * @param string $method
     * @return string
     */
    protected function getMutationClass($method)
    {
        return '\\Library\\Api\\GraphQl\\Client\\' . $this->type . '\\Mutation\\' . ucfirst($method);
    }

    /**
     * generateQueryParams
     * 
     * @param string $class
     * @return array [\GraphQL\Query(\GraphQL\Mutation), array]
     * @throws RuntimeException
     */
    protected function generateQueryParams($class, array $payload = [])
    {
        $result = [];

        if (class_exists($class)) {
            $builder = new $class();
            // generate \GraphQL\Mutation or \GraphQL\Query object
            $result[] = $builder->build($payload);
            // retrieve variables
            $result[] = $builder->getVariables();
        } else {
            throw new RuntimeException('Class ' . $class . ' not found');
        }
        
        return $result;
    }   
}
