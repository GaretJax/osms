<?php namespace osmf;


require_once 'osmf/vendor/KLogger.php';


class Logger extends \KLogger
{
	protected $request;

	public function __construct($logdir, $severity)
	{
		if (is_string($severity)) {
			$severity = constant('\KLogger::' . $severity);
		}
		return parent::__construct($logdir, $severity);
	}

	public function setRequest($request)
	{
		$this->request = $request;
	}

	protected function _getTimeLine($level)
	{
		$line = parent::_getTimeLine($level);

		$details = array(
			$_SERVER['REMOTE_ADDR']
		);

		if ($this->request) {
			$user = \prop_get($this->request, 'user');
			$username = $user ? $user->getUsername() : '<not authenticated>';

			$session = \prop_get($this->request, 'session');
			$session_id = $session ? $session->getId() : '<no session id>';

			array_push($details, "$username:$session_id");
		}

		$details = sprintf('- %s -', implode(' - ', $details));

		$line = str_replace('-->', $details, $line);

		return $line;
	}

}
