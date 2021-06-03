<?php

return [
    '' => 'index/index',

    'json'  => [ 
        'get' => [
            'action' =>'index/json-test',
            'cache'  => ['enable' => true, 'lifetime' => 60]
        ]
    ],

    'userdata/(\d+)' => ['get' => 'index/user/$1'],

    'root' => [
        'get' => [ 
            'action'     => 'admin/index/index', 
            'middleware' => ['before'=>'auth']
        ]
    ],
];
