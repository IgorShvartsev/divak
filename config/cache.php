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
        'cache_dir' => defined('STORAGE_PATH') ?  (STORAGE_PATH . '/cache/') : '',
    ]
];
