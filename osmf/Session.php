<?php namespace osmf;


class Session
{
	// Oh, look... event the official PHP documentation
	// mentions databases as session handlers:
	// http://ch2.php.net/manual/en/session.customhandler.php

	public function __construct($name, $session_id, $path, $lifetime, $regenerate)
	{
		session_name($name);
		if ($session_id) {
			session_id($session_id);
		}

		$domain = $_SERVER['HTTP_HOST'];
		$secure = isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] === 'on';
		$httponly = TRUE;

		session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
		session_start();

		// Automatically regenerate session ID each X requests
		if ($regenerate > 0) {
			$count = $this->get('__count', 0) + 1;
			if ($count > $regenerate) {
				$this->regenerate();
				$count = 0;
			}
			$this->set('__count', $count);
		}
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

	public function regenerate()
	{
		session_regenerate_id(TRUE);
	}

	public function destroy()
	{
		// Clear the session cookie
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);

		// Destroy the session
		session_destroy();
	}
}
