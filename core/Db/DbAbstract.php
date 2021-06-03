<?php

namespace Db;

/**
*  Db Abstract class
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.1
*/
abstract class DbAbstract
{
    const SQL_TYPE_INSERT = 0;
    const SQL_TYPE_UPDATE = 1;
   
    /**
    * DB handler
    * 
    * @var object
    */
    protected $dbh;
  
    
    /**
    * Last query string
    * 
    * @var string
    */
    protected $lastquery = '';
    
    /**
    * Constructor
    * 
    */
    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }
    
    
    /**
    * query
    *
    * @param string $query
    */
    abstract public function query($query);
    
    /**
    * fetch
    *
    * @param mixed $params
    * @param numeric $mode returned values 0 - assoc array, 1 - object
    * 
    * @return object|array
    */
    abstract public function fetch($params = [], $mode = 0);
    
    /**
    * fetchAll
    *
    * @param mixed $params
    * @param numeric $mode returned values 0 - assoc array, 1 - object
    * 
    * @return object|array
    */
    abstract public function fetchAll($params = [], $mode = 0);
    
    /**
    * execute
    * 
    * @param array $params
    */
    abstract public function execute($params = []);
    
    /**
    * Quotes target value
    *
    * @param string|int $value
    * 
    * @return string|int
    */
    abstract public function quote($value);
    
    
    /**
    * getLastInsertId
    *
    * @return last insertId
    */
    abstract public function getLastInsertId();
    
    /**
    * getLastQuery
    * 
    * @return last query string
    */
    public function getLastQuery()
    {
        return $this->lastquery;
    }
    
    
    /**
    * Prepares sql for execution using data in array to be inserted or updated
    * Result looks like  INSERT INTO table (column1, column2,...) VALUES (?, ?, ...)
    * or UPDATE table SET column1 = ?, column2 = ?, .....
    *
    * @param string $table table name
    * @param array $data
    * @param int $sqlType 0 - insert sql,  1 - update sql
    * 
    * @return string
    */
    public function prepareSqlFromArray($table, $data, $sqlType = 0)
    {
        $sql = '';
        
        if (!is_array(reset($data))) {
            $data = [$data];
        }
        
        if (count($data) > 0) {
            reset($data);
        }
        // get columns
        $columns = array_map(function ($val) {
            return '`' . $val . '`';
        }, array_keys($data[key($data)]));
        
        // get parameters
        $parameters = array_map(function () {
            return '?';
        }, $columns);
        
        if ($sqlType === self::SQL_TYPE_INSERT) {
            $sql = "INSERT INTO $table (" . implode(',', $columns) . ') VALUES (' . implode(',', $parameters) . ')';
        } elseif ($sqlType === self::SQL_TYPE_UPDATE) {
            $updateData = array_map(function ($column, $param) {
                return $column . '=' . $param;
            }, $columns, $parameters);
            $sql = "UPDATE $table SET " . implode(',', $updateData);
        } else {
            trigger_error("SQL type is not defined");
        }
        
        return  $sql;
    }
}
