<?php

namespace Helper;

/**
 * Send Mail class
 * 
 * @author Igor Shvartsev (igor.shvartsev@gmail.com) 
 */
class SendMail
{
    /**
     * @var string
     */
    protected $headers;
    
    /**
     * Constructor
     * 
     * @param string $sendFrom
     */
    public function __construct($sendFrom)
    {
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= "From: $sendFrom\r\n";
        $this->headers = $headers;
    }
    
    /**
     * Send message
     *
     * @param string $to
     * @param string $subject
     * @param string $body 
     * @return bool
     */
    public function send($to, $subject, $body)
    {
        if (is_array($to)) {
            $to = implode(',', $to);
        }
        
        return mail($to, $subject, $body, $this->headers);
    }
    
    /**
     * Send message built on template
     *
     * @param string $to  -  address(es) to
     * @param string $subject - mail subject
     * @param \Helper\Template $template  
     * @param array $data - array of data key => value that replaces placeholders in template like {{key}}    
     * @return bool
     */
    public function sendWithTemplate($to, $subject, \Helper\Template $template, $data)
    {
        if (is_array($to)) {
            $to = implode(',', $to);
        }

        $body = $template->populate($data)->getContent();

        return mail($to, $subject, $body, $this->headers);
    }
}
