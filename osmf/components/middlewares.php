<?php namespace osmf;


class SessionMiddleware extends Middleware
{
	public function process_request($request)
	{
		$request->session = new Session();
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
