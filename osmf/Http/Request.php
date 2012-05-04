<?php namespace osmf\Http;


class Request
{
	public $path;
	public $method;
	public $POST;
	public $GET;
	public $FILES;
	public $COOKIES;

	public function __construct($path, $method, $get, $post, $files, $cookie)
	{
		$this->path = $path;
		$this->method = $method;
		$this->POST = $post;
		$this->GET = $get;
		$this->FILES = $files;
		$this->COOKIES = $cookie;
	}
}
