<?php
namespace Helper;

/**
 * Benchmark class
 *
 */    
class Benchmark
{
    /**
     * @var array
     */
    protected static $timePointers = [];
    
    /**
     * @var string
     */
    protected static $defaultName = 'main';
    
    /**
     * Mark
     *
     * @param string $name
     * @return int
     */
    public static function mark($name = null)
    {
        if (empty($name)) {
            $name = static::$defaultName;
        }
        
        if (!isset(static::$timePointers[$name])) {
            static::reset($name);    
        }
        
        $currentTime =  microtime(true);
        $timeElapsed = $currentTime - static::$timePointers[$name];
        static::$timePointers[$name] = $currentTime;
    
        return $timeElapsed;
    }
    
    /**
     * Reset
     *
     * @param string $name of mark
     */
    public static function reset($name = null)
    {
        if (empty($name)) {
            $name = static::$defaultName;
        }

        static::$timePointers[$name] = 0;
    }
}
