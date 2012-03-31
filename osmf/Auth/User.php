<?php namespace osmf\Auth;


class User
{
	protected $is_authenticated = FALSE;
	protected $session;

	public function __construct($session) {
		$this->session = $session;

		if ($session->get('user_id')) {
			$this->is_authenticated = TRUE;
		}
	}

	public function isAuthenticated()
	{
		return $this->is_authenticated;
	}

	public function login()
	{
		$this->is_authenticated = TRUE;
		$this->session->set('user_id', 1);
	}

	public function logout()
	{
		$this->is_authenticated = FALSE;
		$this->session->del('user_id');
	}
}
