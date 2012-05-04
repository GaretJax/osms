<?php namespace osmf;

/**
 * Middlewares im oSMF are greatly inspired by Django's one.
 * Refer to the original documentation for more information:
 * https://docs.djangoproject.com/en/dev/topics/http/middleware/
 */


abstract class Middleware
{
	public function process_request($dispatcher, $request)
	{
	}

	public function process_view($dispatcher, $request, $route, $view)
	{
	}

	public function process_exception($dispatcher, $request, $exception)
	{
	}

	public function process_response($dispatcher, $response)
	{
		return $response;
	}
}
