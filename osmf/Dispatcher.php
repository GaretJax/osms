<?php namespace osmf;


class Dispatcher
{
	protected $router;
	protected $middlewares = array();

	public function __construct($router)
	{
		$this->router = $router;
		$this->loadMiddlewares();
	}

	protected function loadMiddlewares()
	{
		$this->middlewares = array();

		foreach (Config::get('middleware_classes') as $middleware) {
			array_push($this->middlewares, new $middleware());
		}
	}

	protected function getUrl()
	{
		if (!array_key_exists('PATH_INFO', $_SERVER)) {
			$_SERVER['PATH_INFO'] = '';
		}

		if ($_SERVER['REQUEST_URI'] === $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO']) {
			header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . $_SERVER['PATH_INFO']);
			die();
		}

		return $_SERVER['PATH_INFO'];
	}

	protected function getRequest()
	{
		$request = new Http\Request();
		$request->method = $_SERVER['REQUEST_METHOD'];

		return $request;
	}

	public function dispatch()
	{
		$request = $this->getRequest();

		try {
			foreach ($this->middlewares as $middleware) {
				$middleware->process_request($request);
			}

			$url = $this->getUrl();
			$route = $this->router->route($url);
			$request->args = $route->getArgs();

			unset($_POST);
			unset($_GET);
			unset($_REQUEST);

			$view = $route->getView($request);
			return $view->render($request);
		} catch (Http\Response\NotFound $e) {
			if (Config::get('debug')) {
				$tpl = 'debug404.html';
			} else {
				$tpl = '404.html';
			}
			
			$context = new \stdClass();
			$context->exception = $e;
			$view = new Views\DirectToTemplate(array('template' => $tpl), $context);
			return $view->render($request);
		} catch (Http\Response $e) {
			return $e;
		} catch (\Exception $e) {
			if (Config::get('debug')) {
				$tpl = 'debug500.html';
			} else {
				$tpl = '500.html';
			}
	
			$context = new \stdClass();
			$context->exception = $e;
			$view = new Views\DirectToTemplate(array('template' => $tpl), $context);
			return $view->render($request);
		}
	}
}
