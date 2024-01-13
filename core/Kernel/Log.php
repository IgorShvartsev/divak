<?php
namespace Kernel;

/**
 *  Log class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class Log
{
    /**
     * logfile - target file to log any messages
     *
     * @var string
     */
    private $logfile = null;

    /**
     * error - show possible error  when writing failed
     *
     * @var mixed
     */
    public $error = '';

    /**
     * lastUpdated - shows if file had been updated  after writing
     *
     * @var mixed
     */
    private $lastUpdated = false;

    /**
     * checkFileTimeDelay  - checks if filetime changed in a defined period (sec)
     *
     * @var numeric - in seconds
     */
    public $checkFileTimeDelay = 180;
    
    /**
     * constructor
     *
     * @param string $logfile - abs path to the log file
     */
    public function __construct($logfile, $delay = 180)
    {
        $this->logfile = $logfile;
        $this->checkFileTimeDelay = $delay;

        if (empty($this->logfile)) {
            $this->error = 'File name is empty';
        }
    }
    
    /**
     * write method
     *
     * @param mixed $msg - message to be loged
     * 
     * @return boolean
     */
    private function write($msg, $type = 'Error')
    {
        if (empty($this->logfile)) {
            return false;
        }

        $this->error = '';

        if (file_exists($this->logfile)) {
            $lastmodified = filemtime($this->logfile);
            $this->lastUpdated   = (time() - $lastmodified) < $this->checkFileTimeDelay;
        }

        if ($f = fopen($this->logfile, 'a+')) {
            fwrite($f, date('d-m-Y H:i') . ' ' . $type . '  ' . $msg . "\n");
            fclose($f);
            
            return $this->lastUpdated ? false : true;
        } else {
            $this->error = 'File ' . $this->logfile . ' can\'t be created or not found';
        }

        return false;
    }
    
    /**
     * Magic call
     * Calls methods: status, notify, system, error
     * 
     * @param string $method
     * @param array $args
     * 
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        switch ($method) {
            case 'info':
                $type = 'Info';
                break; 
            case 'status':
                $type = 'Status';
                break;
            case 'notify':
                $type = 'Notification';
                break;
            case 'system':
                $type = 'System';
                break;
            case 'error':
                $type = 'Error';
                break;
            case 'fatal':
                $type = 'Fatal';
                break;
            
            default:
                throw new \Exception("Undefined method $method");
        }

        if (!count($args)) {
            throw new \Exception("Method $method needs param \$msg");
        }

        $this->write($args[0], $type);
    }
}
