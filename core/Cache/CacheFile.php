<?php

namespace Cache;

use \Cache\Exception\CacheFileException;

/**
*  File class
* 
*  @author Igor Shvartsev (igor.shvartsev@gmail.com)
*  @package Divak
*  @version 1.0
*/
class CacheFile extends CacheAbstract
{
    protected $cachename;
    
    /**
    * Options
    * 
    * @var mixed
    */
    protected $_options = array(
        'cache_dir'         => null,
        'file_name_prefix'  => 'bit',
        'control_type'      => 'md5',
        'file_locking'      => true
    );
    
    /**
    * Constructor
    * 
    * @param array $_options
    */
    public function __construct($cachename, $_options = array(), $lifetime = 3600, $logging = false)
    {
        parent::__construct($_options);
        $this->cachename = $cachename;
        $this->directives['lifetime'] = $lifetime;
        $this->directives['logging']  = $logging;
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
     * @param  boolean $trailingSeparator If true, add a trailing separator is necessary
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
    
    public function load($id)
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
        $semaphoreTime = $this->_getLastModifiedTimeSemaphore();
        $file = $this->_file($id);
        if ( !file_exists($file) ) return false;
        return (time() - $semaphoreTime) <= $this->directives['lifetime']; 
    }
    
    public function save($data, $id)
    {
        $id = $this->_hash($id, $this->_options['control_type']);
        $file = $this->_file($id);
        $this->_filePutContents($file, $data);
        $this->_updateSemaphore();
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
        $cachename = $this->cachename;
        $glob = @glob($path . $prefix . '_' . $cachename . '_*');
        if ($glob === false) {
            return true;
        }
        foreach ($glob as $file)  {
            if (is_file($file)) {
                $fileName = basename($file);
                $id = $this->_fileNameToId($fileName);
                $this->_remove($id);
            }
        }
        @unlink($path . $prefix . '_' . $cachename );
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
        $cachename = $this->cachename;
        $result = $prefix . '_' . $cachename . '_' . $id;
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
            if ($this->_options['file_locking']) @flock($f, LOCK_SH);
            $result = stream_get_contents($f);
            if ($this->_options['file_locking']) @flock($f, LOCK_UN);
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
            if ($this->_options['file_locking']) @flock($f, LOCK_EX);
            fseek($f, 0);
            ftruncate($f, 0);
            $tmp = @fwrite($f, $string);
            if (!($tmp === FALSE)) {
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
        $cachename = $this->cachename;
        return preg_replace('~^' . $prefix  . '_' . $cachename . '_(.*)$~', '$1', $fileName);
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
    
    /**
    *  Updates semaphor file
    */
    protected function _updateSemaphore()
    {
        $path = $this->_options['cache_dir'];
        $prefix = $this->_options['file_name_prefix'];
        $cachename = $this->cachename;
        $semaphore = $path . $prefix . '_' . $cachename;
        $this->_filePutContents($semaphore, '1');
    }
    
    /**
    *  Get last modified time of semaphor file
    * 
    *  @return numeric modified timestamp
    */
    protected function _getLastModifiedTimeSemaphore()
    {
        $path = $this->_options['cache_dir'];
        $prefix = $this->_options['file_name_prefix'];
        $cachename = $this->cachename;
        $semaphore = $path . $prefix . '_' . $cachename;
        if (!file_exists($semaphore)) {
            $this->_updateSemaphore();
        }
        clearstatcache();
        return (int)(filemtime($semaphore)/$this->directives['lifetime']) * $this->directives['lifetime']; 
    }
}
