<?php namespace osmf\Router;


class Route
{
	protected $view;
	protected $args;
	protected $parameters;
	protected $grants;

	public function __construct($view, $args, $grants, $parameters)
	{
		$this->view = $view;
		$this->args = $args;
		$this->grants = $grants;
		$this->parameters = $parameters;
	}

	protected function checkPermissions($user)
	{
		if (in_array('*', $this->grants)) {
			return True;
		}

		if (in_array($user->getRole(), $this->grants)) {
			return True;
		}

		throw new \Exception('Permission denied');
	}

	public function getView($request)
	{
		$this->checkPermissions($request->user);
		return new $this->view($this->parameters);
	}

	public function getArgs()
	{
		return $this->args;
	}
}
