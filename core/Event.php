<?php

/**
 * Event class
 * Implements Facade pattern
 *
 * @author Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class Event extends \Facade
{   
    /**
     * Returns object instance
     *
     * @return object
     */
    public static function getObjectInstance()
    {
        return \App::make(\Kernel\EventDispatcher::class);
    }
}
