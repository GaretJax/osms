<?php namespace osmf;


class ContextProcessors
{
	public static function auth($request)
	{
		return array(
			'user' => $request->user,
		);
	}

	public static function config($request)
	{
		return array(
			'config' => Config::getInstance(),
		);
	}

	public static function request($request)
	{
		return array(
			'request' => $request,
		);
	}

	public static function csrfToken($request)
	{
		// TODO: Use here a custom hash function, hint: vulnerability
		$token = '123456';

		$request->session->set('csrf_token', $token);

		return array(
			'csrf_token' => $token,
		);
	}
}
