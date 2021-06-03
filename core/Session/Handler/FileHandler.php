<?php
namespace Session\Handler;

/**
 *  Session File handler
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
class FileHandler implements \SessionHandlerInterface
{
    /**
    * Storage path
    * 
    * @var string
    */
    private $savePath;

    /**
    * Constructor
    *
    * @param array $options
    */
    public function __construct($options)
    {
    }

    /**
    * Open session directory
    *
    * @param string $savePath - abs path to session directory
    * @param string $sessionName
    * 
    * @return boolean
    */
    public function open($savePath, $sessionName)
    {
        $this->savePath = $savePath;

        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }

        return true;
    }

    /**
    *  Close session
    */
    public function close()
    {
        return true;
    }

    /**
    * Read session data
    * Must be returned as string. Inner PHP engine that unserialize this string
    *
    * @param string $id
    * 
    * @return string
    */
    public function read($id)
    {
        return (string)@file_get_contents("{$this->savePath}/sess_$id");
    }

    /**
    * Write data  to file
    * Inner PHP engine serialize all session data and passes data as string
    *
    * @param string $id
    * @param string $data
    * 
    * @return boolean
    */
    public function write($id, $data)
    {
        return file_put_contents("{$this->savePath}/sess_$id", $data) === false ? false : true;
    }

    /**
    * Destroy session with given ID
    *
    * @param string $id
    * 
    * @return boolean
    */
    public function destroy($id)
    {
        $file = "{$this->savePath}/sess_$id";

        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    /**
    * Session Garbage cleaner
    *
    * @param int $maxlifetime
    */
    public function gc($maxlifetime)
    {
        foreach (glob("{$this->savePath}/sess_*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        \Log::info('Session GC responded max lifetime' . $maxlifetime);

        return true;
    }
}
