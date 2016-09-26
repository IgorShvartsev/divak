<?php

use \Cache\Exception\CacheException;

/**
*  Cache class
* 
*  @author  Igor Shvartsev (igor.shvartsev@gmail.com)
*  @package Divak
*  @version 1.0
*/
class Cache
{
    /**
    * Factory method
    * Constructs target class from cache type
    * 
    * @param string $type  Type of used cache 
    * @param array $options  Params for the created class
    * @param boolean $autoload If autoload is used
    * @return class
    */
    public static function factory( $type, $options = array())
    {
        if (empty($type)) {
            throw new CacheExeption('Cache type is empty.');
        }
        $class = '\Cache\Cache' . $type;
        if (!class_exists($class)) {
            throw new CacheException('Class '. $class .' not defined.');
        }
        return new $class($options);
    } 
    
    /**
    * isReadable
    * Checks if file is readable
    * 
    * @param string $filename
    */
    private static function _isReadable($filename)
    {
        if (!$fh = @fopen($filename, 'r', true)) {
            return false;
        }
        @fclose($fh);
        return true;
    }
}
