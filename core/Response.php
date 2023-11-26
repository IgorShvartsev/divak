<?php

/**
 * Response class
 * Facade (static calls) for Kernel\Http\Response instance
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
*/
class Response extends \Facade
{
    /**
     * Returns object instance
     *
     * @return object
     */
    public static function getObjectInstance()
    {
        return \App::make(\Kernel\Http\Response::class);
    }
}
