<?php namespace osmf;


class DirectToTemplate extends View
{
	private $template;

	public function __construct($template, $context=NULL)
	{
		parent::__construct();

		if (!is_null($context)) {
			$this->context = $context;
		}

		$this->template = $template;
	}

	public function render($request)
	{
		$this->request = $request;
		return $this->renderResponse($this->template);
	}
}
