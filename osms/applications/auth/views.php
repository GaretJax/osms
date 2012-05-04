<?php namespace osms\auth\views;

use \osms\auth\models;
use \osms\auth\forms;


class Login extends \osmf\View
{
	protected function render_GET($request, $args)
	{
		if ($request->user->isAuthenticated()) {
			$url = \osmf\Config::get('login_redirect_url');
			$this->logger->logInfo("User already authenticated, redirecting to $url");
			return $this->redirect($this->reverse($url));
		}

		$attempt = models\LoginAttempt::getOrCreate();
		$this->context->form_errors = FALSE;
		$this->context->error = FALSE;
		$this->context->form = new forms\Login();

		if ($attempt->attempts < 3) {
			unset($this->context->form->captcha);
		}

		return $this->renderResponse('auth/login.html');
	}

	protected function render_POST($request, $args)
	{
		if ($request->user->isAuthenticated()) {
			$url = \osmf\Config::get('login_redirect_url');
			$this->logger->logInfo("User already authenticated, redirecting to $url");
			return $this->redirect($this->reverse($url));
		}

		$attempt = models\LoginAttempt::getOrCreate();

		$form = new forms\Login($request->POST, array(), $request->session);
		$this->context->form_errors = FALSE;

		if ($attempt->attempts < 3) {
			unset($form->captcha);
		} else {
			$this->logger->logNotice("Too many login attempts, displaying captcha");
		}

		if ($form->isValid()) {
			try {
				$model = models\User::query()
					->where('username', 'eq', $form->cleaned_data['username'])
					->one();

				$password = $form->cleaned_data['password'];

				if ($request->user->checkLoginAs($model, $password)) {
					$attempt->delete();
					$url = \osmf\Config::get('login_redirect_url');
					$username = $model->username;
					$this->logger->logNotice("User correctly authenticated as $username, redirecting to $url");
					return $this->redirect($this->reverse($url));
				} else {
					$this->logger->logWarn("User provided a wrong password (attempt $attempt->attempts)");
				}
			} catch (\osmf\Model\ObjectNotFound $e) {
				$this->logger->logWarn("User provided a wrong username (attempt $attempt->attempts)");
			}

			$attempt->inc();

			$form = new forms\Login($request->POST, array(), $request->session);
			if ($attempt->attempts < 3) {
				unset($form->captcha);
			} else {
				$this->logger->logNotice("Too many login attempts, displaying captcha");
			}
		} else {
			$this->logger->logWarn("Invalid data submitted to the login form");
			$this->context->form_errors = TRUE;
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
		$username = $request->user->getUsername();
		$request->user->logout();
		$request->session->destroy();

		$this->logger->logNotice("User $username correctly logged out");

		return $this->redirect($this->reverse('index'));
	}
}


class ChangePassword extends \osmf\View\Transaction
{
	protected function render_GET($request, $args)
	{
		$this->context->error = FALSE;
		$this->context->auth_error = FALSE;
		$this->context->form = new forms\ChangePassword();

		return $this->renderResponse('auth/change-password.html');
	}

	protected function render_POST($request, $args)
	{
		$form = new forms\ChangePassword($request->POST);
		$this->context->auth_error = FALSE;

		if ($form->isValid()) {
			if ($form->cleaned_data['new_password_1'] === $form->cleaned_data['new_password_2']) {
				if ($request->user->checkPassword($form->cleaned_data['old_password'])) {
					$request->user->setPassword($form->cleaned_data['new_password_1']);
					$this->message('Password successfully changed!');
					$username = $request->user->getUsername();
					$this->logger->logNotice("Password for $username correctly changed");
					return $this->redirect($this->reverse('index'));
				} else {
					$this->logger->logWarn("Current password didn't match while changing password");
				}
			}
			$this->context->auth_error = TRUE;
		}

		$this->logger->logWarn("Invalid data submitted to the password change form");

		$this->context->error = TRUE;
		$this->context->form = $form;
		$this->setDbError();

		return $this->renderResponse('auth/change-password.html');
	}
}


class ListUsers extends \osmf\View
{
	protected function render_GET($request, $args)
	{
		$this->context->users = models\User::query()
			->orderBy('username')
			->all();
		return $this->renderResponse('auth/list-users.html');
	}
}


class AddUser extends \osmf\View\Transaction
{
	protected function render_GET($request, $args)
	{
		$this->context->error = FALSE;
		$this->context->form = new forms\User();
		return $this->renderResponse('auth/add-user.html');
	}

	protected function render_POST($request, $args)
	{
		$form = new forms\User($request->POST);

		if ($form->isValid()) {
			// TODO: Handle duplicate user exception
			$user = new models\User('admin');
			$user->username = $form->cleaned_data['username'];
			$user->setPassword($form->cleaned_data['password_1']);
			$user->enabled = $form->cleaned_data['enabled'];
			$user->role = $form->cleaned_data['role'];
			$user->cro = $form->cleaned_data['cro'];
			$user->save();
			$this->message("The user $user->username was correcly added!");
			$this->logger->logNotice("New user $user->username correctly added to the system");
			return $this->redirect($this->reverse('manage-users'));
		}

		$this->logger->logWarn("Invalid data submitted to the user add form");
	
		$this->context->error = TRUE;
		$this->context->form = $form;
		$this->setDbError();
		return $this->renderResponse('auth/add-user.html');
	}
}


class ChangeUserStatus extends \osmf\View\Transaction
{
	protected function render_POST($request, $args)
	{
		$form = new forms\ChangeStatus($request->POST);

		if ($form->isValid()) {
			$user = NULL;

			if ($form->cleaned_data['enable']) {
				$user = $form->cleaned_data['enable'];
				$user->enabled = TRUE;
				$this->logger->logInfo("Enabling request received for $user->username");
			} elseif ($form->cleaned_data['disable']) {
				$user = $form->cleaned_data['disable'];
				$user->enabled = FALSE;
				$this->logger->logInfo("Disabling request received for $user->username");
			}

			if ($user and $user->username != $request->user->getUsername()) {
				$user->save();
				$this->logger->logNotice("User status correctly changed");
			} else {
				$this->logger->logWarn("Ignoring user status update");
			}
		} else {
			$this->logger->logWarn("Invalid data submitted to the user status change form, redirecting to user listing");
		}

		return $this->redirect($this->reverse('manage-users'));
	}
}
