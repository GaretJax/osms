<?php namespace osmf\commonplugins;


class CsrfPlugin extends \osmf\Template\Plugin
{
	protected $name = 'csrfToken';

	public function render($template, $context, $args)
	{
		$tpl = new \osmf\Template('forms/fields/csrf_token.html');
		echo $tpl->render(array(
			'token' => $context->csrf_token,
		));
	}
}
$template->registerPlugin(new CsrfPlugin());


class FileHighlighingPlugin extends \osmf\Template\Plugin
{
	protected $name = 'highlight';

	public function render($template, $context, $args)
	{
		$file = $args[0];
		$start = \array_get($args, 1, 1);
		$end = \array_get($args, 2);
		$assumePhp = \array_get($args, 3, True);

		$content = file_get_contents($file);
		$lines = explode("\n", $content);
		if ($end === NULL) {
			$end = count($lines);
		}

		$lines = array_splice($lines, $start, $end - $start + 1);
		$content = implode("\n", $lines);

		if ($assumePhp) {
			$content = "<?php\n" . $content;
		}

		$content = highlight_string($content, TRUE);

		if ($assumePhp) {
			$content = str_replace('&lt;?php<br />', '', $content);
		}

		echo $content;
	}
}
$template->registerPlugin(new FileHighlighingPlugin());


class BacktracePlugin extends \osmf\Template\Plugin
{
	protected $name = 'backtrace';

	public function render($template, $context, $args)
	{
		$exc = $args[0];

		$stack = array();
		$stack[] = array(
			'file' => $exc->getFile(),
			'line' => $exc->getLine(),
		);
		$stack = array_merge($stack, $exc->getTrace());

		$tpl = new \osmf\Template('stacktrace.html');
		echo $tpl->render(array(
			'stack' => $stack,
		));

	}
}
$template->registerPlugin(new BacktracePlugin());
