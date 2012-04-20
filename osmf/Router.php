<?php namespace osmf;


class Router
{
	private $routes = array();
	private $app_root = '';

	public function __construct($app_root)
	{
		$this->app_root = $app_root;
	}

	public function addRoute($name, $pattern, $view, $grants, $parameters=array())
	{
		$pattern = '%' . $pattern . '%';

		$view = explode('.', $view);

		$this->routes[$name] = array(
			'pattern' => $pattern,
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
				$params[strval($param['name'])] = strval($param['value']);
			}

			$grants = array();
			foreach ($route->grant as $grant) {
				$grants[] = strval($grant['role']);
			}

			if (count($grants) === 0) {
				throw new \Exception("View without explicit access rule {$route['name']}.");
			}

			$this->addRoute(
				strval($route['name']),
				strval($route['pattern']),
				strval($route->view[0]['name']),
				$grants,
				$params
			);
		}
	}

	public function reverse($name, $args=array())
	{
		if (!array_key_exists($name, $this->routes)) {
			throw new \Exception("No reverse match for '$name'");
		}

		$pattern = $this->routes[$name]['pattern'];

		$pattern = trim($pattern, '%');
		$pattern = ltrim($pattern, '^');
		$pattern = rtrim($pattern, '$');

		if ($args) {
			// TODO: Replace groups with args
			throw new \Exception('Groups not yet implemented');
		}

		return $pattern;
	}

	public function route($pathinfo)
	{
		foreach ($this->routes as $name => $spec) {
			if (preg_match($spec['pattern'], $pathinfo, $matches)) {
				if ($spec['application'] === 'osmf') {
					// Framework provided view
					require_once 'osmf/components/views.php';
					$view = 'osmf\Views\\' . $spec['view'];
				} else {
					require_once $this->app_root . '/' .  $spec['application'] . '/views.php';
					$view = 'osms\\' . $spec['application'] . '\\views\\' . $spec['view'];
				}

				foreach ($matches as $key => $value) {
					if (is_int($key)) {
						unset($matches[$key]);
					}
				}

				return new Router\Route($view, $matches, $spec['grants'], $spec['parameters']);
			}
		}

		throw new Http\Error\Http404("Page not found");
	}
}
