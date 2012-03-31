<?php namespace osmf\Http\Response;


class Redirect extends \osmf\Http\Response
{
	protected $url;

	public function __construct($url)
	{
		parent::__construct('', 302);
		$this->url = $url;
	}

	public function sendHeaders()
	{
		header('Location: ' . $this->url);
	}

	public function sendBody()
	{
	}
}
