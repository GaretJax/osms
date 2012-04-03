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

	public function render_GET($request, $args)
	{
		return $this->renderResponse(
			$this->parameters['template'],
			\array_get($this->parameters, 'response_class')
		);
	}
}
