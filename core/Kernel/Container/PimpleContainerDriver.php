<?php

namespace Kernel\Container;

use \Kernel\Exception\ContainerException;
use \Kernel\Container\ContainerInterface;
use \Pimple\Container As PimpleContainer;
use \Resolver;

/**
*  Container driver using Pimple third part 
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class PimpleContainerDriver implements ContainerInterface
{	
	/**
	* @var PimpleContainer
	*/
	protected $_container;
	
	/**
	* Constructor
	*/
	public function __construct(PimpleContainer $container)
	{
		$this->_container = $container;	
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
		if ($type == self::BIND_SHARE) {
			// to have singleton
			$this->_container[$className] = function($c) use ($classImplementation) {				
				return method_exists($classImplementation, 'getInstance') ?
					  $classImplementation::getInstance()	 
					: (new Resolver)->resolve($classImplementation);
			};
		} else if ($type == self::BIND_FACTORY){
			// to have multiple instances
			$this->_container[$className] = $this->_container->factory(function ($c) use ($classImplementation) {
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
		$this->_container[$varName] = $value;
	}

	/**
	* Get instance from container.
	* Use "bind" first to save class implementation into container
	*
	* @param string $className
	* @return instance
	*/
	public function make($className)
	{
		if (isset($this->_container[$className])) {
			return $this->_container[$className];
		} else {
			throw new ContainerException('Container doesn\'t contain key "' . $className . '"');
		}
	}

	/**
	* Check if is set value|instance|callable for the given key
	*
	* @param string $key
	* @return boolean
	*/
	public function isValid($key) 
	{
		return isset($this->_container[$key]);
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
