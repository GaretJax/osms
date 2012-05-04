<?php namespace osmf\Http\Response;


class Image extends \osmf\Http\Response
{
	protected $image;
	protected $format;

	public function __construct($image, $format='png')
	{
		parent::__construct('OK', 200);

		$this->image = $image;
		$this->format = $format;
	}

	public function sendHeaders()
	{
		if ($this->format === 'png') {
			header("Content-type: image/png");
		} elseif ($this->format === 'jpg') {
			header("Content-type: image/jpeg");
		} else {
			throw new \NotImplementedError();
		}
	}

	public function sendBody()
	{
		if ($this->format === 'png') {
			imagepng($this->image);
		} elseif ($this->format === 'jpg') {
			imagejpeg($this->image, null, 80);
		} else {
			throw new \NotImplementedError();
		}

		imagedestroy($this->image);
	}
}
