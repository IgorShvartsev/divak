<?php
namespace Cache;

use Cache\Exception\CacheMemcacheException;

/**
 * Cache Memcache class.
 *
 * @author Igor Shvartsev (igor.shvartsev@gmail.com)
 *
 * @version 1.2
 */
class CacheMemcache extends CacheAbstract
{
    /** @var \Memcache */
    protected $memcache;

    /** @var boolean */
    protected $isConnection = true;

    /** @var array */
    protected $config = [
        'host' => 'localhost',
        'port' => 11211,
    ];

    /** @var array $options */
    protected $options = [
        'control_type' => 'crc32',
    ];

    /**
     * Constructor.
     *
     * @param array $options
     * @param mixed $isLogging
     */
    public function __construct($options = [], $isLogging = false)
    {
        parent::__construct($options);
        $this->directives['lifetime'] = isset($options['lifetime']) ? $options['lifetime'] : 3600;
        $this->directives['logging'] = $isLogging;

        foreach ($options as $i => $v) {
            if (isset($this->options[$i])) {
                $this->options[$i] = $v;
            }
        }

        $this->memcache = new \Memcache();

        if (!$this->memcache->connect($this->config['host'], $this->config['port'])) {
            $this->isConnection = false;
        }
    }

    /**
     * Load cached content
     *
     * @param string $id
     * @param boolean $doNotTestCacheValidity
     * 
     * @return mixed
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        if (!$this->test($id)) {
            // The cache is not hit !
            return false;
        }

        $key = $this->hash($id, $this->options['control_type']);

        return $this->getKey($key);
    }

    /**
     * Test cache on availability ID
     *
     * @param string $id
     * 
     * @return boolean
     */
    public function test($id)
    {
        $key = $this->hash($id, $this->options['control_type']);
        $val = $this->getKey($key);

        if ($val) {
            return true;
        }

        return false;
    }

    /**
     * Save data into cache by ID
     * 
     * @param mixed $data
     * @param string $id
     */
    public function save($data, $id)
    {
        if (!$this->isConnection) {
            return false;
        }

        $key = $this->hash($id, $this->options['control_type']);
        $this->memcache->set($key, $data, MEMCACHE_COMPRESSED, $this->directives['lifetime']);
    }

    /**
     * Remove cached data by ID
     *
     * @param string $id
     * 
     * @return boolean
     */
    public function remove($id)
    {
        if (!$this->isConnection) {
            return false;
        }

        $key = $this->hash($id, $this->options['control_type']);

        $this->memcache->delete($key);

        return true;
    }

    /**
     * Clean cache
     *
     */
    public function clean()
    {
        if (!$this->isConnection) {
            return false;
        }
        
        $this->memcache->flush();
    }

    /**
     * Make a control key with the string containing datas.
     *
     * @param string $data        Data
     * @param string $controlType Type of control 'md5', 'crc32' or 'strlen'
     *
     * @throws CacheMemcacheException
     *
     * @return string Control key
     */
    protected function hash($data, $controlType)
    {
        switch ($controlType) {
            case 'md5':
                return md5($data);
            case 'crc32':
                return crc32($data);
            case 'strlen':
                return \strlen($data);
            case 'adler32':
                return hash('adler32', $data);
            default:
                throw new CacheMemcacheException("Incorrect hash function : $controlType");
        }
    }

    /**
     * Get DB key.
     *
     * @param mixed $key
     */
    protected function getKey($key)
    {
        if (!$this->isConnection) {
            return false;
        }

        return $this->memcache->get($key);
    }
}
