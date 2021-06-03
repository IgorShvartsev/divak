<?php

namespace Db;

/**
 * Pdo adapter class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
class PdoDriver extends \Db\DbAbstract
{
    
    /**
    * PDO Statement
    * 
    * @var object
    */
    protected $sth;
    
    
    /**
    * Constructor
    * 
    * @param PDO $pdo
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
        return $this->dbh;
    }
    
    /**
    * query
    *
    * @param string $query
    * 
    * @return PdoDriver
    */
    public function query($query)
    {
        $this->sth = $this->dbh->prepare($query);
        $this->lastquery = $query;

        return $this;
    }
    
    /**
    * fetch
    *
    * @param mixed $params
    * @param numeric $mode returned values 0 - assoc array, 1 - object
    * 
    * @return object|array
    */
    public function fetch($params = [], $mode = 0)
    {
        $mode = $mode ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC;

        if (!($this->sth instanceof \PDOStatement)) {
            trigger_error("Query should be created before calling fetch method");
            return false;
        }

        $this->sth->setFetchMode(\PDO::FETCH_ASSOC);
        $this->sth->execute($params);
        $data = $this->sth->fetch($mode);
        $this->sth->closeCursor();

        return $data;
    }
    
    /**
    * fetchAll
    *
    * @param mixed $params
    * @param numeric $mode returned values 0 - assoc array, 1 - object
    * 
    * @return object|array
    */
    public function fetchAll($params = [], $mode = 0)
    {
        $mode = $mode ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC;

        if (!($this->sth instanceof \PDOStatement)) {
            trigger_error("Query should be created before calling fetchAll method");
            return false;
        }

        $this->sth->execute($params);
        $data = $this->sth->fetchAll($mode);
        $this->sth->closeCursor();

        return $data;
    }
    
    /**
    * execute
    * 
    * @param array $params
    * 
    * @return PdoDriver | false
    */
    public function execute($params = [])
    {
        if (!($this->sth instanceof \PDOStatement)) {
            trigger_error("Query should be created before calling execute method");
            return false;
        }

        $this->sth->execute($params);

        return $this;
    }
    
    /**
    * Get prepared tatement object
    *
    * @return  PDOStatement | false
    */
    public function getStatementObject()
    {
        if (!($this->sth instanceof \PDOStatement)) {
            trigger_error("Query should be created before calling getStatementObject");
            return false;
        }

        return $this->sth;
    }
    
    /**
    * Set prepared statement object
    *
    * @param PDOStatement $sth
    * 
    * @return PdoDriver
    */
    public function setStatementObject(\PDOStatement $sth)
    {
        $this->sth = $sth;

        return $this;
    }
    
    /**
    * Quotes target value
    *
    * @param string|int $value
    * 
    * @return string|int
    */
    public function quote($value)
    {
        return is_int($value) ? $value : $this->dbh->quote($value);
    }
    
    /**
    * getLastInsertId
    *
    * @return last insertId
    */
    public function getLastInsertId()
    {
        return $this->dbh->lastInsertId();
    }
    
    /**
    * getLastQuery
    * 
    * @return last query string
    */
    public function getLastQuery()
    {
        return $this->lastquery;
    }
}
