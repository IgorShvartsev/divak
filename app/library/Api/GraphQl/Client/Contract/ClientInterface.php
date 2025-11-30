<?php
namespace Library\Api\GraphQl\Client\Contract;

/**
 * Client interface
 */
interface ClientInterface
{
    /**
     * Query
     * 
     * @param string $method
     * @param array $payload
     * 
     * @return mixed
     */
    public function query($method, array $payload = []);

    /**
     * Mutation
     * 
     * @param string $method
     * @param array $payload
     * 
     * @return mixed
     */
    public function mutation($method, array $payload = []);
}