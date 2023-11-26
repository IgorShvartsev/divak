<?php
namespace Cache;

/**
 * Cache abstract class.
 *
 * @author Igor Shvartsev (igor.shvartsev@gmail.com)
 *
 * @version 1.2
 */
abstract class CacheAbstract
{
    /**
     * Directives.
     *
     * @var array
     */
    protected $directives = [
        'lifetime' => 3600,
        'logging' => false,
    ];
    
    /**
     * Options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * set options.
     *
     * @param string $name
     * @param mixed  $value
     * @return boolean
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            return  false;
        }

        $name = strtolower($name);

        if (isset($this->options) && array_key_exists($name, $this->options)) {
            $this->options[$name] = $value;
        } else {
            return false;
        }

        return true;
    }

    /**
     * Start cache helper.
     *
     * @param mixed $id
     *
     * @return string|false
     */
    public function start($id)
    {
        if ($out = $this->load($id)) {
            return $out;
        }
        ob_start();

        return false;
    }

    /**
     * End cache helper.
     *
     * @param mixed $id
     *
     * @return string
     */
    public function end()
    {
        $out = ob_get_clean();

        return $out;
    }

    /**
     * Load cached content
     *
     * @param string $id
     * @param boolean $doNotTestCacheValidity
     * @return mixed
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
    }

    /**
     * Test cache on availability ID
     *
     * @param string $id
     * @return boolean
     */
    public function test($id)
    {
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
    }

    /**
     * Remove cached data by ID
     *
     * @param string $id
     * @return boolean
     */
    public function remove($id)
    {
        return false;
    }

    /**
     * Clean cache
     *
     */
    public function clean()
    {
    }
}
