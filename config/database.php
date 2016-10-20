<?php

return [
    /**
    * currently used DB credentials , leave empty to disable using DB connection
    */
    'default' => 'connect_1',

    /**
    * connection credentials , you can add as many as you wish 
    * and name differently instead of example "connect_1", ...
    */ 
    'connect_1' => [
		'adapter'  => 'mysql',  
    	'database' => 'xxxxx',
    	'host'     => 'localhost',
    	'user'     => 'xxxxx',
    	'password' => 'xxxxx',
    ],

    'connect_2' => [
    	'adapter'  => 'mysql',  
    	'database' => '',
    	'host'     => 'localhost',
    	'user'     => '',
    	'password' => '',
    ]
];
