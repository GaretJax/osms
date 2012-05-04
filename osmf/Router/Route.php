<?php namespace osmf\Router;


class Route
{
	protected $view;
	protected $args;
	protected $parameters;
	protected $grants;
	protected $definition;
	protected $logger;

	public function __construct($logger, $view, $args, $grants, $parameters, $definition)
	{
		$this->logger = $logger;
		$this->view = $view;
		$this->args = $args;
		$this->grants = $grants;
		$this->parameters = $parameters;
		$this->definition = $definition;
	}

	protected function checkPermissions($user)
	{
		if (in_array('*', $this->grants)) {
			$this->logger->logWarn("Access to $this->view granted (wildcard)");
			return True;
		}

		if (in_array($user->getRole(), $this->grants)) {
			$this->logger->logWarn("Access to $this->view granted (role)");
			return True;
		}

		$this->logger->logWarn("Access to $this->view denied");
		throw new \Exception('Permission denied');
	}

	public function getView($dispatcher, $logger, $request)
	{
		$this->checkPermissions($request->user);
		return new $this->view($dispatcher, $logger, $this->parameters);
	}

	public function getDefinition()
	{
		return $this->definition;
	}

	public function getArgs()
	{
		return $this->args;
	}
}
