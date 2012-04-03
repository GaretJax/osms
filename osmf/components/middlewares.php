<?php namespace osmf;


class SessionMiddleware extends Middleware
{
	public function process_request($request)
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


class AuthenticationMiddleware extends Middleware
{
	public function process_request($request)
	{
		$request->user = new Auth\User($request->session);
	}
}


class CsrfMiddleware extends Middleware
{
	public function process_request($request)
	{
		// TODO: Also check other http methods
		if ($request->method == 'POST') {
			$sent_token = array_get($request->POST, 'csrf_token');
			$stored_token = $request->session->get('csrf_token');

			if (!$stored_token || $sent_token !== $stored_token) {
				throw new \Exception("No CSRF token provided");
			}
		}
	}
}


class NotFoundMiddleware extends Middleware
{
	public function process_exception($request, $exception)
	{
		if (!is_a($exception, '\osmf\Http\Error\Http404')) {
			return NULL;
		}

		if (Config::get('debug')) {
			$tpl = '404-debug.html';
		} else {
			$tpl = '404.html';
		}
			
		$context = new \stdClass();
		$context->exception = $exception;
		$view = new Views\DirectToTemplate(array(
			'template' => $tpl,
			'response_class' => '\osmf\Http\Response\NotFound',
		), $context);

		$response = $view->render($request);
		return $response;
	}
}
