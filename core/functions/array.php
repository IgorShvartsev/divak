<?php
if (!function_exists('findEntriesInArray')) {
    /**
     * Find entries by array of $filed => $value
     *
     * @param array $array
     * @param array $byFields
     * @return array
     */
    function findEntriesInArray($array, array $byFields)
    {
        $result  = [];
        $entryList = [];

        if (is_array($array)) {
            $entryList = $array;
        }

        foreach ($byFields as $field => $value) {
            $result = [];
            foreach ($entryList as $entry) {
                if (key_exists($field, $entry) && $entry[$field] === $value) {
                    $result[] = $entry;
                }
            }
            $entryList = $result;
        }

        return $result;
    }
}

if (!function_exists('getArrayKeyValue')) {
    /**
     * Get array key value
     *
     * @param string $name
     * @param array $array
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    function getArrayKeyValue($key, array $array, $defaultValue = null)
    {
        if (array_key_exists($key, $array)) {
            $value = $array[$key];
            $result = is_string($value) ? trim($value) : $value;
        } else {
            $result = $defaultValue;
        }

        return $result;
    }
}
