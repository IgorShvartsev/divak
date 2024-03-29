<?php

/**
 * Facade class
 * Wrapper for object class allowing to call methods as static
 *
 * @author  Igor Shvartsev(igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class Facade
{
    /**
     * Returns object instance
     * Should be overriden
     *
     * @return object
     * 
     * @throws RuntimeException
     */
    public static function getObjectInstance()
    {
        throw new \RuntimeException('Facade doesn\'t implement getObjectInstance method');
    }

    /**
     * Static calls to object
     *
     * @param  string  $method
     * @param  array   $args
     * 
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getObjectInstance();
        
        return call_user_func_array([$instance, $method], $args);
    }
}
