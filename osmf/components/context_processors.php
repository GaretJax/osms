<?php namespace osmf;


class ContextProcessors
{
	public static function auth($request)
	{
		if (isset($request->user)) {
			return array(
				'user' => $request->user,
			);
		}
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

	public static function messaging($request)
	{
		return array(
			'message_queue' => $request->session->pop('messages', array())
		);
	}
}
