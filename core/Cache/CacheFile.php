<?php

namespace Cache;

use \Cache\Exception\CacheFileException;

/**
* File class
*
* @author Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class CacheFile extends CacheAbstract
{
    
    /**
    * Options
    *
    * @var mixed
    */
    protected $_options = array(
        'cache_dir'         => null,
        'file_name_prefix'  => 'cache',
        'control_type'      => 'md5',
        'file_locking'      => true
    );
    
    /**
    * Constructor
    *
    * @param array $_options
    */
    public function __construct($_options = array(), $logging = false)
    {
        parent::__construct($_options);
        $this->_directives['lifetime'] = isset($_options['lifetime']) ? $_options['lifetime'] : 3600;
        $this->_directives['logging']  = $logging;
        if ($this->_options['cache_dir'] !== null) {
            $this->setCacheDir($this->_options['cache_dir']);
        } else {
            throw new CacheFileException('Cache directory not defined');
        }
    }
    
    /**
     * Set the cache_dir (particular case of setOption() method)
     *
     * @param  string  $value
     * @param  boolean $trailingSeparator I$this->controllerPathf true, add a trailing separator is necessary
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setCacheDir($value, $trailingSeparator = true)
    {
        if (!is_dir($value)) {
            throw new CacheFileException('cache_dir must be a directory');
        }
        if (!is_writable($value)) {
            throw new CacheFileException('cache_dir is not writable');
        }
        if ($trailingSeparator) {
            // add a trailing DIRECTORY_SEPARATOR if necessary
            $value = rtrim(realpath($value), '\\/') . DIRECTORY_SEPARATOR;
        }
        $this->_options['cache_dir'] = $value;
    }
    
    public function load($id, $doNotTestCacheValidity = false)
    {
        if (!$this->test($id)) {
            // The cache is not hit !
            return false;
        }
        $id = $this->_hash($id, $this->_options['control_type']);
        $file = $this->_file($id);
        return $this->_fileGetContents($file);
    }
    
    public function test($id)
    {
        $id = $this->_hash($id, $this->_options['control_type']);
        $file = $this->_file($id);
        if (!file_exists($file)) {
            return false;
        }
        clearstatcache();
        $lastModifiedTime = (int)(filemtime($file)/$this->_directives['lifetime']) * $this->_directives['lifetime'];
        return (time() - $lastModifiedTime) <= $this->_directives['lifetime'];
    }
    
    public function save($data, $id)
    {
        $id = $this->_hash($id, $this->_options['control_type']);
        $file = $this->_file($id);
        $this->_filePutContents($file, $data);
    }
    
    public function remove($id)
    {
        $id = $this->_hash($id, $this->_options['control_type']);
        return $this->_remove($id);
    }
    
    public function clean()
    {
        $path = $this->_options['cache_dir'];
        $prefix = $this->_options['file_name_prefix'];
        $glob = @glob($path . $prefix . '_*');
        if ($glob === false) {
            return true;
        }
        foreach ($glob as $file) {
            if (is_file($file)) {
                $fileName = basename($file);
                $id = $this->_fileNameToId($fileName);
                $this->_remove($id);
            }
        }
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
     * @throws CacheFuleException
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
                throw new CacheFileException("Incorrect hash function : $controlType");
        }
    }
    
    /**
     * Transform a cache id into a file name and return it
     *
     * @param  string $id Cache id
     * @return string File name
     */
    protected function _idToFileName($id)
    {
        $prefix = $this->_options['file_name_prefix'];
        $result = $prefix . '_' . $id;
        return $result;
    }
    
    /**
     * Make and return a file name (with path)
     *
     * @param  string $id Cache id
     * @return string File name (with path)
     */
    protected function _file($id)
    {
        $path = $this->_options['cache_dir'];
        $fileName = $this->_idToFileName($id);
        return $path . $fileName;
    }
    
    /**
     * Return the file content of the given file
     *
     * @param  string $file File complete path
     * @return string File content (or false if problem)
     */
    protected function _fileGetContents($file)
    {
        $result = false;
        if (!is_file($file)) {
            return false;
        }
        $f = @fopen($file, 'rb');
        if ($f) {
            if ($this->_options['file_locking']) {
                @flock($f, LOCK_SH);
            }
            $result = stream_get_contents($f);
            if ($this->_options['file_locking']) {
                @flock($f, LOCK_UN);
            }
            @fclose($f);
        }
        return $result;
    }
    
    /**
     * Put the given string into the given file
     *
     * @param  string $file   File complete path
     * @param  string $string String to put in file
     * @return boolean true if no problem
     */
    protected function _filePutContents($file, $string)
    {
        $result = false;
        $f = @fopen($file, 'ab+');
        if ($f) {
            if ($this->_options['file_locking']) {
                @flock($f, LOCK_EX);
            }
            fseek($f, 0);
            ftruncate($f, 0);
            $tmp = @fwrite($f, $string);
            if (!($tmp === false)) {
                $result = true;
            }
            @fclose($f);
        }
        @chmod($file, 0600);
        return $result;
    }
    
     /**
     * Transform a file name into cache id and return it
     *
     * @param  string $fileName File name
     * @return string Cache id
     */
    protected function _fileNameToId($fileName)
    {
        $prefix = $this->_options['file_name_prefix'];
        return preg_replace('~^' . $prefix  . '_(.*)$~', '$1', $fileName);
    }
    
    /**
    * Remove file
    *
    * @param mixed $id
    */
    protected function _remove($id)
    {
        $file = $this->_file($id);
        if (!is_file($file)) {
            return false;
        }
        @unlink($file);
        return true;
    }
}
