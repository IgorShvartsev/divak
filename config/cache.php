<?php

return [
    /**
    * cache type : File, Db, Memcache
    */  
    'type' => 'File',
    
    /**
    * options for the given cache type
    */
    'options' => [
        'lifetime'  => 3600,
        'cache_dir' => STORAGE_PATH . '/cache/'
    ]
];
