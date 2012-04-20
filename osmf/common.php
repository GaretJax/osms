<?php


function join_paths()
{
	$args = func_get_args();
	$paths = array();

	foreach($args as $arg) {
		$paths = array_merge($paths, (array)$arg);
	}

	foreach($paths as &$path) {
		$path = trim($path, '/');
	}

	$paths = array_filter($paths);

	// Keep starting slashes
	if (substr($args[0], 0, 1) == '/') {
		$paths[0] = '/' . $paths[0];
	}

	// Keep ending slashes
	if (substr(array_pop($args), -1) == '/') {
		$paths[] = '';
	}

	return join('/', $paths);
}


function array_get($array, $key, $default=NULL)
{
	if (array_key_exists($key, $array)) {
		return $array[$key];
	} else {
		return $default;
	}
}
