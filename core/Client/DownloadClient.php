<?php
namespace Client;

/**
 * Stream Download client class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
class StreamDownloadClient
{
    /**
     * @var resource handle;
     */
    protected $handle;


    /**
     * Constructor
     */
    public function __construct()
    {
        // stream initialization
    }

    /**
     * Download file without loading it into memory
     *
     * @param string $file file path or url
     * @param string $fileExtension
     */
    public function downloadFile($file, $fileExtension)
    {
        $this->handle = @fopen($file, 'rb');

        if (!$this->handle) {
            return false;
        }

        if (preg_match('/^http/', $file)) {
            $headers = get_headers($file, 1);
            $headers = array_change_key_case($headers);
            if (!empty($headers['content-length'])) {
                header('Content-Length: ' . $headers['content-length']);
            }
        } else {
            header('Content-Length: ' . filesize($file)); 
        }

        header('Content-Type: ' . \Mime::getType($fileExtension));
        header('Cache-Control: no-cache');
        header('Pragma: public');
        header('Expires: 0');
        header('Access-Control-Allow-Origin: *');
        
        $this->read();

        return true;
    }

    /**
     * Read stream
     *
     */
    protected function read()
    {
        if ($this->handle) {
            fpassthru($this->handle);
            fclose($this->handle);
            exit();
        }
    } 
}
