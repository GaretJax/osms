<?php namespace osmf;


abstract class FileTemplateLoader
{
	abstract protected function getPath($path);

	public function getStream($path)
	{
		$path = $this->getPath($path);

		if (empty($path)) {
			throw new Template\NotFound();
		}
		return array(
			$path,
			fopen($path, 'rb')
		);
	}
}


class PathTemplateLoader extends FileTemplateLoader
{
	protected function getPath($path)
	{
		$paths = Config::getInstance()->template_search_dirs;

		foreach ($paths as $root) {
			$file = $root . '/' . $path;

			if (file_exists($file)) {
				return $file;
			}
		}
	}
}


class ApplicationsTemplateLoader extends FileTemplateLoader
{
	protected function getPath($path)
	{
		$apps = Config::getInstance()->installed_apps;
		$root = Config::getInstance()->app_root;

		foreach ($apps as $app) {
			$file = $root . '/' . $app . '/templates/' . $path;

			if (file_exists($file)) {
				return $file;
			}
		}
	}
}
