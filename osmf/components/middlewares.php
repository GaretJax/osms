<?php namespace osmf;


class SessionMiddleware extends Middleware
{
	public function process_request($dispatcher, $request)
	{
		$config = Config::getInstance();
		$name = $config->session['name'];
		$session_id = \array_get($request->COOKIES, $name, '');
		$request->session = new Session(
			'session', $session_id,
			$config->base_url,
			$config->session['lifetime'],
			$config->session['regenerate']
		);
	}
}


abstract class FilteredExceptionMiddleware extends Middleware
{
	protected abstract function getExceptions();

	protected abstract function process_filtered_exception($dispatcher, $request, $exception);

	public final function process_exception($dispatcher, $request, $exception)
	{
		$exceptions = $this->getExceptions();

		foreach ($exceptions as $type) {
			if (is_a($exception, $type)) {
				return $this->process_filtered_exception($dispatcher, $request, $exception);
			}
		}
	}
}


class AuthenticationMiddleware extends Middleware
{
	public function process_request($dispatcher, $request)
	{
		$request->user = new Auth\User($request->session);
	}
}


class CsrfMiddleware extends Middleware
{
	public function process_request($dispatcher, $request)
	{
		if ($request->method != 'GET') {
			// Manually add exceptions for other methods here when needed
			$sent_token = array_get($request->POST, 'csrf_token');
			$stored_token = $request->session->get('csrf_token');

			if (!$stored_token || $sent_token !== $stored_token) {
				throw new \Exception("No CSRF token provided");
			}
		}
	}
}


class ExceptionTo404Middleware extends FilteredExceptionMiddleware
{
	protected function process_filtered_exception($dispatcher, $request, $exception)
	{
		if (Config::get('debug')) {
			$tpl = '404-debug.html';
		} else {
			$tpl = '404.html';
		}

		$context = new \stdClass();
		$context->exception = $exception;

		$logger = $dispatcher->getLogger();
		$type = get_class($exception);
		$message = $exception->getMessage();
		$logger->logError("Catched exception of type $type with message '$message'. Rendering 404 page instead.");

		$view = new Views\DirectToTemplate($dispatcher, $logger, array(
			'template' => $tpl,
			'response_class' => '\osmf\Http\Response\NotFound',
		), $context);

		$response = $view->render($request, array());
		return $response;
	}

	protected function getExceptions()
	{
		return array(
			'\osmf\Http\Error\Http404',
			'\osmf\Model\ObjectNotFound',
			'\osmf\FileNotFound',
		);
	}
}


class PermissionDeniedMiddleware extends FilteredExceptionMiddleware
{
	protected function process_filtered_exception($dispatcher, $request, $exception)
	{
		if (!$request->user->isAuthenticated()) {
			$url = $dispatcher->getRouter()->reverse('login');
			$url = \join_paths(\osmf\Config::get('base_url'), $url);
			return new Http\Response\Redirect($url);
		} elseif (!Config::get('debug')) {
			$context = new \stdClass();
			$context->exception = $exception;

			$logger = $dispatcher->getLogger();
			$type = get_class($exception);
			$message = $exception->getMessage();
			$logger->logError("Catched exception of type $type with message '$message'. Rendering 404 page instead.");

			$view = new Views\DirectToTemplate($dispatcher, $logger, array(
				'template' => '404.html',
				'response_class' => '\osmf\Http\Response\NotFound',
			), $context);

			$response = $view->render($request, array());
			return $response;
		}
	}

	protected function getExceptions()
	{
		return array(
			'\osmf\Router\PermissionDenied',
		);
	}

}
