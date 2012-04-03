<?php namespace osmf;

/**
 * Middlewares im oSMF are greatly inspired by Django's one.
 * Refer to the original documentation for more information:
 * https://docs.djangoproject.com/en/dev/topics/http/middleware/
 */


abstract class Middleware
{
	public function process_request($request)
	{
	}

	public function process_response($response)
	{
		return $response;
	}
}
