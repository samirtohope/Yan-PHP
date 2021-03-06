<?php
/**
 * New bootstrap
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

define('ROOT_PATH', rtrim(str_replace('\\', '/', dirname(dirname(__FILE__))), '/'));
define('LIB_PATH', rtrim(str_replace('\\', '/', dirname(ROOT_PATH)), '/') . '/Lib');

require_once LIB_PATH . '/Yan.php';

$request = new Yan_Request_Http();

$path = explode('/', $request->getPathInfo());
if (empty($path)) {
	$appname = $_REQUEST['app'];
} else {
	$appname = array_splice($path, 1, 1);
	$appname = current($appname);
	$request->setPathInfo(implode('/', $path));
}

if (empty($appname)) {
	$appname = 'system';
}

define('APP_NAME', ucfirst(strtolower($appname)));
define('APP_PATH', ROOT_PATH . '/Apps/' . APP_NAME);

$app = new Yan_Application(ROOT_PATH . '/Config/Config.php');
if (is_file(APP_PATH . '/Config/Routes.php')) {
	$router = new Yan_Router();
	$router->addRoute(include APP_PATH . '/Config/Routes.php');
	$app->setRouter($router);
}
$app->run($request);