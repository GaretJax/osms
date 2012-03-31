<?php namespace osmf;

/**
 * This is the bootstrap file for the OSMF framework, this should be
 * the only php file available under your publicly accessible web
 * directory.
 */

// Define needed constants for include paths and files inclusion
define('LIBRARIES_PATH', '/Users/garetjax/Sites/osms-root');
define('APPLICATION_PATH', '/Users/garetjax/Sites/osms-root/osms');
define('SETTINGS_FILE', 'settings.php');

// Set up include paths
set_include_path(LIBRARIES_PATH . PATH_SEPARATOR . get_include_path());
set_include_path(APPLICATION_PATH . PATH_SEPARATOR . get_include_path());

// Include needed components
require_once 'osmf/bootstrap.php';

$config = Config::getInstance();

// Instantiate router and load urls
$router = new Router($config->app_root);
$router->loadRoutes($config->urlconf);

// Dispatch the request to the correct controller and carry out the
// response
$dispatcher = new Dispatcher($router);
try {
	$response = $dispatcher->dispatch();
	$response->carryOut();
} catch (\Exception $e) {
	if ($config->debug) {
		var_dump($e);
	} else {
		// TODO: Log to file
	}
}
