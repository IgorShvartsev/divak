<?php
namespace Cache;

/**
* Cache abstract class
*
* @author Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
abstract class CacheAbstract
{
    /**
    * Directives
    *
    * @var array
    */
    protected $_directives = array(
        'lifetime' => 3600,
        'logging'  => false
    );
    /**
    * Options
    *
    * @var array
    */
    protected $_options = array();
    
    /**
    * Constructor
    *
    * @param array $options
    */
    public function __construct($options = array())
    {
        while (list($name, $value) = each($options)) {
            $this->setOption($name, $value);
        }
    }
    /**
    * set options
    *
    * @param string $name
    * @param mixed $value
    */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            return  false;
        }
        $name = strtolower($name);
        
        if (isset($this->_options) && array_key_exists($name, $this->_options)) {
            $this->_options[$name] = $value;
        } else {
            return false;
        }
        return true;
    }

    /**
    * Start cache helper
    *
    * @param mixed $id
    * @return string|false
    */
    public function start($id)
    {
        if ($out = $this->load($id)) {
            return $out;
        } else {
            ob_start();
            return false;
        }
        return false;
    }
    
    /**
    * End cache helper
    *
    * @param mixed $id
    * @return string
    */
    public function end()
    {
        $out = ob_get_clean();
        return $out;
    }
    
    public function load($id, $doNotTestCacheValidity = false)
    {
    }
    
    public function test($id)
    {
    }
    
    public function save($data, $id)
    {
    }
    
    public function remove($id)
    {
    }
    
    public function clean()
    {
    }
}
