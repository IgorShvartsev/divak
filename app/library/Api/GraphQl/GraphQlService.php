<?php
namespace Library\Api\GraphQl;

use Library\Api\GraphQl\Client\GqlClient;

/**
 * GraphQlService Fabric class
 * Serves as container for instances  
 */
class GraphQlService
{   
    /**
     * @var array
     */
    protected static $clients = [];

    /**
     * Config should contain 2 required keys: $config['endpointUrl'] and $config['htmlOptions']
     * About htmlOptions see https://docs.guzzlephp.org/en/latest/request-options.html
     * @var array
     */
    protected static $config = [];

    /**
     * Get client
     * 
     * @param string $providerClassName (or short, etc that is implemented as Client)
     * @param array $config if empty default static::$config is used
     * @return GqlClient
     */
    public static function getClient($type, array $config = [])
    {
        if (!array_key_exists($type, static::$clients)) {
            static::$clients[$type] = new GqlClient($type, $config ?? static::$config);
        }

        return static::$clients[$type];

    }

    /**
     * Set config
     * 
     * @param array $config
     */
    public static function setConfig(array $config)
    {
        static::$config = $config;
    }
}
