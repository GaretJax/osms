<?php namespace osmf;


class View
{
	protected $context;
	protected $request;
	protected $parameters;

	public function __construct($parameters)
	{
		$this->initContext();
		$this->parameters = $parameters;
	}

	protected function initContext()
	{
		$this->context = new \stdClass();
	}

	public function render($request)
	{
		$this->request = $request;
		$func = 'render_' . $request->method;
		return $this->$func($request);
	}

	protected function redirect($url)
	{
		return new Http\Response\Redirect($url);
	}

	protected function renderResponse($template)
	{
		$template = new Template($template);

		// Execute context processors
		$config = Config::getInstance();
		foreach ($config->context_processors as $cp) {
			$merge = call_user_func($cp, $this->request);
			foreach ($merge as $key => $value) {
				$this->context->$key = $value;
			}
		}

		$content = $template->render($this->context);
		return new Http\Response($content);
	}

	protected function render_GET($request)
	{
		throw new \Exception("Method not allowed");
	}

	protected function render_POST($request)
	{
		throw new \Exception("Method not allowed");
	}
}
