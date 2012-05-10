<?php namespace osmf\commonplugins;


class CsrfPlugin extends \osmf\Template\Plugin
{
	protected $name = 'csrfToken';

	public function render($template, $context, $args)
	{
		$token = base64_encode(openssl_random_pseudo_bytes(32));
		$context->request->session->set('csrf_token', $token);

		$tpl = new \osmf\Template('forms/fields/csrf_token.html');
		echo $tpl->render(array(
			'token' => $token,
		));
	}
}
$template->registerPlugin(new CsrfPlugin());


class FileHighligthingPlugin extends \osmf\Template\Plugin
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
$template->registerPlugin(new FileHighligthingPlugin());


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


class UrlReversingPlugin extends \osmf\Template\Plugin
{
	protected $name = 'url';

	public function render($template, $context, $args)
	{
		$name = $args[0];
		$param = \array_get($args, 1, array());
		$url = $context->_dispatcher->getRouter()->reverse($name, $param);

		echo \join_paths(\osmf\Config::get('base_url'), $url);
	}
}
$template->registerPlugin(new UrlReversingPlugin());


class TextToParagraphPlugin extends \osmf\Template\Plugin
{
	protected $name = 'nl2p';

	public function render($template, $context, $args)
	{
		$value = $args[0];
		$value = htmlspecialchars($value);
		$value = preg_replace("%\s*(\r|\n){2}\s*%m", "</p>\n<p>", $value);

		echo '<p>' . $value . '</p>';
	}
}
$template->registerPlugin(new TextToParagraphPlugin());


class BytesToHumanPlugin extends \osmf\Template\Plugin
{
	protected $name = 'bytes2human';
	protected $prefixes = array(
		'B', 'kB', 'MB', 'GB', 'TB', 'EB', 'PB',
	);

	public function render($template, $context, $args)
	{
		$value = $args[0];

		$i = 0;
		while ($value > 1024 && $i<count($this->prefixes))
		{
			$value /= 1024;
			$i++;
		}

		printf('%.2f %s', $value, $this->prefixes[$i]);
	}
}
$template->registerPlugin(new BytesToHumanPlugin());
