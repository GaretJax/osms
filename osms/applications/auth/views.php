<?php namespace osms\Auth;


require_once 'applications/auth/models.php';
require_once 'applications/auth/forms.php';


class Login extends \osmf\View
{
	protected function buildLoginForm()
	{
	}

	protected function redirectAuthenticated($request)
	{
		$config = \osmf\Config::getInstance();

		if ($request->user->isAuthenticated()) {
			$url = $config->base_url . $config->login_redirect_url;
			throw $this->redirect($url);
		}
	}

	protected function render_GET($request)
	{
		$this->redirectAuthenticated($request);
		$this->context->error = FALSE;
		$this->context->form = new LoginForm();

		/*$model = new User();
		$model->username = 'test1';
		$model->password = 'pass';
		$model->save();*/

		$model = User::get(34);
		$model->password = "pass23";
		$model->save();

		//throw new \Exception("You are not allowed to access this method");

		return $this->renderResponse('auth/login.html');
	}

	protected function render_POST($request)
	{
		$this->redirectAuthenticated($request);
		$form = new LoginForm($request->POST);

		if ($form->isValid()) {
			// TODO: Check username & password
			$request->user->login();
			return $this->redirect(\osmf\Config::get('base_url'));
		}

		$this->context->error = TRUE;
		$this->context->form = $form;

		return $this->renderResponse('auth/login.html');
	}
}


class Logout extends \osmf\View
{
	protected function render_GET($request)
	{
		$this->request->user->logout();

		return $this->redirect(\osmf\Config::get('base_url'));
	}
}
