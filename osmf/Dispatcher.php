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
		$request->url = $this->getUrl();

		// Clean the global environment
		unset($_POST);
		unset($_GET);
		unset($_REQUEST);


		return $request;
	}

	protected function process_middlewares($method, $arguments, $retype=NULL, $reverse=FALSE, $update=FALSE)
	{
		if ($reverse) {
			$middlewares = array_reverse($this->middlewares);
		} else {
			$middlewares = $this->middlewares;
		}

		$method = 'process_' . $method;

		foreach ($middlewares as $middleware) {
			$result = call_user_func_array(array($middleware, $method), $arguments);
			if ($result !== NULL) {
				// The middleware processing function returned a result; make sure
				// it is a of the right type and interrupt processing.
				// If the type does not match, raise an exception.
				if ($retype !== NULL and !$update and is_a($result, $retype)) {
					return $result;
				} else if ($retype !== NULL and $update) {
					$object = $result;
				} else {
					$type = gettype($result);
					$class = get_class($middleware);
					throw new \Exception("Invalid object of type '$type' returned from '$class' middleware, expecting '$retype'");
				}
			} else if ($update and $retype !== NULL) {
				$class = get_class($middleware);
				throw new \Exception("NULL returned from '$class' middleware, expecting '$retype'");
			}
		}

		if ($update) {
			return $object;
		} else {
			return NULL;
		}
	}

	public function dispatch()
	{
		// TODO: Pass arguments for GET, POST, etc to the request object.
		//       Only the dispatcher should be aware of global variables.
		$request = $this->getRequest();

		try {
			// Process the request middlewares before anything else. If a response
			// is returned, stop immediately and jump to middleware response 
			// processing.
			$response = $this->process_middlewares(
				'request', array($request),
				'\osmf\Http\Response'
			);

			// Process the actual view only if none of the processed middlewares
			// already returned a response object.
			if ($response == NULL) {
				try {
					$route = $this->router->route($request->url);
				} catch(\Exception $e) {
					// An exception occurred while routing a request,
					// process exception middlewares in reverse order
					// and stop as soon as we have a valid response object.
					$response = $this->process_middlewares(
						'exception', array($request, $e),
						'\osmf\Http\Response', TRUE
					);

					// If no exception middleware returned a valid response,
					// raise the exception.
					if ($response === NULL) {
						throw $e;
					}
				}

				if ($response === NULL) {
					$view = $route->getView($request);

					$response = $this->process_middlewares(
						'view', array($request, $view),
						'\osmf\Http\Response'
					);

					// If new response was returned until now, render the view
					if ($response === NULL) {
						try {
							$response = $view->render($request);
						} catch (\Exception $e) {
							// An exception occurred while rendering a view,
							// process exception middlewares in reverse order
							// and stop as soon as we have a valid response object.
							$response = $this->process_middlewares(
								'exception', array($request, $e),
								'\osmf\Http\Response', TRUE
							);

							// If no exception middleware returned a valid response,
							// raise the exception.
							if ($response === NULL) {
								throw $e;
							}
						}
					}
				}
			}

			// Process response middlewares in reverse order and update the 
			// $response object each time.
			$response = $this->process_middlewares(
				'response', array($response),
				'\osmf\Http\Response',
				TRUE, TRUE
			);

			return $response;
		} catch (\Exception $e) {
			if (Config::get('debug')) {
				$tpl = '500-debug.html';
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
