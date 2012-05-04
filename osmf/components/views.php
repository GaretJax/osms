<?php namespace osmf\Views;


class DirectToTemplate extends \osmf\View
{
	public function __construct($dispatcher, $logger, $parameters, $context=NULL)
	{
		parent::__construct($dispatcher, $logger, $parameters);

		if (!is_null($context)) {
			$this->context = (object) $context;
		}

		$this->context->_dispatcher = $dispatcher;
	}

	public function render_GET($request, $args)
	{
		return $this->renderResponse(
			$this->parameters['template'],
			\array_get($this->parameters, 'response_class')
		);
	}

	public function render_POST($request, $args)
	{
		return $this->render_GET($request, $args);
	}
}


class Captcha extends \osmf\View
{
	protected function render_GET($request, $args)
	{
		$captcha = new \osmf\Captcha();
		$captcha = $captcha->generateImage();
		$request->session->set('captcha', $captcha['text']);

		return new \osmf\Http\Response\Image($captcha['image']);
	}
}
