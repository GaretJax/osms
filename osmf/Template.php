<?php namespace osmf;


class Template
{
	protected $template;
	protected static $init = false;
	protected $context;
	protected $block_stack = array();
	protected $blocks = array();
	protected $base = NULL;
	protected $plugins = array();
	protected static $pluginsDir = array();
	protected $pluginsContext = array();

	public function __construct($path)
	{
		$this->template = $path;

		if (!Template::$init) {
			Template::init();
		}
	}

	public static function init()
	{
		stream_wrapper_register('template', '\osmf\Template\Loader');
		Template::$init = true;
	}

	public function uselib($path)
	{
		$path = 'osmf/' . $path . '.php';
		$this->pluginsContext = array();
		$template = $this;
		
		if (!array_key_exists($path, Template::$pluginsDir)) {
			require_once $path;
			Template::$pluginsDir[$path] = $this->pluginsContext;
			$this->pluginsContext = array();
		}

		$this->plugins = array_merge(
			$this->plugins,
			Template::$pluginsDir[$path]
		);
	}

	public function registerPlugin($plugin)
	{
		$this->pluginsContext[$plugin->getName()] = $plugin;
	}

	public function __call($name, $args)
	{
		return $this->plugins[$name]->render($this, $this->context, $args);
	}

	public function render($context=array(), $blocks=NULL)
	{
		if (!is_null($blocks)) {
			$this->blocks = $blocks;
		}

		$this->context = (object) $context;
		unset($blocks);
		unset($context);
		
		extract(get_object_vars($this->context), EXTR_REFS);

		ob_start();

		include 'template://' . $this->template;

		$contents = ob_get_contents();
		ob_end_clean();

		if (is_null($this->base)) {
			return $contents;
		}

		return $this->base->render($this->context, $this->blocks);
	}

	public function printEscaped($value)
	{
		echo htmlspecialchars($value);
	}

	public function p($value)
	{
		return $this->printEscaped($value);
	}


	public function dump($value)
	{
		ob_start();
		var_export($value);
		$value = ob_get_contents();
		ob_end_clean();
		return $this->printEscaped($value);
	}

	public function d($value)
	{
		return $this->dump($value);
	}


	public function printRaw($value)
	{
		echo $value;
	}

	public function r($value)
	{
		return $this->printRaw($value);
	}

	public function extend($template)
	{
		if (!is_null($this->base)) {
			throw new Exception();
		}

		$this->base = new Template($template);
	}

	public function set($name, $value)
	{
		$this->context->$name = $value;
	}

	public function block($name)
	{
		echo $this->blocks[$name];
	}

	public function startblock($name)
	{
		array_push($this->block_stack, $name);
		ob_start();
	}

	public function endblock()
	{
		$block = array_pop($this->block_stack);

		if (!array_key_exists($block, $this->blocks)) {
			$this->blocks[$block] = ob_get_contents();
		}

		ob_end_clean();
	}
}
