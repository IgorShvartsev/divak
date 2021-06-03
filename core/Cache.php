<?php
use Cache\Exception\CacheException;

/**
 *  Cache class.
 *
 *  @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 *
 *  @version 1.1
 */
class Cache
{
    /**
     * Factory method
     * Constructs target class from cache type.
     *
     * @param string $type Type of used cache
     * @param array $options  Params for the created class
     * @param bool $autoload If autoload is used
     *
     * @return Cache\CacheAbstract
     * 
     * @throws CacheExeption
     */
    public static function factory($type, $options = [])
    {
        if (empty($type)) {
            throw new CacheExeption('Cache type is empty.');
        }

        $class = '\Cache\Cache' . $type;
        
        if (!class_exists($class)) {
            throw new CacheException('Class ' . $class . ' not defined.');
        }

        return new $class($options);
    }
}
