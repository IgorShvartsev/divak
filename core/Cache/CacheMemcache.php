 <?php

namespace Cache;

use \Cache\Exception\CacheMemcacheException;

/**
* Cache Memcache class
*
* @author Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class CacheMemcache extends CacheAbstract
{
    protected $_memcache;

    protected $_connection = true;

    protected $_config = array(
        'host'  => 'localhost',
        'port'  => 11211
    );

    /**
    * Options
    *
    * @var mixed
    */
    protected $_options = array(
        'control_type'  => 'crc32',
    );

    /**
    * Constructor
    *
    * @param array $_options
    */
    public function __construct($_options = array(), $logging = false)
    {
        parent::__construct($_options);
        $this->directives['lifetime'] = isset($_options['lifetime']) ? $_options['lifetime'] : 3600;
        $this->directives['logging']  = $logging;

        foreach($_options as $i => $v) {
            if (isset($this->options[$i])) {
                $this->options[$i] = $v;
            }
        }
        $this->_memcache = new Memcache();

        if (!$this->_memcache->connect($this->_config['host'], $this->_config['port'])) {
            $this->_connection = false;
            //echo 'Error.Memcache server.Connection failed';
        }
    }

    public function load($id, $doNotTestCacheValidity = false)
    {
        if (!$this->test($id)) {
            // The cache is not hit !
            return false;
        }
        $key = $this->_hash($id, $this->_options['control_type']);
        return $this->_getKey($key);
    }

    public function test($id)
    {
        $key= $this->_hash($id, $this->_options['control_type']);
        $val  = $this->_getKey($key);
        if ($val) {
            return true;
        }
        return false;
    }

    public function save($data, $id)
    {
        if (!$this->_connection) return false;

        $key = $this->_hash($id, $this->_options['control_type']);
        $this->_memcache->set($key, $data, MEMCACHE_COMPRESSED, $this->directives['lifetime']);
    }

    public function remove($id)
    {
        if (!$this->_connection) return false;

        $key = $this->_hash($id, $this->_options['control_type']);

        $this->_memcache->delete($key);
        return true;
    }

    public function clean()
    {
        if (!$this->_connection) return false;

        $this->_memcache->flush();
    }

    /**
     * Make a control key with the string containing datas
     *
     * @param  string $data        Data
     * @param  string $controlType Type of control 'md5', 'crc32' or 'strlen'
     * @throws CacheMemcacheException
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
                throw new CacheMemcacheException("Incorrect hash function : $controlType");
        }
    }

    /**
    * Get DB key
    *
    * @param mixed $key
    */
    protected function _getKey($key)
    {
        if (!$this->_connection) return false;

        return $this->_memcache->get($key);
    }
}
