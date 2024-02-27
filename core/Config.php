<?php

/**
 *  Configuration class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class Config
{
    /**
     * @var string
     */
    protected static $confDir = '/../config';

    /**
     * @var array
     */
    protected static $confData = [];

    /**
     * Initialize data collection from files inside configuration directory
     *
     * @throws Exception
     */
    public static function init()
    {
        self::$confData = [];

        $confDir = __DIR__ . self::$confDir;

        if (is_dir($confDir)) {
            self::setConfig($confDir);
        } else {
            throw new \Exception('Config directory not found ' . $confDir);
        }
    }


    /**
     * Get value based on key from configuration data
     *
     * @return mixed
     * 
     * @throws Exception
     */
    public static function get($key = null)
    {
        if (!$key) {
            return self::$confData;
        }

        $arr = explode('.', $key);
        $curKey = [];
        $isFirst = false;

        foreach ($arr as $subKey) {
            if (!$isFirst) {
                if (!isset(self::$confData[$subKey])) {
                    throw new \Exception('Key not found in config ' . $key);
                }

                $curKey = self::$confData[$subKey];
                $isFirst = true;
                continue;
            }

            if (!isset($curKey[$subKey])) {
                throw new \Exception('Key not found in config ' . $key);
            }
            
            $curKey = $curKey[$subKey];
        }
        
        return $curKey;
    }

    /**
    * Set value based on key path specified by "dot" notation
    *
    * @param string $dottedKey
    * @param mixed $value
    *
    */
    public static function set($dottedKey, $value)
    {
        $keys = explode('.', $dottedKey);

        if (!count($keys)) {
            return;
        }

        self::$confData = array_replace_recursive(
            self::$confData, 
            self::arrayFromKeys($keys, $value)
        );
    }

    /**
     *  Set config
     * 
     * @param string $path
     */
    protected static function setConfig($path)
    {
        $dir = dir($path);

        while (false !== ($entry = $dir->read())) {
            $entryPath = rtrim($path, '/') . '/' . $entry;

            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (is_dir($entryPath)) {
                self::setConfig($entryPath);
            } 

            if (is_file($entryPath)) {
                $key = basename($entryPath, '.php');
                $data = require_once $entryPath;

                if (!is_array($data)) {
                    throw new \Exception('Data is not an array type in ' . $entryPath);
                }
                
                if (!empty(self::$confData[$key])) {
                    self::$confData[$key] = array_merge(self::$confData[$key], $data);
                } else {
                    self::$confData[$key] = $data;
                }
            }
        }
    }  

    /**
     * Create array from array of keys which is an multidimensional index 
     * 
     * @param array $keys
     * @param mixed $value
     * 
     * @return array 
     */ 
    protected static function arrayFromKeys(array $keys, $value)
    {
        $result = [];

        if (empty($keys)) {
            return $result;
        }

        $idx = array_shift($keys);

        if (empty($keys)) {
            $result[$idx] = $value;
        } else {   
            $result[$idx] = self::arrayFromKeys($keys, $value);            
        }

        return $result;
    }
}
