<?php

return [
	''                => 'index/index',
	'json'            => 'index/json-test',
	'userdata/(\d+)'  => ['get' => 'index/user/$1'],
	'root'  => [
		'get' => [ 
			'action' => 'admin/index/index', 
			'middleware' => ['before'=>'auth']
		]
	],
];
