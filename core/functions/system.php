<?php

if (!function_exists('filter_input_custom')) {
    /**
     * Custom Filter input (instead of PHP function filter_input)
     *
     * @param string $type
     * @param string $variableName 
     * @param string $filter
     * @param array $options
     *
     * @return mixed
     */
    function filter_input_custom(
        $type, 
        $variableName, 
        $filter = FILTER_DEFAULT, 
        $options = null
    ) {
        switch ($type) {
            case INPUT_SERVER:
                if (isset($_SERVER[$variableName])) {
                    return filter_var($_SERVER[$variableName], $filter, $options);
                } else {
                    return '';
                }
            case INPUT_ENV:
                if (isset($_ENV[$variableName])) {
                    return filter_var($_ENV[$variableName], $filter, $options);
                } else {
                    return '';
                }
            default:
                return filter_input($type, $variableName, $filter = FILTER_DEFAULT, $options);
        }
    }
}

if (!function_exists('isEnabledHttps')) {    
    /**
     * Check if current domain has HTTPS (SSL) enabled
     *
     * @return boolean
     */
    function isEnabledHttps() 
    {
        $hasHttps = false;
        $httpsPort = 443;
        $serverPort = (int)filter_input_custom(INPUT_SERVER, 'SERVER_PORT');
        $httpsHeader = filter_input_custom(INPUT_SERVER, 'HTTPS');
        $protoHeader = filter_input_custom(INPUT_SERVER, 'HTTP_X_FORWARDED_PROTO');

        if (
            (!empty($httpsHeader) && 'off' !== $httpsHeader)
            || $httpsPort === $serverPort
            || 'https' === $protoHeader
        ) {
            $hasHttps = true;
        }

        return $hasHttps;
    }
}

if (!function_exists('url')) {
    /**
     * Make url
     *
     * @param string $url relative or full 
     * 
     * @return string
     */
    function url($url = '') 
    {
        $baseUrl = \Config::get('app.base_url');

        if (
            false !== strpos($url, 'http://') 
            || false !== strpos($url, 'https://') 
            || false !== strpos($url, 'mailto:')
        ) {
        } elseif (empty($url)) {
            $url = rtrim($baseUrl, '/') . '/';
        } else {
            $url = rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
        }
            
        return $url;
    }
} 

if (!function_exists('output')) {
    /**
     * Output text
     * 
     * @param string $text
     */
    function output($text, $lineBreak = "\n")
    {
        file_put_contents('php://output', $text . $lineBreak);
        flush();
    }
}
