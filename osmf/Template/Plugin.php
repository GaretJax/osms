<?php namespace osmf\Template;


abstract class Plugin
{
	protected $name;

	public function getName()
	{
		return $this->name;
	}

	abstract public function render($template, $context, $args);
}

