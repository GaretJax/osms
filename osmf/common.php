<?php


class NotImplementedError extends Exception
{}


function const_strcmp($str1, $str2)
{
	if (strlen($str1) != strlen($str2)) {
		return FALSE;
	}

	$result = 0;

	for ($i = 0; $i < strlen($str1); $i++) {
		$result |= ord($str1[$i]) ^ ord($str2[$i]);
	}

	return 0 === $result;
}


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

	$paths = array_values(array_filter($paths));

	// Keep starting slashes
	if (substr($args[0], 0, 1) == '/') {
		if ($paths) {
			$paths[0] = '/' . $paths[0];
		} else {
			return '/';
		}
	}

	// Keep ending slashes
	if (substr(array_pop($args), -1) == '/') {
		$paths[] = '';
	}

	return join('/', $paths);
}


function startswith($haystack, $needle)
{
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}


function endswith($haystack, $needle)
{
	$length = strlen($needle);
	
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}


function array_get($array, $key, $default=NULL)
{
	if (array_key_exists($key, $array)) {
		return $array[$key];
	} else {
		return $default;
	}
}


function prop_get($object, $property, $default=NULL)
{
	if (property_exists($object, $property)) {
		return $object->$property;
	} else {
		return $default;
	}
}
