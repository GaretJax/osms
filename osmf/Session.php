<?php namespace osmf;


class Session
{
	// Oh, look... event the official PHP documentation
	// mentions databases as session handlers:
	// http://ch2.php.net/manual/en/session.customhandler.php

	public function __construct()
	{
		// TODO: Set the correct params
		// session_set_cookie_params(...);

		session_start();
	}

	public function get($name, $default=NULL)
	{
		if (array_key_exists($name, $_SESSION)) {
			return $_SESSION[$name];
		} else {
			return $default;
		}
	}

	public function set($name, $value)
	{
		$_SESSION[$name] = $value;
	}

	public function del($name)
	{
		if (array_key_exists($name, $_SESSION)) {
			unset($_SESSION[$name]);
		}

	}
}
