<?php namespace osmf\Model;


abstract class Field
{
	protected $args;
	protected $name;

	public function __construct($name, $args=array())
	{
		$this->args = $args;
		$this->name = $name;
	}

	abstract public function toPhp($value);

	abstract public function toDb($value);
}
