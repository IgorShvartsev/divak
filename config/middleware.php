<?php

return [
	/**
	* list of registered middlewares
	*/
	'middleware' => [
		'translator' => \Kernel\Http\Middleware\InitTranslator::class,
		'auth'       => \Middleware\Auth::class
	],

	/**
	* which registered middlewares to use before or after
	*/ 
	'before' => [
		'auth',
		'translator',
	],

	'after'  => []
];
