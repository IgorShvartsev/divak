<?php
namespace Helper;

/**
 * Array helper class
 * 
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
class Arr
{
    /**
     * Retrieves a value from a deeply nested array using "dot" notation
     *
     * @param array $array
     * @param string $dottedKey with "dot" notation
     * @param mixed $default value, which will be returned if the specific key is not found
     *
     * @return mixed
     */
    public static function get(array $array, $dottedKey, $default = null)
    {
        $keys = explode('.', $dottedKey);
        $value = [];
        $isFirst = false;
        
        foreach ($keys as $subKey) {
            if (!$isFirst) {
                if (!isset($array[$subKey])) {
                    return $default;
                }

                $value = $array[$subKey];
                $isFirst = true;
                continue;
            }

            if (!isset($value[$subKey])) {
                return $default;
            }

            $value = $value[$subKey];
        }

        return $value;
    }

    /**
    * Set value into array on key path specified by "dot" notation
    *
    * @param array $array
    * @param string $dottedKey
    * @param mixed $value
    *
    */
    public static function set(array &$array, $dottedKey, $value)
    {
        $keys = explode('.', $dottedKey);

        foreach ($keys as $subKey) {
            if (!isset($array[$subKey])) {
                return;
            } else {
                $array = &$array[$subKey];
            }
        }

        $array = $value;
    }

    /**
    * Find key from multidimensional array
    *
    * @param array $array
    * @param string $field
    * @param mixed $value
    *
    * @return string | null 
    */
    public static function findKey(array $array, $field, $value)
    {
        foreach ($array as $key => $row) {
            if ($row[$field] === $value ) {
                return $key;
            }
        }

        return;
    }

    /**
    * Array to file 
    * 
    * @param array $array
    * @param string $filePath where to save PHP formatted array
    * @param int $level this parameter only for inner recursion, don't use it
    *
    * @return bool if array succesfully saved into file
    */
    public static function arrayToFile(array $array, $filePath, $level = 0)
    {
        $level = (int)$level;
        
        if (!$level) {
            $level = 0;
        }

        $level++;

        $result = '';

        foreach ($array as $key => $value) {
            $key = trim($key);

            if (empty($key) && empty($value)) {
                continue;
            }
            
            if (!is_numeric($key) && !empty($key)) {
                if ($key === '[]') {
                    $key = null;
                } else {
                    $key = "'" . addslashes($key) . "'";
                }
            }
            
            if ($value === null) {
                $value = 'null';
            } elseif ($value === false) {
                $value = 'false';
            } elseif ($value === true) {
                $value = 'true';
            } elseif ($value === '') {
                $value = "''";
            } elseif (!is_array($value) && !is_numeric($value)) {
                $value = "'" . addslashes($value) . "'";
            }
            
            if (is_array($value)) {
                if ($key !== null) {
                    $result .= str_repeat("\t",  $level) . "$key => [\n";
                } else {
                    $result .= str_repeat("\t", $level) . "[\n";          
                }
                $result .= static::arrayToFile($value, null, $level);
                $result .= str_repeat("\t", $level) . "],\n";
            } else {
                if ($key !== null) {
                    $result .= str_repeat("\t", $level) . "$key => $value,\n";
                } else {
                    $result .= str_repeat("\t", $level) . $value . ",\n";
                }
            }
        }

        if (!empty($filePath)) {
            $result = "<?php  \nreturn [\n" . $result . "];";

            return file_put_contents($filePath, $result);
        } else {
            return $result;
        }
    }
}
