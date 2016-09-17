<?php

namespace Kernel\Http;

use \Kernel\Exception\KernelException;

/**
*  Middleware Manager Class
*
* @author  Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class MiddlewareManager
{
	/**
	* @var array;
	*/
	protected $_config;

	/**
	* Middleware list before application handling
	* @var array
	*/
	protected $_before;

	/**
	* Middleware list after application handling
	* @var array
	*/
	protected $_after;

	/**
	* Constructor
	*
	* @param array $config
	*/
	public function __construct($config)
	{
		$this->_config = $config;
		foreach($this->_config['before'] as $mTag) {
			$this->_before[] = $this->_resolveClassOnTag($mTag);
		}
		foreach($this->_config['after'] as $mTag) {
			$this->_after[] = $this->_resolveClassOnTag($mTag);
		}
	}

	/**
	*  Handle middlewares from "before" list
	*
	* @param \Kernel\Http\Request $request
	* @param \Kernel\Http\Response $response
	* @return \Kernel\Http\Response
	*/
	public function handleBefore($request, $response)
	{
		if (!count($this->_before)) {
			return $response;
		}
		return $this->_executeWithPipe($this->_before, $request, $response);
	}

	/**
	*  Handle middlewares from "after" list
	*
	* @param \Kernel\Http\Request $request
	* @param \Kernel\Http\Response $response
	* @return \Kernel\Http\Response
	*/
	public function handleAfter($request, $response)
	{
		if (!count($this->_after)) {
			return $response;
		}
		return $this->_executeWithPipe($this->_after, $request, $response);
	}

	/**
	*  Handle middleware named with given tag
	*
	* @param string $mTag
	* @param \Kernel\Http\Request $request
	* @param \Kernel\Http\Response $response
	* @return \Kernel\Http\Response
	*/
	public function handleWithTag($mTag, $request, $response)
	{
		if (!is_array($mTag)) {
			$mTag = array($mTag);
		}
		$mArr = [];
		foreach($mTag as $t) {
			$mArr[] = $this->_resolveClassOnTag($t);
		}
		return $this->_executeWithPipe($mArr, $request, $response);
	}

	/**
	* Run middelwares through the pipe function
	*
	* @param array $mArr - middleware list
	* @param \Kernel\Http\Request $request
	* @param \Kernel\Http\Response $response
	*/
	protected function _executeWithPipe($mArr, $request, $response)
	{
		$mArr = array_reverse($mArr);
		$f = $this->_makeInitialHandleFunction($response);
		$pipe = $this->_makePipeFunction($mArr, $f);
		return $pipe($request);
	}

    /**
    * Make initial function for the pipe
	* 
	* @param \Kernel\Http\Response
	* @return \Kernel\Http\Response
    */
	protected function _makeInitialHandleFunction($response)
	{
		return function($callback) use ($response){
			return $response;
		};
	}

	/**
	* Create pipe function
	*
	* @param array $mArr - middleware list
	* @param callable $initialFunction
	* @return mixed
	*/
	protected function _makePipeFunction($mArr, callable $initialFunction)
	{
		return array_reduce($mArr, function($stack, $m) {
			return function($param) use ($stack, $m){
				return $m->handle($param, $stack);
			};
		}, $initialFunction);
	}

	/**
	* Resolve class on middleware tag name
	*
	* @param string $middlewareTag
	* @return object
	*/
	protected function _resolveClassOnTag($middlewareTag) 
	{
		if (isset($this->_config['middleware'][$middlewareTag])) {
			$mObject = new $this->_config['middleware'][$middlewareTag];
			if (!method_exists($mObject, 'handle')) {
				throw new KernelException('Not defined method "handle" in middleware class ' . $this->_config['middleware'][$middlewareTag]);
			}
			return $mObject;
		} else {
			throw new KernelException("Not found middleware tag $middlewareTag in config middleware");
		}
	}
}
