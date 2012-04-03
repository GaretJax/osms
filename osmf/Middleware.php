<?php namespace osmf;


class Middleware
{
	public function process_request($request)
	{
	}

	public function process_response($response)
	{
		return $response;
	}
}
