<?php
namespace Library\Api\Rest\Client\Example;

use Library\Api\Rest\Client\TransportAbstract;
use Library\Api\Rest\Client\Traits\QueryGetJson;

/**
 * Post controller class
 * 
 */
class Posts extends TransportAbstract
{   
    use QueryGetJson;

    /**
     * Posts
     * api url base_uri/post
     * 
     * @param array $payload
     * @param array $options request options
     * @return array
     */
    public function get(array $payload = [], array $options = [])
    {
        $id = !empty($payload['id']) ? $payload['id'] : 0;

        return $this->sendRequest(
            'posts/' . $id, 
            $payload, 
            $options,
            []
        );
    }
}
