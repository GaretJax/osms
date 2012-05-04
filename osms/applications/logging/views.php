<?php namespace osms\logging\views;


class ViewLogs extends \osmf\View
{
	protected function render_GET($request, $args)
	{
		$pattern = \join_paths(
			\osmf\Config::get('log_dir'),
			'*.txt'
		);
		$log_files = glob($pattern);

		rsort($log_files);

		$log_files = array_map(
			create_function('$f', 'return new \osmf\File($f);'),
			$log_files
		);

		$this->context->log_files = $log_files;

		return $this->renderResponse('logging/list.html');
	}
}


class LogFile extends \osmf\View
{
	protected function render_GET($request, $args)
	{
		$file = new \osmf\File(
			\join_paths(
				\osmf\Config::get('log_dir'),
				$args['name']
			)
		);
		$name = $file->getBasename();

		$this->logger->logNotice("Downloading log file named $name");

		// TODO: Raise 404 if file not found
		return new \osmf\Http\Response\File($file);
	}
}
