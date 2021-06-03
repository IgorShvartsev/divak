<?php

/**
 *  Database Facade
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
class Db
{
    /**
     * Make connection to DB
     * 
     * @param string $connectionName
     * 
     * @return \Db\DbAbstract
     */
    public static function connection($connectName = null)
    {
        $dbManager = \App::make(\Db\Manager::class);
        
        if (empty($connectName)) {
            $connectionName = \Config::get('database.default');
        }
        
        return $dbManager->getConnection($connectionName);
    }
}
