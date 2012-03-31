<?php namespace osmf\Http;


class Request
{
	public $method;
	public $POST;
	public $GET;
	public $args = array();

	public function __construct()
	{
		$this->POST = $_POST;
		$this->GET = $_GET;
	}
}
