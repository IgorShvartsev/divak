<?php

/**
*  Database Facade
* 
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Db
{ 
   public static function connection($connectName = null)
   {	
   		$dbManager = \App::make(\Db\Manager::class);
   		if (empty($connectName)) {
			$connectionName = \Config::get('database.default');
		} 
		return $dbManager->getConnection($connectionName);
   }
}
