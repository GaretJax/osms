<?php


function array_get($array, $key, $default=NULL)
{
	if (array_key_exists($key, $array)) {
		return $array[$key];
	} else {
		return $default;
	}
}
