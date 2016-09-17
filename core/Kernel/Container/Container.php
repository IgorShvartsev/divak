<?php

namespace Kernel\Container;

use \Kernel\Exception\KernelException;
use \Kernel\Container\PimpleContainerDriver;

/**
*  Container class 
*  DI container. Base class for Kernel class
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class Container
{
	/** 
	* @var \Kernel\Container\ContainerInterface  
	*/
	protected $_containerDriver;

	/**
	* Init container with given container driver from third part
	*
	* @param string $containerDriveName - must be defined here method as create[NAME]ContainerDriver
	* @return void 
	*/
	public function initContainer($containerDriverName = null)
	{
		if (!$containerDriverName) {
			$containerDriverName = $this->getDefaultContainerDriver();
		}
		$method = 'create' . ucfirst($containerDriverName) . 'ContainerDriver';
		if (!method_exists($this, $method)) {
			throw new KernelException('Method is not defined : ' . $method);
		}
		$this->$method();
	}

	/**
	* Get default container driver
	*
	* @return string
	*/
	public function getDefaultContainerDriver()
	{
		return 'Pimple';
	}

	/**
	*  Debug container
	*/
	public function traceContainer()
	{
		echo '<pre>' . print_r($this->_containerDriver, true) . '</pre>';
	}

	/**
	* Create Pimple container driver
	*
	*/
	protected function createPimpleContainerDriver()
	{
		$this->_containerDriver = (new \Resolver)->resolve(PimpleContainerDriver::class);
	}

	/**
     * Dynamically pass missing methods to the Container Driver instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $callable = [$this->_containerDriver, $method];
        return call_user_func_array($callable, $parameters);
    }
}
