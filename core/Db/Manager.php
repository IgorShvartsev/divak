<?php

namespace Db;

use \Kernel\Exception\KernelException;
use \Db\Exception\DbException;
use \Db\PdoDriver;

/**
* DB manager
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Manager
{
    
    /**
    * Connection pool
    * @var array
    */
    protected $pool = [];
    
    /**
    * Connect to DB with given driver
    * Succesful connection is added to Pool
    *
    * @param array $dbParams array DB credentials
    * @param string $connectName connection name
    * @param string $dbDriverName default is PDO
    */
    public function connect($dbParams, $connectName, $dbDriverName = null)
    {
        $params = [];
        foreach (['adapter', 'database', 'host', 'user', 'password'] as $k) {
            if (isset($dbParams[$k])) {
                $params[$k] = $dbParams[$k];
            } else {
                throw new DbException('Db parameter "' . $k . '" is not defined');
            }
        }

        if (empty($connectName)) {
            throw new DbException('Connection name is empty');
        }

        if (empty($dbDriverName)) {
            $dbDriverName = $this->getDefaultDriver();
        }

        $method = strtolower($dbDriverName) . 'Driver';
        if (!method_exists($this, $method)) {
            throw new KernelException('Method is not defined : ' . $method);
        }

        if (isset($this->pool[$connectName])) {
            $this->disconnect($connectName);
        }

        $this->pool[$connectName] = $this->$method(
            $params['adapter'],
            $params['host'],
            $params['user'],
            $params['password'],
            $params['database']
        );
    }
    
    /**
    * Remove connection from Pool
    *
    * @param string $connectName
    */
    public function disconnect($connectName)
    {
        unset($this->pool[$connectName]);
    }
    
    /**
    * Retrieve connection from Pool
    *
    * @param string $connectName
    * @return object(driver)
    */
    public function getConnection($connectName)
    {
        if (empty($this->pool[$connectName])) {
            throw new DbException(
                'Connection "' . $connectName 
                . '" is not created. Use "connect" method to create connection'
            );
        }

        return $this->pool[$connectName];
    }
    
    /**
    * Trace Pool for debug purpose
    */
    public function tracePool()
    {
        \Debug::trace($this->pool);
    }

    /**
    * Get DB driver
    *
    * @return string
    */
    public function getDefaultDriver()
    {
        return 'pdo';
    }

    /**
    * PDO driver to connect to DB
    *
    * @param string $adapter mysql,sqlite, postgreSql ...
    * @param string $host usually locallhost
    * @param string $user user name
    * @param string $password password
    * @param string $database DB name
    */
    protected function pdoDriver($adapter, $host, $user, $password, $database)
    {
        $dbh = $adapter == 'sqlight' 
            ? new \PDO('sqlite:' . STORAGE_PATH . '/data/' . $database . '.db')
            : new \PDO($adapter . ':dbname=' . $database . ';host=' . $host, $user, $password);
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $query = "SET NAMES UTF8";
        $dbh->exec($query);
        
        return new PdoDriver($dbh);
    }
}
