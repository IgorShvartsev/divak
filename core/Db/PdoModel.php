<?php

namespace Db;

/**
* PDO Model class
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class PdoModel extends \Db\PdoDriver
{
    /**
    * Table name
    *
    * @var string
    */
    public $table;

    /**
    *  Constructor
    *
    * @param  \PDO  - we dont use parameter hint \PDO to avoid error in resolving DI
    */
    public function __construct($pdo = null)
    {
        if (!$pdo) {
            $dbManager = \App::make(\Db\Manager::class);
            $connectionName = \Config::get('database.default');
            $pdo = $dbManager->getConnection($connectionName)->getPdo();
        }
        parent::__construct($pdo);
    }

    /**
    * update
    *
    * @param array $hash - array of pairs field=>value
    * @param array $where - where clause array joined with AND , key can contain <,>,!=, = at the end of field name
    */
    public function update($hash = array(), $where = array())
    {
        if (!count($hash) || !count($where)) {
            return false;
        }
        $keys = array_keys($hash);
        foreach ($keys as $idx=>$key) {
            $keys[$idx] = "`".$key."`";
        }
        $keys = join(' = ? , ', $keys);
        $keys .= ' = ? ';
        $values = array_values($hash);
        $whereKeys = array();
        foreach ($where as $f => $v) {
            if (preg_match('/\s*[<>=]{1,2}\s*$/', $f)) {
                $whereKeys[] = "`" . $f . "`" . ' ? ';
            } else {
                $whereKeys[] =  "`" . $f . "`" . ' = ? ';
            }
        }
        $whereKeys = join(' AND ', $whereKeys);
        $whereValues = array_values($where) ;
        $sql = "UPDATE {$this->table} SET $keys WHERE $whereKeys";
        $this->query($sql)->execute(array_merge($values, $whereValues));
        return true;
    }
    
    /**
    * insert
    *
    * @param mixed $hash - array of pairs field=>value
    * @return int
    */
    public function insert($hash = array())
    {
        if (!count($hash)) {
            return false;
        }
        $keys = array_keys($hash);
        foreach ($keys as $i=>$v) {
            $keys[$i] = "`$v`";
        }
        $keys = join(', ',  $keys) ;
        $values = array_values($hash) ;
        $q = $this->generatePlaceHolders(count($hash)) ;
        $sql = "INSERT INTO {$this->table} ($keys) VALUES ($q)" ;
        return $this->query($sql)->execute($values)->getLastInsertId();
    }
    
  
    /**
    * GeneratePlaceHolders
    *
    * @param int $count
    * @return string
    */
    public function generatePlaceHolders($count)
    {
        return join(', ', array_fill(0, $count, '?')) ;
    }
}
