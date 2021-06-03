<?php
define('APP_PATH', __DIR__ . '/../app');
define('STORAGE_PATH', __DIR__ . '/../storage');

require_once __DIR__ . '/../vendor/autoload.php';

$dirs = [
    APP_PATH,
    APP_PATH . '/controllers',
    APP_PATH . '/models',
    APP_PATH . '/library',
    APP_PATH . '/helpers'
];

\Loader::addDirectories($dirs);
\Loader::register();
\Loader::loadFunctions(__DIR__ . '/../core/functions');

require_once APP_PATH . '/bootstrap.php';
