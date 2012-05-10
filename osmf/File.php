<?php namespace osmf;


class File
{
	protected $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getBasename()
	{
		return basename($this->path);
	}

	public function getType()
	{
		$finfo = new \finfo(FILEINFO_MIME_TYPE);
		return $finfo->file($this->getPath());
	}

	public function getExtension()
	{
		$chunks = explode('.', $this->getBasename());
		return $chunks[count($chunks) - 1];
	}

	public function getMTime()
	{
		$timestamp = filemtime($this->getPath());
		return \DateTime::createFromFormat('U', $timestamp);
	}

	public function countLines()
	{
		return count(file($this->getPath()));
	}

	public function delete()
	{
		return unlink($this->getPath());
	}

	public function getSize()
	{
		return filesize($this->getPath());
	}

	public function moveUploadedFileTo($path)
	{
		$result = move_uploaded_file($this->path, $path);
		$this->path = $path;
		return $result;
	}
}
