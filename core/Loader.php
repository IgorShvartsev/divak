<?php

/**
*  Class Loader
*  Registers autoload function
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Loader
{
    protected static $directories = [];

    protected static $isRegistered = false;

    /**
    * Regiser autoload function
    */
    public static function register()
    {
        if (!self::$isRegistered) {
            self::$isRegistered = spl_autoload_register(array('Loader', 'loadClass'));
        }
    }

    /**
    * Load class function
    *
    * @param string $class;
    * @return boolean
    */
    public static function loadClass($class)
    {
        if ($class[0] === '\\') {
            $class = substr($class, 1);
        }
        $class = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $class) . '.php';
        foreach (self::$directories as $dir) {
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $class)) {
                require_once $file;
                return true;
            }
        }

        return false;
    }

    /**
    * Add directories to be scanned for autoload
    *
    * @param array $dirs
    * @return void
    */
    public static function addDirectories($dirs)
    {
        self::$directories = array_unique(array_merge(self::$directories, (array)$dirs));
    }
}
