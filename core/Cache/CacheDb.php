<?php

namespace Cache;

use \Cache\Exception\CacheDbException;

/**
*  Cache DB class
*
*  @author Igor Shvartsev (igor.shvartsev@gmail.com)
*  @package Divak
*  @version 1.0
*/
class CacheDb extends CacheAbstract
{
    protected $dbConnection;
    
    /**
    * Options
    * 
    * @var mixed
    */
    protected $_options = array(
        'control_type'  => 'crc32',
        'table'         => 'cache',
        'type'          => 'profile'     
    );
    
    /**
    * Constructor
    * 
    * @param array $_options
    */
    public function __construct($_options = array(), $logging = false)
    {
        parent::__construct($_options);
        $this->dbConnection = isset($_options['dbConnection']) ? $_options['dbConnection'] : true;
        $this->directives['lifetime'] = isset($_options['lifetime']) ? $_options['lifetime'] : 3600;
        $this->directives['logging']  = $logging;
        
        foreach($_options as $i => $v) {
            if (isset($this->options[$i])) {
                $this->options[$i] = $v;
            }
        }
        
    }
    
    public function load($id, $doNotTestCacheValidity = false)
    {
        if (!$this->test($id)) {
            // The cache is not hit !
            return false;
        }
        $id = $this->_hash($id, $this->_options['control_type']);
        $r  = $this->_getKey($id);
        return $r->value;
    }
    
    public function test($id)
    {
        $id = $this->_hash($id, $this->_options['control_type']);
        $r  = $this->_getKey($id);

        if ($r) {
            return (time() - $r->datetime) <= $this->directives['lifetime']; 
        }
        return false;
    }
    
    public function save($data, $id)
    {
        if (!$this->dbConnection) return false;
        
        $id = $this->_hash($id, $this->_options['control_type']);
        $r  = $this->_getKey($id);
        $time = $this->_expireTime($this->directives['lifetime']);
        if ($r) {
            $q = new query("UPDATE {$this->_options['table']} SET `value` = ??, datetime = ?? WHERE `key` = ??");
            $q->execute($data, $time, $id);
        } else {
            $q = new query("INSERT INTO {$this->_options['table']} SET `value` = ??, datetime = ??, type = ??, `key` = ??");
            $q->execute($data, $time, $this->_options['type'], $id );
        }
    }
    
    public function remove($id)
    {
        if (!$this->dbConnection) return false;
        
        $id = $this->_hash($id, $this->_options['control_type']);
        
        $q = new query("DELETE FROM {$this->_options['table']} WHERE `key` = ??");
        $q->execute($id);
        return true;
    }
    
    public function clean()
    {
        if (!$this->dbConnection) return false;
        
        $q = new query("DELETE FROM {$this->_options['table']} WHERE `type` = ??");
        $q->execute($this->_options['type']);
        return true;
    }
    
    /**
     * Compute & return the expire time
     *
     * @return int expire time (unix timestamp)
     */
    protected function _expireTime($lifetime)
    {
        if ($lifetime === null) {
            return 9999999999;
        }
        return time() + $lifetime;
    }

    /**
     * Make a control key with the string containing datas
     *
     * @param  string $data        Data
     * @param  string $controlType Type of control 'md5', 'crc32' or 'strlen'
     * @throws CacheDbException
     * @return string Control key
     */
    protected function _hash($data, $controlType)
    {
        switch ($controlType) {
            case 'md5':
                return md5($data);
            case 'crc32':
                return crc32($data);
            case 'strlen':
                return strlen($data);
            case 'adler32':
                return hash('adler32', $data);
            default:
                throw new CacheDbException("Incorrect hash function : $controlType");
        }
    }
    
    /**
    * Get DB key
    * 
    * @param mixed $key
    */
    protected function _getKey($key)
    {
        if (!$this->dbConnection) return false;
        
        $query = new query("SELECT * FROM {$this->_options['table']} WHERE `key` = ??");
        $query->execute($key);
        return $query->fetch();
    }
    

}
