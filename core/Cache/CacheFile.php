<?php
namespace Cache;

use Cache\Exception\CacheFileException;

/**
 * File class.
 *
 * @author Igor Shvartsev (igor.shvartsev@gmail.com)
 *
 * @version 1.1
 */
class CacheFile extends CacheAbstract
{
    /**
     * Options.
     *
     * @var mixed
     */
    protected $options = [
        'cache_dir' => null,
        'file_name_prefix' => 'cache',
        'control_type' => 'md5',
        'file_locking' => true,
    ];

    /**
     * Constructor.
     *
     * @param array $options
     * @param mixed $logging
     * 
     * @throws CacheFileException
     */
    public function __construct($options = [], $isLogging = false)
    {
        parent::__construct($options);
        $this->directives['lifetime'] = isset($options['lifetime']) ? $options['lifetime'] : 3600;
        $this->directives['logging'] = $isLogging;

        if (null !== $this->options['cache_dir']) {
            $this->setCacheDir($this->options['cache_dir']);
        } else {
            throw new CacheFileException('Cache directory not defined');
        }
    }

    /**
     * Set the cache_dir (particular case of setOption() method).
     *
     * @param string $value
     * @param bool   $trailingSeparator I$this->controllerPathf true, add a trailing separator is necessary
     *
     * @throws CacheFileException
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
            $value = rtrim(realpath($value), '\\/').\DIRECTORY_SEPARATOR;
        }

        $this->options['cache_dir'] = $value;
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

        $id = $this->hash($id, $this->options['control_type']);
        $file = $this->file($id);

        return $this->fileGetContents($file);
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
        $id = $this->hash($id, $this->options['control_type']);
        $file = $this->file($id);

        if (!file_exists($file)) {
            return false;
        }

        clearstatcache();
        $lastModifiedTime = (int) (filemtime($file) / $this->directives['lifetime']) * $this->directives['lifetime'];

        return (time() - $lastModifiedTime) <= $this->directives['lifetime'];
    }

    /**
    * Save data into cache by ID
    * 
    * @param mixed $data
    * @param string $id
    */
    public function save($data, $id)
    {
        $id = $this->hash($id, $this->options['control_type']);
        $file = $this->file($id);
        $this->filePutContents($file, $data);
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
        $id = $this->hash($id, $this->options['control_type']);

        return $this->delete($id);
    }

    /**
    * Clean cache
    *
    * @return boolean
    */
    public function clean()
    {
        $path = $this->options['cache_dir'];
        $prefix = $this->options['file_name_prefix'];
        $glob = @glob($path . $prefix . '_*');

        if (false === $glob) {
            return true;
        }

        foreach ($glob as $file) {
            if (is_file($file)) {
                $fileName = basename($file);
                $id = $this->fileNameToId($fileName);
                $this->remove($id);
            }
        }

        return true;
    }

    /**
     * Compute & return the expire time.
     *
     * @param mixed $lifetime
     *
     * @return int expire time (unix timestamp)
     */
    protected function expireTime($lifetime)
    {
        if (null === $lifetime) {
            return 9999999999;
        }

        return time() + $lifetime;
    }

    /**
     * Make a control key with the string containing datas.
     *
     * @param string $data        Data
     * @param string $controlType Type of control 'md5', 'crc32' or 'strlen'
     *
     * @throws CacheFuleException
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
                throw new CacheFileException("Incorrect hash function : $controlType");
        }
    }

    /**
     * Transform a cache id into a file name and return it.
     *
     * @param string $id Cache id
     *
     * @return string File name
     */
    protected function idToFileName($id)
    {
        $prefix = $this->options['file_name_prefix'];
        $result = $prefix . '_' . $id;

        return $result;
    }

    /**
     * Make and return a file name (with path).
     *
     * @param string $id Cache id
     *
     * @return string File name (with path)
     */
    protected function file($id)
    {
        $path = $this->options['cache_dir'];
        $fileName = $this->idToFileName($id);

        return $path . $fileName;
    }

    /**
     * Return the file content of the given file.
     *
     * @param string $file File complete path
     *
     * @return string File content (or false if problem)
     */
    protected function fileGetContents($file)
    {
        $result = false;

        if (!is_file($file)) {
            return false;
        }

        $f = @fopen($file, 'rb');

        if ($f) {
            if ($this->options['file_locking']) {
                @flock($f, LOCK_SH);
            }

            $result = stream_get_contents($f);

            if ($this->options['file_locking']) {
                @flock($f, LOCK_UN);
            }

            @fclose($f);
        }

        return $result;
    }

    /**
     * Put the given string into the given file.
     *
     * @param string $file   File complete path
     * @param string $string String to put in file
     *
     * @return bool true if no problem
     */
    protected function filePutContents($file, $string)
    {
        $result = false;

        $f = @fopen($file, 'ab+');

        if ($f) {
            if ($this->options['file_locking']) {
                @flock($f, LOCK_EX);
            }

            fseek($f, 0);
            ftruncate($f, 0);
            $tmp = @fwrite($f, $string);

            if (!(false === $tmp)) {
                $result = true;
            }

            @fclose($f);
        }

        @chmod($file, 0600);

        return $result;
    }

    /**
     * Transform a file name into cache id and return it.
     *
     * @param string $fileName File name
     *
     * @return string Cache id
     */
    protected function fileNameToId($fileName)
    {
        $prefix = $this->options['file_name_prefix'];

        return preg_replace('~^' . $prefix . '_(.*)$~', '$1', $fileName);
    }

    /**
     * Delete file.
     *
     * @param mixed $id
     * 
     * @return boolean
     */
    protected function delete($id)
    {
        $file = $this->file($id);

        if (!is_file($file)) {
            return false;
        }
        
        @unlink($file);

        return true;
    }
}
