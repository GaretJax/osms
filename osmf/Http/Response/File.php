<?php namespace osmf\Http\Response;


class File extends \osmf\Http\Response
{
	protected $file;
	protected $error;

	public function __construct($file)
	{
		parent::__construct('OK', 200);
		$this->file = $file;

		$path = $file->getPath();
		if (
			!file_exists($path) or
			(!is_file($path) and !is_link($path)) or
			!is_readable($path)
		) {
			throw new \osmf\FileNotFound("File not accessible");
		}
	}

	public function sendHeaders()
	{
		header("Content-type: " . $this->file->getType());
		header('Content-Disposition: attachment; filename="' . ($this->file->getBasename()) . '"');
		header("Content-Length: ". $this->file->getSize());
	}

	public function sendBody()
	{
		readfile($this->file->getPath());
	}
}

