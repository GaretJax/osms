<?php namespace osmf;


class Config
{
	private static $instance;

	public static function getInstance()
	{
		if (!isset(Config::$instance)) {
			Config::$instance = new Config();
		}

		return Config::$instance;
	}

	public static function get($name) {
		return Config::getInstance()->$name;
	}
}
