<?php
namespace Helper;

/**
 * Message helper class
 * 
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
class Message
{
    /**
     * @var string $template
     */
    protected $template = '';

    /**
     * @var string $placeholder
     */
    protected $placeholder = '';

    /**
     * @var bool $useHtmlEntity
     */
    protected $useHtmlEntity = true;

    /**
     * Constructor
     *
     * @param string $template 
     * @param string $placeholder 
     */
    public function __construct($template = '', $placeholder = '{msg}')
    {
        $this->setTemplate($template, $placeholder);
    } 

    /**
     * Set Template
     *
     * @param string $template HTML or other type of text with 
     *        placeholder to be replaced by text
     * @param string $placeholder special construction, must be included into template also
     */ 
    public function setTemplate($template, $placeholder)
    {
        $this->template = $template;
        $this->placeholder = $placeholder;
    }

    /**
     * Render message
     *
     * @param string $message
     *
     * @param string
     */
    public function render($message)
    {
        if ($this->useHtmlEntity) {
            $message = htmlentities($message);
        }

        if (
            !empty($this->template) 
            && !empty($this->placeholder)
            && strpos($this->template, $this->placeholder) !== false
        ) {
            $message = str_replace($this->placeholder, $message, $this->template);
        }

        return $message;
    }
}
