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
	protected $config;

	/**
	* Middleware list before application handling
	* @var array
	*/
	protected $before;

	/**
	* Middleware list after application handling
	* @var array
	*/
	protected $after;

	/**
	* Constructor
	*
	* @param array $config
	*/
	public function __construct($config)
	{
		$this->config = $config;
		foreach($this->config['before'] as $mTag) {
			$this->before[] = $this->resolveClassOnTag($mTag);
		}
		foreach($this->config['after'] as $mTag) {
			$this->after[] = $this->resolveClassOnTag($mTag);
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
		if (!count($this->before)) {
			return $response;
		}

		return $this->executeWithPipe($this->before, $request, $response);
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
		if (!count($this->after)) {
			return $response;
		}

		return $this->executeWithPipe($this->after, $request, $response);
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
			$mArr[] = $this->resolveClassOnTag($t);
		}

		return $this->executeWithPipe($mArr, $request, $response);
	}

	/**
	* Run middelwares through the pipe function
	*
	* @param array $mArr - middleware list
	* @param \Kernel\Http\Request $request
	* @param \Kernel\Http\Response $response
	*/
	protected function executeWithPipe($mArr, $request, $response)
	{
		$mArr = array_reverse($mArr);
		$f = $this->makeInitialHandleFunction($response);
		$pipe = $this->makePipeFunction($mArr, $f);

		return $pipe($request);
	}

    /**
    * Make initial function for the pipe
	* 
	* @param \Kernel\Http\Response
	* @return \Kernel\Http\Response
    */
	protected function makeInitialHandleFunction($response)
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
	protected function makePipeFunction($mArr, callable $initialFunction)
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
	protected function resolveClassOnTag($middlewareTag) 
	{
		if (isset($this->config['middleware'][$middlewareTag])) {
			$mObject = new $this->config['middleware'][$middlewareTag];
			if (!method_exists($mObject, 'handle')) {
				throw new KernelException(
					'Not defined method "handle" in middleware class ' 
					. $this->config['middleware'][$middlewareTag]
				);
			}
			return $mObject;
		} else {
			throw new KernelException("Not found middleware tag $middlewareTag in config middleware");
		}
	}
}
