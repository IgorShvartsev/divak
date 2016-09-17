<?php

/**
* Request class
* Facade (static calls) for Kernel\Http\Request instance
* 
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Request extends \Facade
{	
  /**
  *  Returns object instance
  *
  *  @return Object
  */
  public static function getObjectInstance()
  {
    return \App::make(\Kernel\Http\Request::class);
  }
}
