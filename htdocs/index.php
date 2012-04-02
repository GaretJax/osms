<?php namespace osmf;
/**
 * This is the bootstrap file for the OSMF framework, this should be
 * the only php file available under your publicly accessible web
 * directory.
 */

/**
 * This is the path to the location where the 'osmf' folder is located.
 *
 * This location will be added to the path in order for the inclusions
 * of the framework related files to work.
 */
define('LIBRARIES_PATH', '/Users/garetjax/Sites/osms-root');

define('APPLICATION_PATH', '/Users/garetjax/Sites/osms-root/osms');

define('SETTINGS_FILE', 'settings.php');

/* --------------------------------------------------------------------- */

// Setup include path
if (defined('LIBRARIES_PATH') && LIBRARIES_PATH !== '') {
	set_include_path(LIBRARIES_PATH . PATH_SEPARATOR . get_include_path());
}

set_include_path(APPLICATION_PATH . PATH_SEPARATOR . get_include_path());

// Bootstrap the framework
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
		// This should not happen, errors should be caught by the
		// dispatcher and dealt accordingly.
		var_dump($e);
	} else {
		throw $e;
	}
}
