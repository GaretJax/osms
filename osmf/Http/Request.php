<?php namespace osmf\Http;


class Request
{
	public $url;
	public $method;
	public $POST;
	public $GET;
	public $COOKIE;
	public $args = array();

	public function __construct($url, $method, $get, $post, $cookie)
	{
		$this->url = $url;
		$this->method = $method;
		$this->POST = $post;
		$this->GET = $get;
		$this->COOKIE = $cookie;
	}
}
