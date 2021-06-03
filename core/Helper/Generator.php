<?php
namespace Helper;

/**
 * Generator class
 * 
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
class Generator 
{
    /**
     * Generate string containing Digits
     *
     * @param int $len
     *
     * @return string
     */
    public static function generateDigits($len = 6)
    {
        $result = '';
        $len = intVal($len);
        
        if (empty($len)) {
            return $result;
        } 

        $buf = [];
        $firstPosition = true;
        for ($i = 0; $i < $len; $i++) {
            if ($firstPosition) {
                $firstPosition = false;
                $buf[] = rand(1, 9);
            } else {
                $buf[] = rand(0, 9);
            }
        }

        $result = implode('', $buf);

        return $result;
    }

    /**
     * Generate string containing letters and digits
     *
     * @param int $len
     *
     * @return string
     */
    public function generateAlphaNumeric($len)
    {
        $result = '';
        $charSetArray = [
            'ABCDEFGJKLMNPQRSTUVWXYZ',
            '0123456789',
        ];
        
        $len = intVal($len);

        if (empty($len)) {
            return $result;
        }

        $buf = [];
        $charSetArrayLength = count($charSetArray);
        for ($i = 0; $i < $len; $i++) {
            $charSetIdx = rand(0, $charSetArrayLength - 1);
            $charSet = $charSetArray[$charSetIdx];
            $charSetLength = strlen($charSet);
            $charIndex = rand(0, $charSetLength - 1);
            $buf[] = $charSet{$charIndex}; 
        }

        $result = implode('', $buf);

        return $result; 
    }
}
