<?php namespace osmf\Auth;


class AnonymousUser implements IUserModel
{
	const ROLE = "anonymous";

	public function getUserId()
	{
		throw new Exception('Anonymous user has no user id');
	}

	public static function getEnabledById($id, $dbconf)
	{
		return new static();
	}

	public function checkPassword($password)
	{
		return FALSE;
	}

	public function getUsername()
	{
		return 'Anonymous';
	}

	public function setPassword($password)
	{
		throw new Exception('Anonymous user can\'t have a password');
	}

	public function isEnabled()
	{
		return TRUE;
	}

	public function getRoleName()
	{
		return AnonymousUser::ROLE;
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
			$dbconf = \osmf\Config::get('user_dbconf');

			try {
				$model = $class::getEnabledById($session->get('user_id'), $dbconf);
				return $this->loginAs($model);
			} catch(\osmf\Model\ObjectNotFound $e) {
				// User does not exist anymore (disabled)
			}
		}

		$this->model = new AnonymousUser();
		$this->is_authenticated = FALSE;
		$this->session->del('user_id');
	}

	public function getModel()
	{
		return $this->model;
	}

	public function checkPassword($password)
	{
		return $this->model->checkPassword($password);
	}

	public function checkLoginAs($model, $password)
	{
		if ($model->isEnabled()) {
			if ($model->checkPassword($password)) {
				$this->loginAs($model);
				$this->session->regenerate();
				return TRUE;
			}
		}

		return FALSE;
	}

	public function getRole()
	{
		return $this->model->getRoleName();
	}

	public function getUsername()
	{
		return $this->model->getUsername();
	}

	public function isAuthenticated()
	{
		return $this->is_authenticated;
	}

	public function setPassword($password)
	{
		$this->model->setPassword($password);
		$this->model->save();
	}

	public function loginAs($model)
	{
		$this->model = $model;
		$this->is_authenticated = TRUE;
		$this->session->set('user_id', $model->getUserId());
	}

	public function logout()
	{
		$this->model = new AnonymousUser();
		$this->is_authenticated = FALSE;
		$this->session->del('user_id');
		$this->session->regenerate();
	}
}
