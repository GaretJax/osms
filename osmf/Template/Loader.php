<?php namespace osmf\Template;


class Loader
{
	public $context;

	private $stream;

	protected function getStream($path)
	{
		$path = substr_replace($path, '', 0, 11);

		$loaders = \osmf\Config::getInstance()->template_loaders;

		foreach ($loaders as $loader) {
			try {
				$loader = new $loader();
				return $loader->getStream($path);
			} catch (NotFound $e) {}
		}

		throw new NotFound();
	}

	public function stream_open($path, $mode, $options, &$opened_path)
	{
		if ($mode != 'rb') {
			return FALSE;
		}

		try {
			$opened = $this->getStream($path);
		} catch (TemplateNotFound $e) {
			return FALSE;
		}

		$path = $opened[0];
		$this->stream = $opened[1];

		if ($options & STREAM_USE_PATH) {
			$opened_path = $path;
		}

		return TRUE;
	}

	public function stream_read($count)
	{
		return fread($this->stream, $count);
	}

	public function stream_stat()
	{
		return fstat($this->stream);
	}

	public function stream_eof()
	{
		feof($this->stream);
	}
}
