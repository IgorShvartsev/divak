<?php

/**
*  Configuration class
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Config
{
    protected static $_confDir = '/../config';

    protected static $_confData = [];

    /**
    * Initialize data collection from files inside configuration directory
    *
    * @throws Exception
    */
    public static function init()
    {
        self::$_confData = [];
        $confDir = dirname(__FILE__) . self::$_confDir;
        if (is_dir($confDir)) {
            $dir = dir($confDir);
            while (false !== ($entry = $dir->read())) {
                $entryPath = rtrim($confDir, '/') . '/' . $entry;
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                if (is_dir($entryPath)) {
                    continue;
                }  // lets not handle files inside subdirs for now
                if (is_file($entryPath)) {
                    $key = basename($entryPath, '.php');
                    $data = require_once $entryPath;
                    if (!is_array($data)) {
                        throw new \Exception('Data is not an array type in ' . $entryPath);
                    }
                    self::$_confData[$key] = $data;
                }
            }
        } else {
            throw new \Exception('Config directory not found ' . $confDir);
        }
    }

    /**
    * Get value based on key from configuration data
    *
    * @return mixed
    * @throws Exception
    */
    public static function get($key)
    {
        $arr = explode('.', $key);
        $curKey = [];
        $isFirst = false;
        foreach ($arr as $subKey) {
            if (!$isFirst) {
                if (!isset(self::$_confData[$subKey])) {
                    throw new \Exception('Key not found in config ' . $key);
                }
                $curKey = self::$_confData[$subKey];
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
}
