<?php

namespace Session;

/**
* Session Manager class
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class SessionManager
{
    /**
    * @var SessionHandlerInterface
    */
    protected static $_handler;

    /**
    * Set session handler
    *
    * @param string $type
    */
    public static function setHandler($type = 'file', $options = [])
    {
        $handleClass = '\\Session\\Handler\\' . ucfirst($type).'Handler';
        $filePath = str_replace('\\', '/', $handleClass) . '.php';
        
        $fileHandlers = [
            APP_PATH .'/core'. $filePath ,        // custom implementation in app/core/Session/Handle dir
            dirname(__FILE__) . '/..'. $filePath  // core implemetation
        ];

        foreach ($fileHandlers as $fileHandler) {
            if (file_exists($fileHandler)) {
                require_once($fileHandler);
                self::$_handler = new $handleClass($options);
                if (!static::$_handler instanceof \SessionHandlerInterface) {
                    throw new \RuntimeException('Session handler doesn\'t implement "SessionHandlerInterface" in '. $fileHandler);
                }
                session_set_save_handler(static::$_handler, true);
                return;
            }
        }

        throw new \RuntimeException('File doesn\' exsist: ' . $fileHandler);
    }
}
