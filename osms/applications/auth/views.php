<?php namespace osms\auth\views;

use \osms\auth\models;
use \osms\auth\forms;


class Login extends \osmf\View
{
	protected function redirectAuthenticated()
	{
		$config = \osmf\Config::getInstance();
		$url = $config->base_url . $config->login_redirect_url;
		return $this->redirect($url);
	}

	protected function checkPermissions()
	{
		return True;
	}

	protected function render_GET($request, $args)
	{
		if ($request->user->isAuthenticated()) {
			return $this->redirectAuthenticated();
		}

		$this->context->error = FALSE;
		$this->context->form = new forms\Login();

		/*$model = new User();
		$model->username = 'test1';
		$model->password = 'pass';
		$model->save();*/

		/*$model = models\User::get(34);
		$model->password = "pass23";
		$model->save();*/

		return $this->renderResponse('auth/login.html');
	}

	protected function render_POST($request, $args)
	{
		if ($request->user->isAuthenticated()) {
			return $this->redirectAuthenticated();
		}

		$form = new forms\Login($request->POST);

		if ($form->isValid()) {
			// TODO: Check username & password
			try {
				$model = models\User::get(array(
					'username' => $form->cleaned_data['username']
				));

				$password = $form->cleaned_data['password'];
				
				if ($request->user->checkLoginAs($model, $password)) {
					return $this->redirectAuthenticated();
				}
				//var_dump($model->username);
				//$request->user->login();
			} catch (\osmf\Model\ObjectNotFound $e) {
			}
		}

		$this->context->error = TRUE;
		$this->context->form = $form;

		return $this->renderResponse('auth/login.html');
	}
}


class Logout extends \osmf\View
{
	protected function render_GET($request, $args)
	{
		$request->user->logout();
		$request->session->destroy();

		return $this->redirect(\osmf\Config::get('base_url'));
	}
}
