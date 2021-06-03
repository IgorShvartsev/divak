<?php
namespace Kernel\Container;

/**
 * Container Interface
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
interface ContainerInterface
{
    const BIND_SHARE = 0;
    const BIND_FACTORY = 1;

    /**
    * Binds class name to it's implementation
    *
    * @param string $className
    * @param string $classImplementation
    * @param int $type - 0: singleton instance from container,
    *                    1: multiple instances from container
    */
    public function bind($className, $classImplementation, $type = 0);

    /**
    * Binds object instance
    *
    * @param string $className
    * @param object $instance
    */
    public function bindInstance($className, $instance);

    /**
    * Binds varibale
    *
    * @param string $varName
    * @param mixed $value
    */
    public function bindVariable($varName, $value);

    /**
    * Get instance from container.
    * Use "bind" first to save class implementation into container
    *
    * @param string $className
    * 
    * @return instance
    */
    public function make($className);

    /**
    * Check if is set value|instance|callable for the given key
    *
    * @param string $key
    * 
    * @return boolean
    */
    public function isValid($key);

    /**
    * Allows extend  already saved in container binding
    *
    * @param string $className
    * @param Closure $callback
    */
    public function extend($className, \Closure $callback);
}
