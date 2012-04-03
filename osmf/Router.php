<?php namespace osmf;


class Router
{
	private $routes = array();
	private $app_root = '';

	public function __construct($app_root)
	{
		$this->app_root = $app_root;
	}

	public function addRoute($pattern, $view, $grants, $parameters=array())
	{
		$pattern = '%' . $pattern . '%';

		$view = explode('.', $view);

		$this->routes[$pattern] = array(
			'application' => $view[0],
			'view' => $view[1],
			'grants' => $grants,
			'parameters' => $parameters,
		);
	}

	public function loadRoutes($path)
	{
		$routes = simplexml_load_file($path);
		foreach ($routes->route as $route) {
			$params = array();
			foreach ($route->view[0]->param as $param) {
				$params[strval($param['name'])] = $param['value'];
			}

			$grants = array();
			foreach ($route->grant as $grant) {
				$grants[] = $grant['role'];
			}

			if (count($grants) === 0) {
				throw new \Exception("View without explicit access rule {$route['name']}.");
			}

			$this->addRoute($route['pattern'], $route->view[0]['name'], $grants, $params);
		}
	}

	public function reverse($name, $args)
	{
		throw new \Exception("Not Yet Implemented");
	}

	public function route($pathinfo)
	{
		foreach ($this->routes as $pattern => $responder) {
			if (preg_match($pattern, $pathinfo, $matches)) {
				if ($responder['application'] === 'osmf') {
					// Framework provided view
					require_once 'osmf/components/views.php';
					$view = 'osmf\Views\\' . $responder['view'];
				} else {
					require_once $this->app_root . '/' .  $responder['application'] . '/views.php';
					$view = 'osms\\' . $responder['application'] . '\\views\\' . $responder['view'];
				}

				foreach ($matches as $key => $value) {
					if (is_int($key)) {
						unset($matches[$key]);
					}
				}

				return new Router\Route($view, $matches, $responder['grants'], $responder['parameters']);
			}
		}

		throw new Http\Error\Http404("Page not found");
	}
}
