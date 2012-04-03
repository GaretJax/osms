<?php namespace osmf\Auth;


class AnonymousUser
{
	public function getRole()
	{
		return NULL;
	}
}


class User
{
	protected $session;
	protected $is_authenticated;
	protected $model;

	public function __construct($session) {
		$this->session = $session;

		if ($session->get('user_id')) {
			$class = \osmf\Config::get('user_model');
			$this->is_authenticated = TRUE;
			$this->model = $class::get(intval($session->get('user_id')));
		} else {
			$this->is_authenticated = FALSE;
			$this->model = new AnonymousUser();
		}
	}

	public function getRole()
	{
		return $this->model->getRole();
	}

	public function getUsername()
	{
		return $this->model->getUsername();
	}


	public function isAuthenticated()
	{
		return $this->is_authenticated;
	}

	public function login()
	{
		$this->is_authenticated = TRUE;
		$this->session->set('user_id', 34);
		$this->session->regenerate();
	}

	public function logout()
	{
		$this->is_authenticated = FALSE;
		$this->session->del('user_id');
		$this->session->regenerate();
	}
}
