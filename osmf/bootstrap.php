<?php namespace osmf;


// Setup autoloaders
function autoload_osmf_library($classname)
{
	$components = explode('\\', $classname);

	if ($components[0] !== 'osmf') {
		// This class is not to be loaded by this autoloader implementation
		return;
	}

	$path = implode(DIRECTORY_SEPARATOR, $components) . '.php';
	$path = stream_resolve_include_path($path);

	if ($path !== FALSE) {
		require_once $path;
	}
}
spl_autoload_register(__NAMESPACE__ . '\autoload_osmf_library');

function autoload_osms_applications($classname)
{
	$components = explode('\\', $classname);

	if ($components[0] !== 'osms') {
		// This class is not to be loaded by this autoloader implementation
		return;
	}

	$components[0] = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'applications';
	$class = array_pop($components);

	$path = implode(DIRECTORY_SEPARATOR, $components) . '.php';
	$path = stream_resolve_include_path($path);

	if ($path !== FALSE) {
		require_once $path;
	}
}
spl_autoload_register(__NAMESPACE__ . '\autoload_osms_applications');


// Load settings
require_once APPLICATION_PATH . DIRECTORY_SEPARATOR . SETTINGS_FILE;


// Setup environment
ini_set('display_errors', intval(Config::get('debug')));


// Load commonly used library contributions
require_once 'osmf/common.php';
require_once 'osmf/components/middlewares.php';
require_once 'osmf/components/template_loaders.php';
require_once 'osmf/components/context_processors.php';
require_once 'osmf/components/views.php';
