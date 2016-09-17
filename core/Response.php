<?php

/**
* Response class
* Facade (static calls) for Kernel\Http\Response instance
* 
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Response extends \Facade
{
  /**
  *  Returns object instance
  *
  *  @return Object
  */
  public static function getObjectInstance()
  {
    return \App::make(\Kernel\Http\Response::class);
  }
}
