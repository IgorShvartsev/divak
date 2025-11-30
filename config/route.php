<?php

return [
    '' => 'index/index',

    'sendmail' => 'index/sendMail',

    'json'  => [ 
        'get' => [
            'action' =>'index/json-test',
            'cache'  => ['enable' => true, 'lifetime' => 60]
        ]
    ],

    'api' => 'index/api-test',

    'api/(\d+)'  => [ 
        'get' => [
            'action' =>'index/api-test/$1',
        ]
    ],

    'user/(\d+)' => ['get' => 'index/user/$1'],

    'root' => [
        'get' => [ 
            'action'     => 'admin/index/index', 
            'middleware' => ['before' => 'auth']
        ]
    ],

    'chat' => [
        'get' => [ 
            'action'     => '[chat]/index', 
            'middleware' => ['before' => 'auth']
        ]
    ],
];
