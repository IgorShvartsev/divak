<?php

return [
	'middleware' => [
		'translator' => \Kernel\Http\Middleware\InitTranslator::class,
		'auth'       => \Middleware\Auth::class
	],
	'before' => [
		'auth',
		'translator',
	],
	'after'  => []
];