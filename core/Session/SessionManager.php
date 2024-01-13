<?php
namespace Session;

/**
 * Session Manager class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class SessionManager
{
    /**
     * @var \SessionHandlerInterface
     */
    protected static $handler;

    /**
     * Set session handler
     *
     * @param string $type
     * @param array $options
     * 
     * @throws \RuntimeException
     */
    public static function setHandler($type = 'file', $options = [])
    {
        $handleClass = '\\Session\\Handler\\' . ucfirst($type) . 'Handler';
        $filePath = str_replace('\\', '/', $handleClass) . '.php';
        
        $fileHandlers = [
            APP_PATH . '/core' . $filePath , // custom implementation in app/core/Session/Handle dir
            dirname(__FILE__) . '/..' . $filePath  // core implemetation
        ];

        foreach ($fileHandlers as $fileHandler) {
            if (file_exists($fileHandler)) {
                require_once $fileHandler;
                self::$handler = new $handleClass($options);

                if (!static::$handler instanceof \SessionHandlerInterface) {
                    throw new \RuntimeException(
                        'Session handler doesn\'t implement "SessionHandlerInterface" in '. $fileHandler
                    );
                }

                session_set_save_handler(static::$handler, true);
                return;
            }
        }

        throw new \RuntimeException('File doesn\' exsist: ' . $fileHandler);
    }
}
