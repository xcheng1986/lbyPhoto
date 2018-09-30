<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'App');
$_SERVER['COMMON_INCLUDE_PHP_FILE'] = dirname(__DIR__).'/common.php';
@file_exists($_SERVER['COMMON_INCLUDE_PHP_FILE']) && include $_SERVER['COMMON_INCLUDE_PHP_FILE'];
require dirname(__DIR__) . '/vendor/autoload.php';
\Core\Core::runCli();
