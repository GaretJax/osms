<?php namespace osmf;


class View
{
	protected $context;
	private $request;
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

	public function render($request, $args)
	{
		$this->request = $request;
		$func = 'render_' . $request->method;
		$response = $this->$func($request, $args);
		$this->request = NULL;
		return $response;
	}

	protected function redirect($url)
	{
		return new Http\Response\Redirect($url);
	}

	protected function renderResponse($template, $response_class=NULL)
	{
		if ($response_class === NULL) {
			$response_class = '\osmf\Http\Response';
		}

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
		return new $response_class($content);
	}

	protected function render_GET($request, $args)
	{
		throw new \Exception("Method not allowed");
	}

	protected function render_POST($request, $args)
	{
		throw new \Exception("Method not allowed");
	}
}
