<?php namespace osmf\Router;


class Route
{
	protected $view;
	protected $args;

	public function __construct($view, $args)
	{
		$this->view = $view;
		$this->args = $args;
	}

	public function getView()
	{
		$view = $this->view;
		return new $view();
	}

	public function getArgs()
	{
		return $this->args;
	}
}
