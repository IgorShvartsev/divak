<?php
namespace Session\Handler;

/**
 *  Session File handler
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
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
     * @param string $path - abs path to session directory
     * @param string $name
     * 
     */
    public function open($path, $name): bool
    {
        $this->savePath = $path;

        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }

        return true;
    }

    /**
     *  Close session
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Read session data
     * Must be returned as string. Inner PHP engine that unserialize this string
     *
     * @param string $id
     * @return bool|string 
     */
    #[\ReturnTypeWillChange]
    public function read($id): bool|string
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
     */
    public function write($id, $data): bool
    {
        return file_put_contents("{$this->savePath}/sess_$id", $data) === false ? false : true;
    }

    /**
     * Destroy session with given ID
     *
     * @param string $id
     * 
     */
    public function destroy($id): bool
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
    #[\ReturnTypeWillChange]
    public function gc($maxlifetime): bool|int
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
