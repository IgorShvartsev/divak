<?php

/**
*  Validation class
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Validation
{
    /**
     * Minimum Length.
     *
     * @param string $str
     * @param value $val
     *
     * @return bool
     */
    public static function minLength($str, $val)
    {
        if (preg_match('/[^0-9]/', $val)) {
            return false;
        }
        if (function_exists('mb_strlen')) {
            return mb_strlen($str) > $val;
        }

        return strlen($str) > $val;
    }

    /**
     * Max Length.
     *
     * @param string $str
     * @param value $val
     *
     * @return bool
     */
    public static function maxLength($str, $val)
    {
        if (preg_match('/[^0-9]/', $val)) {
            return false;
        }
        if (function_exists('mb_strlen')) {
            return mb_strlen($str) < $val;
        }

        return strlen($str) < $val;
    }

    /**
     * Exact Length.
     *
     * @param string $str
     * @param value $val
     *
     * @return bool
     */
    public static function exactLength($str, $val)
    {
        if (preg_match('/[^0-9]/', $val)) {
            return false;
        }
        if (function_exists('mb_strlen')) {
            return mb_strlen($str) === $val;
        }

        return strlen($str) === $val;
    }

    /**
     * Valid Email.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function validEmail($str)
    {
        return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str);
    }

    /**
     * Valid Emails.
     *
     * @param string $str - emails separated by comma
     *
     * @return bool
     */
    public static function validEmails($str)
    {
        if (false === strpos($str, ',')) {
            return static::validEmail(trim($str));
        }
        foreach (explode(',', $str) as $email) {
            if ('' !== trim($email) && !static::validEmail(trim($email))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Alpha.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function alpha($str)
    {
        return (bool) preg_match('/^([a-z])+$/i', $str);
    }

    /**
     * Alpha-numeric.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function alphaNumeric($str)
    {
        return (bool) preg_match('/^([a-z0-9])+$/i', $str);
    }

    /**
     * Alpha-numeric with underscores and dashes.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function alphaDash($str)
    {
        return (bool) preg_match("/^([^!@#$%&*()<>{}\[\]|?`~\/№\";:,])+$/iu", $str);
    }

    /**
     * Numeric.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function numeric($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
    }

    /**
     * Is Numeric.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isNumeric($str)
    {
        return is_numeric($str);
    }

    /**
     * Integer.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function integer($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
    }

    /**
     * Is a Natural number  (0,1,2,3, etc.).
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isNatural($str)
    {
        return (bool) preg_match('/^[0-9]+$/', $str);
    }

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.).
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isNaturalNoZero($str)
    {
        if (!preg_match('/^[0-9]+$/', $str)) {
            return false;
        }
        if (0 === $str) {
            return false;
        }

        return true;
    }

    /**
     * Valid Base64.
     *
     * Tests a string for characters outside of the Base64 alphabet
     * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
     *
     * @param string $str
     *
     * @return bool
     */
    public static function validBase64($str)
    {
        return (bool) !preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
    }

    /**
     * Valid words.
     *
     * @param string $str
     * @param string $vMethod
     * @param mixed  $val
     *
     * @return bool
     */
    public static function isWords($str, $vMethod = 'alphaDash', $val = '')
    {
        $ok = true;
        $arr = explode(' ', $str);
        foreach ($arr as $word) {
            if (empty($word)) {
                continue;
            }
            if (!method_exists('Validation', $vMethod)) {
                throw new \Exception('Validation method is undefined. Look Validation.class for correct method');
            }
            if ('minLegth' === $vMethod || 'maxLength' === $vMethod || 'exactLength' === $vMethod) {
                if (!self::$vMethod($word, $val)) {
                    $ok = false;
                }
            } else {
                if (!self::$vMethod($word)) {
                    $ok = false;
                }
            }
        }

        return $ok;
    }
}
