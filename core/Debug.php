<?php

/**
*  Debug class
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Debug
{
    /**
    * Trace variable to be readable in convinient way on html page
    */
    public static function trace($var)
    {
        if (is_array($var) || is_object($var)) {
            echo '<pre>'.print_r($var, true).'</pre>';
        } else {
            echo $var;
        }
    }
}
