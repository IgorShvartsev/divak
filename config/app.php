<?php

return [
    /**
    * Base url (relative to the site root, f.e. http://example.com/tesing
    * here base_url is /testing)
    */
    'base_url' => '',

    /**
    * Site title
    */
    'title' => 'My Site',


    /**
    * Default layout 
    * This is a file name without extension .tpl
    * (file should be created in app/view folder )
    */
    'default_layout' => 'layout',

    /**
    * Language
    */
    'default_language' => 'en',
    
    /**
    * Available languages ('en', 'nl' ...)
    */
    'all_langs' => [
        'en', 'nl'
    ],

    /**
    * Show errors
    */
    'show_errors' => true,

    /**
    * Timezone
    */
    'timezone' => 'Europe/Amsterdam', 

    /**
    * Cookie params
    */
    'cookie'=> [
        'path'   => "/",
    ] 
];
