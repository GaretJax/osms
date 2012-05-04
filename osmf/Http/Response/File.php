<?php namespace osmf\Http\Response;


class File extends \osmf\Http\Response
{
	protected $file;

	public function __construct($file)
	{
		parent::__construct('OK', 200);
		$this->file = $file;
	}

	public function sendHeaders()
	{
		//header('X-SendFile: ' . $this->file->getPath());
		header("Content-type: " . $this->file->getType());
		header('Content-Disposition: attachment; filename="' . ($this->file->getBasename()) . '"');
		header("Content-Length: ". $this->file->getSize());
	}

	public function sendBody()
	{
		readfile($this->file->getPath());
	}
}

