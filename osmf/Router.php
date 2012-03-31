<?php namespace osmf;


class Router
{
	private $routes = array();
	private $app_root = '';

	public function __construct($app_root)
	{
		$this->app_root = $app_root;
	}

	public function addRoute($pattern, $application, $view, $delimiter='%')
	{
		$pattern = $delimiter . $pattern . $delimiter;

		$this->routes[$pattern] = array(
			'application' => $application,
			'view' => $view,
		);
	}

	public function loadRoutes($path)
	{
		$router = $this;
		require $path;
	}

	public function reverse($name, $args)
	{
		throw new \Exception("Not Yet Implemented");
	}

	public function route($pathinfo)
	{
		foreach ($this->routes as $pattern => $responder) {
			if (preg_match($pattern, $pathinfo, $matches)) {
				require_once $this->app_root . '/' .  $responder['application'] . '/views.php';
				$view = 'osms\\' . $responder['application'] . '\\' . $responder['view'];

				foreach ($matches as $key => $value) {
					if (is_int($key)) {
						unset($matches[$key]);
					}
				}

				return new Router\Route($view, $matches);
			}
		}

		throw new Http\Response\NotFound("Page not found");
	}
}
