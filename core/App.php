<?php

/**
 * Application class
 * Facade (static calls) for Kernel\Kernel instance
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class App extends \Facade
{
    /**
     * Returns object instance
     *
     * @return object
     */
    public static function getObjectInstance()
    {
        return \Kernel\Kernel::getInstance();
    }
}
