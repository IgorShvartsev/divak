<?php
namespace Helper;

/**
 * Zip class
 * 
 */ 
class Zip extends \ZipArchive 
{ 
    /**
     * Add content in directory recursively 
     * 
     * @param string $path path to directory
     * 
     * @return boolean
     */
    public function addDir($path) 
    {  
        $path = rtrim($path, '/');
        $path  = realpath($path);

        if (!is_dir($path)) {
            return false;
        }

        if (!$this->addEmptyDir($path)) {
            return false;
        } 

        $nodes = glob($path . '/*'); 

        foreach ($nodes as $node) { 
            if (is_dir($node)) { 
                $this->addDir($node); 
            } elseif (is_file($node))  {
                $this->addFile($node); 
            } 
        }

        return true; 
    } 
} 
