<?php namespace osmf\Views;


class DirectToTemplate extends \osmf\View
{
	public function __construct($parameters, $context=NULL)
	{
		parent::__construct($parameters);

		if (!is_null($context)) {
			$this->context = (object) $context;
		}
	}

	public function render($request)
	{
		$this->request = $request;
		return $this->renderResponse(
			$this->parameters['template'],
			\array_get($this->parameters, 'response_class')
		);
	}
}
