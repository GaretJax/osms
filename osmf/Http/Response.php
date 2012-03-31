<?php namespace osmf\Http;


class Response extends \Exception
{
	protected $code;
	protected $content;

	public function __construct($content, $code=200)
	{
		$this->content = $content;
		$this->code = $code;
	}

	public function carryOut()
	{
		$this->sendCode();
		$this->sendHeaders();
		$this->sendBody();
	}

	protected function sendCode()
	{
		header('Response code', TRUE, $this->code);
	}

	protected function sendHeaders()
	{
	}

	protected function sendBody()
	{
		echo $this->content;
	}
}
