<?php namespace osmf;


class View
{
	protected $context;
	protected $dispatcher;
	protected $logger;
	private $request;
	protected $parameters;

	public function __construct($dispatcher, $logger, $parameters)
	{
		$this->dispatcher = $dispatcher;
		$this->parameters = $parameters;
		$this->logger = $logger;
		$this->initContext();
	}

	protected function initContext()
	{
		$this->context = new \stdClass();
		$this->context->_dispatcher = $this->dispatcher;
	}

	public function render($request, $args)
	{
		$this->logger->logInfo("Rendering $request->method request for " . get_class($this));

		$this->preRender();

		$this->request = $request;
		$func = 'render_' . $request->method;
		$response = $this->$func($request, $args);
		$this->request = NULL;

		$this->postRender();

		return $response;
	}

	protected function preRender()
	{}

	protected function postRender()
	{}

	protected function reverse($name, $args=array())
	{
		$url = $this->dispatcher->getRouter()->reverse($name, $args);
		return \join_paths(\osmf\Config::get('base_url'), $url);
	}

	protected function redirect($url)
	{
		return new Http\Response\Redirect($url);
	}

	protected function message($message)
	{
		$messages = $this->request->session->get('messages', array());
		array_push($messages, $message);
		$this->request->session->set('messages', $messages);
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
			if ($merge === NULL) {
				continue;
			}
			foreach ($merge as $key => $value) {
				$this->context->$key = $value;
			}
		}

		$content = $template->render($this->context);
		return new $response_class($content);
	}

	protected function render_GET($request, $args)
	{
		$this->logger->logInfo("Method GET not allowed");
		throw new \Exception("Method GET not allowed");
	}

	protected function render_POST($request, $args)
	{
		$this->logger->logInfo("Method POST not allowed");
		throw new \Exception("Method POST not allowed");
	}
}
