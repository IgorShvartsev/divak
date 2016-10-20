<?php

namespace Db;

/**
* Pdo adapter class
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class PdoDriver extends \Db\DbAbstract
{
    
    /**
    * PDO Statement
    * @var object
    */
    protected $_sth;
    
    
    /**
    * Constructor
    */
    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
    }
    
    /**
    *  Get PDO instance
    *
    * @return object
    */
    public function getPdo()
    {
        return $this->_dbh;
    }
    
    /**
    * query
    *
    * @param string $query
    */
    public function query($query)
    {
        $this->_sth = $this->_dbh->prepare($query);
        $this->_lastquery = $query;
        return $this;
    }
    
    /**
    * fetch
    *
    * @param mixed $params
    * @param numeric $mode - returned values 0 - assoc array, 1 - object
    * @return object|array
    */
    public function fetch($params = array(), $mode = 0)
    {
        $mode = $mode ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC;
        if (!($this->_sth instanceof \PDOStatement)) {
            trigger_error("Query should be created before calling fetch method");
            return false;
        }
        $this->_sth->setFetchMode(\PDO::FETCH_ASSOC);
        $this->_sth->execute($params);
        $data = $this->_sth->fetch($mode);
        $this->_sth->closeCursor();
        return $data;
    }
    
    /**
    * fetchAll
    *
    * @param mixed $params
    * @param numeric $mode - returned values 0 - assoc array, 1 - object
    * @return object|array
    */
    public function fetchAll($params = array(), $mode = 0)
    {
        $mode = $mode ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC;
        if (!($this->_sth instanceof \PDOStatement)) {
            trigger_error("Query should be created before calling fetchAll method");
            return false;
        }
        $this->_sth->execute($params);
        $data = $this->_sth->fetchAll($mode);
        $this->_sth->closeCursor();
        return $data;
    }
    
    /**
    * execute
    * @param array $params
    */
    public function execute($params = array())
    {
        if (!($this->_sth instanceof \PDOStatement)) {
            trigger_error("Query should be created before calling execute method");
            return false;
        }

        $this->_sth->execute($params);
        return $this;
    }
    
    /**
    * Get prepared tatement object
    *
    * @return  PDOStatement
    */
    public function getStatementObject()
    {
        if (!($this->_sth instanceof \PDOStatement)) {
            trigger_error("Query should be created before calling getStatementObject");
            return false;
        }
        return $this->_sth;
    }
    
    /**
    * Set prepared statement object
    *
    * @param PDOStatement $sth
    */
    public function setStatementObject(\PDOStatement $sth)
    {
        $this->_sth = $sth;
        return $this;
    }
    
    /**
    * Quotes target value
    *
    * @param string|int $value
    * @return string|int
    */
    public function quote($value)
    {
        return is_int($value) ? $value : $this->_dbh->quote($value);
    }
    
    /**
    * getLastInsertId
    *
    * @return last insertId
    */
    public function getLastInsertId()
    {
        return $this->_dbh->lastInsertId();
    }
    
    /**
    * getLastQuery
    * @return last query string
    */
    public function getLastQuery()
    {
        return $this->_lastquery;
    }
}
