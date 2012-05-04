<?php namespace osmf;


require_once 'osmf/vendor/Captcha.php';


class _Captcha extends \SimpleCaptcha
{
	protected function WriteImage()
	{}

	protected function Cleanup()
	{}

	public function getImage()
	{
		return $this->im;
	}
}


class Captcha
{
	public function generateImage()
	{
		$config = Config::get('captcha');

		$captcha = new \SimpleCaptcha();
		$captcha->resourcesPath = $config['resources_path'];
		$captcha->blur = TRUE;
		$captcha->wordsFile = NULL;
		$captcha->Xamplitude = 4;
		$captcha->Yamplitude = 14;
		$captcha->maxWordLength = 7;
		$captcha->scale = 3;
		$captcha->CreateImage();

		$text = $_SESSION[$captcha->session_var];
		$image = $captcha->getImage();

		return array(
			'text' => $text,
			'image' => $image
		);
	}
}
