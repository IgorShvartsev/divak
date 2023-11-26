<?php
namespace Kernel\Container;

use \Kernel\Exception\ContainerException;
use \Kernel\Container\ContainerInterface;
use \Pimple\Container as PimpleContainer;
use \Resolver;

/**
 *  Container driver using Pimple third part
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class PimpleContainerDriver implements ContainerInterface
{
    /**
     * @var PimpleContainer
     */
    protected $container;
    
    /**
     * Constructor
     */
    public function __construct(PimpleContainer $container)
    {
        $this->container = $container;
    }
    
    /**
     * Binds class name to it's implementation
     *
     * @param string $className
     * @param string $classImplementation
     * @param int $type - 0: singleton instance from container,
     *                    1: multiple instances from container
     */
    public function bind($className, $classImplementation, $type = 0)
    {
        if ($type === self::BIND_SHARE) {
            // to have singleton
            $this->container[$className] = function ($c) use ($classImplementation) {
                return method_exists($classImplementation, 'getInstance') 
                    ? $classImplementation::getInstance()
                    : (new Resolver)->resolve($classImplementation);
            };
        } elseif ($type === self::BIND_FACTORY) {
            // to have multiple instances
            $this->container[$className] = $this->container->factory(function ($c) use ($classImplementation) {
                return (new Resolver)->resolve($classImplementation);
            });
        }
    }

    /**
     * Binds object instance
     *
     * @param string $className
     * @param object $instance
     */
    public function bindInstance($className, $instance)
    {
        $this->bindVariable($className, $instance);
    }

    /**
     * Binds varibale
     *
     * @param string $varName
     * @param mixed $value
     */
    public function bindVariable($varName, $value)
    {
        $this->container[$varName] = $value;
    }

    /**
     * Get instance from container.
     * Use "bind" first to save class implementation into container
     *
     * @param string $className
     * 
     * @return object
     * 
     * @throws ContainerException
     */
    public function make($className)
    {
        if (isset($this->container[$className])) {
            return $this->container[$className];
        } else {
            throw new ContainerException('Container doesn\'t contain key "' . $className . '"');
        }
    }

    /**
     * Check if is set value|instance|callable for the given key
     *
     * @param string $key
     * 
     * @return boolean
     */
    public function isValid($key)
    {
        return isset($this->container[$key]);
    }

    /**
     * Allows extend  already saved in container binding
     *
     * @param string $className
     * @param Closure $callback
     */
    public function extend($className, \Closure $callback)
    {
        // TO-DO
    }
}
