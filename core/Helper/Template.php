<?php
namespace Helper;

/**
 * Template class
 * 
 * @author Igor Shvartsev (igor.shvartsev@gmail.com)
 */
class Template
{
    /** @var string */
    protected $content;
    
    /**
     * Conctructor
     * 
     * @param string $templateFile
     * @throws \RuntimeException
     */
    public function __construct($templateFile)
    {
        if (file_exists($templateFile)) {
            $this->content = $this->getFileContent($templateFile);
        } else {
            throw new \RuntimeException('Template file not found ' . $templateFile);
        }
    }
    
    /**
     * Populate (replace template placeholders) with data in array
     * 
     * @param array $data
     * @return Template instance
     */
    public function populate($data)
    {
        $data = is_array($data) ? $data : (array)$data;

        foreach ($data as $placeholder => $value) {
            $placeholder = str_replace(['{', '}'], ['(', ')'], $placeholder);
            $this->content = preg_replace_callback(
                '/(\{\{\s*' . $placeholder . '\s*\}\})/i', 
                function ($matches) use ($value){
                    return $value;
                }, 
                $this->content
            );
        }

        return $this;
    }
    
    /**
     * Get template content
     * 
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Get file content
     * 
     * @param string $filename - abs path to the file
     * @return string|false
     */
    protected function getFileContent($filename) 
    {
        if (is_file($filename)) {
            ob_start();
            include $filename;
            return ob_get_clean();
        }

        return false;
    }
}
