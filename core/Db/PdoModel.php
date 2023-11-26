<?php

namespace Db;

use \Db\Exception\DbException;

/**
 * PDO Model class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
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
     * @var array
     */
    protected $fields;

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
     * Get entry
     * 
     * @param mixed $id
     * 
     * @return array | null
     */ 
    public function get($id)
    {
        if (empty($id)) {
            return; 
        }

        if (empty($this->table)) {
            throw new DbException('Property "table" is not defined');
        }

        $query = 'SELECT * FROM `'  . $this->table . '` '
            . ' WHERE `id` = ?';
        $entry = $this->query($query)->fetch([$id]);

        return $entry;
    }

    /**
     * Delete entry
     * 
     * @param mixed $id
     * 
     * @return bool
     */ 
    public function delete($id)
    {
        if (empty($id)) {
            return false; 
        }

        if (empty($this->table)) {
            throw new DbException('Property "table" is not defined');
        }

        $query = 'DELETE FROM `' . $this->table . '` WHERE `id` = ?';
        $this->query($query)->execute([$id]); 

        return true;
    }

    /**
     * Make saved data from array 
     * 
     * @param array $data
     * @param array $tableFields valid db table fields to be saved
     * 
     * @param array [id, saveddata]
     */ 
    public function makeSavedData(array $data, array $tableFields = [])
    {
        $id = null;
        $savedData = [];

        if (empty($tableFields) && !empty($this->fields)) {
            $tableFields = $this->fields;
        }

        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }

        foreach ($data as $field => $value) {
            if (in_array($field, $tableFields)) {
                $savedData[$field] = $value;
            }
        }

        return [$id, $savedData];
    }

    /**
     * update
     *
     * @param array $hash array of pairs field=>value
     * @param array $where where clause array joined with AND , key can contain <,>,!=, = at the end of field name
     * 
     * @return bool
     */
    public function update($hash = [], $where = [])
    {
        if (!count($hash) || !count($where)) {
            return false;
        }

        $keys = array_keys($hash);
        
        foreach ($keys as $idx => $key) {
            $keys[$idx] = '`' . $key . '`';
        }
        
        $keys = join(' = ? , ', $keys);
        $keys .= ' = ? ';
        $values = array_values($hash);
        $whereKeys = [];
        
        foreach ($where as $f => $v) {
            if (preg_match('/(?<=[^!<>=])[!<>=]{1,2}\s*$/', $f, $matches)) {
                $operator = $matches[0];
                $f = str_replace($operator, '', $f);
                $f = trim($f);
                $whereKeys[] = '`' . $f . '` ' . $operator . ' ? ';
            } else {
                $whereKeys[] =  '`' . $f . '`' . ' = ? ';
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
     * 
     * @return int
     */
    public function insert($hash = [])
    {
        if (!count($hash)) {
            return false;
        }

        $keys = array_keys($hash);
        
        foreach ($keys as $i => $v) {
            $keys[$i] = '`$v`';
        }
        
        $keys = join(', ',  $keys) ;
        $values = array_values($hash) ;
        $q = $this->generatePlaceHolders(count($hash)) ;
        $sql = "INSERT INTO {$this->table} ($keys) VALUES ($q)";

        return $this->query($sql)->execute($values)->getLastInsertId();
    }
    
  
    /**
     * GeneratePlaceHolders
     *
     * @param int $count
     * 
     * @return string
     */
    public function generatePlaceHolders($count)
    {
        return join(', ', array_fill(0, $count, '?')) ;
    }
}
