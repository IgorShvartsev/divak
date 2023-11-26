<?php
$sapiType = php_sapi_name();
if (substr($sapiType , 0, 3) !== 'cli' && substr($sapiType , 0, 3) !== 'cgi') {
    echo 'script is CLI only.' . PHP_EOL;
    exit;
}

define('CLI_MODE', true);
define('APP_PATH', __DIR__ . '/..');
define('STORAGE_PATH', __DIR__ . '/../../storage');
require_once __DIR__ . '/../../vendor/autoload.php';

$dirs = [
    APP_PATH . '/commands',
];

\Loader::addDirectories($dirs);
\Loader::register();
\Loader::loadFunctions(__DIR__ . '/../../core/functions');

if (count($argv) < 2) {
    echo 'Argument for command is not defined.' . PHP_EOL;
    echo 'Usage: php command.php migrate, where "migrate" is command name due to file path commands/Migrate/MigrateCommand.php'  . PHP_EOL;
    exit;
}

$commandClass = ucfirst($argv[1]) . '\\' . ucfirst($argv[1]) . 'Command';

if (!class_exists($commandClass)) {
    echo 'Class ' . $commandClass . 'doesn\'t exist' . PHP_EOL;
    exit;
}

// Run application
\App::run(function ($app) use ($commandClass) {
    (new \Resolver)->resolve($commandClass)->execute();
});
