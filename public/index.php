<?php
define('APP_PATH', dirname(__FILE__) . '/../app');
define('STORAGE_PATH', dirname(__FILE__) . '/../storage');

require_once(dirname(__FILE__) . '/../vendor/autoload.php');

$dirs = [
	dirname(__FILE__) . '/../core',
	APP_PATH,
	APP_PATH . '/controllers',
	APP_PATH . '/core',
	APP_PATH . '/models',
	APP_PATH . '/library',
	APP_PATH . '/helpers'
];

\Loader::addDirectories($dirs);

\Loader::register();

require_once(APP_PATH . '/bootstrap.php');
