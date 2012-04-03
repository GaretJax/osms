<?php namespace osmf\Http;


class Response
{
	protected $code = 200;
	protected $content;

	public function __construct($content, $code=NULL)
	{
		$this->content = $content;
		if ($code !== NULL) {
			$this->code = $code;
		}
	}

	public function carryOut()
	{
		$this->sendHeaders();
		$this->sendBody();
	}

	protected function sendCode()
	{
		header('Response code', TRUE, $this->code);
	}

	protected function sendHeaders()
	{
		$this->sendCode();
	}

	protected function sendBody()
	{
		echo $this->content;
	}
}
