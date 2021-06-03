<?php

if (!function_exists('benchmark')) {
    /**
     * Benchmark
     *
     * @return int elapsed time
     */
    function benchmark()
    {
        static $timePointer = 0;
        $currentTime =  microtime(true);
        $timeElapsed = $currentTime - $timePointer;
        $timePointer = $currentTime;
    
        return $timeElapsed;
    }
}

if (!function_exists('printbr')) {
    /**
     * Print content of the variable on the web page
     *
     * @param mixed $var
     * @param boolean $isFinal
     */
    function printbr($var, $isFinal = false) 
    {
        if (is_array($var) || is_object($var)) {
            echo '<pre>' . print_r($var, true) . '</pre>';
        } elseif (is_bool($var)) {
            echo ($var ? 'true' : 'false') . '<br>';
        } else {
            echo $var . '<br>';
        }

        if ($isFinal) {
            die('--- END ---');
        }
    }
}