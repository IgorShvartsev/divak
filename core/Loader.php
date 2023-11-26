<?php

/**
 *  Class Loader
 *  Registers autoload function
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class Loader
{
    /**
     * @var array
     */
    protected static $directories = [];

    /**
     * @var boolean
     */
    protected static $isRegistered = false;

    /**
     * @var array 
     */
    protected static  $mcDirs = [
        'controllers',
        'models',
    ];

    /**
    * Regiser autoload function
    */
    public static function register()
    {
        if (!self::$isRegistered) {
            self::$isRegistered = spl_autoload_register(['Loader', 'loadClass']);
        }
    }

    /**
     * Load class function
     *
     * @param string $class
     * 
     * @return boolean
     */
    public static function loadClass($class)
    {
        if ($class[0] === '\\') {
            $class = substr($class, 1);
        }

        $class = str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $class) . '.php';

        foreach (self::$directories as $dir) {
            $file = $dir . DIRECTORY_SEPARATOR . $class;

            if (file_exists($file)) {
                require_once $file;
                return true;
            } else {
                $isMcLoaded = self::mcLoad($dir, $class);

                if ($isMcLoaded) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add directories to be scanned for autoload
     *
     * @param array $dirs
     */
    public static function addDirectories(array $dirs)
    {
        self::$directories = array_unique(array_merge(self::$directories, $dirs));
    }

    /**
     * Load php files fith functions from the given folder
     * 
     * @param string $functionFolder
     * 
     * @throws RuntimeException
     */ 
    public static function loadFunctions($functionFolder = null)
    {
        if (!empty($functionFolder) && is_dir($functionFolder)) {
            $functionFolder = rtrim($functionFolder, '/');
            $phpFiles = glob($functionFolder . '/*.php');

            if (!empty($phpFiles)) {
                foreach ($phpFiles as $phpFile) {
                    require_once $phpFile;
                }
            }
        } else {
            throw new \RuntimeException('Function folder doesn\'t exist: ' . $functionFolder);
        }
    }

    /**
     * mc load
     * 
     * @var string $dir
     * @var string $class
     * 
     * @return bool
     */
    protected static function mcLoad($dir, $class)
    {
        foreach (self::$mcDirs as $mcDir) {
            $file = $dir . DIRECTORY_SEPARATOR . $mcDir . DIRECTORY_SEPARATOR . $class;

            if (file_exists($file)) {
                require_once $file;
                return true;
            } else {
                $classParts = explode('/', $class);
                
                if (count($classParts) > 0 && preg_match('#^Modules$#i', $classParts[0])) {
                    $moduleFolder = strtolower(array_shift($classParts));
                    $moduleName = array_shift($classParts);
                    $file = $dir . DIRECTORY_SEPARATOR 
                        . $moduleFolder . DIRECTORY_SEPARATOR 
                        . $moduleName . DIRECTORY_SEPARATOR
                        . $mcDir . DIRECTORY_SEPARATOR 
                        . implode('/', $classParts);

                    if (file_exists($file)) {
                        require_once $file;
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
