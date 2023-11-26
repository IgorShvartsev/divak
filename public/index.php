<?php
define('APP_PATH', __DIR__ . '/../app');
define('STORAGE_PATH', __DIR__ . '/../storage');

require_once __DIR__ . '/../vendor/autoload.php';

$dirs = [APP_PATH];

\Loader::addDirectories($dirs);
\Loader::register();
\Loader::loadFunctions(__DIR__ . '/../core/functions');

require_once APP_PATH . '/bootstrap.php';
