<?php namespace osmf\Http;


class Request
{
	public $path;
	public $method;
	public $POST;
	public $GET;
	public $COOKIES;

	public function __construct($path, $method, $get, $post, $cookie)
	{
		$this->path = $path;
		$this->method = $method;
		$this->POST = $post;
		$this->GET = $get;
		$this->COOKIES = $cookie;
	}
}
