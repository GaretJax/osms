<?php namespace osms\auth\forms;


$login = new \osmf\Form\Builder(__NAMESPACE__ . '\Login');
$login->addField('username', 'Char', array(
	'label' => 'Username',
	'maxlength' => 255,
));
$login->addField('password', 'Password', array(
	'label' => 'Password',
	'maxlength' => 255,
));
$login->addField('captcha', 'Captcha', array(
	'label' => 'Human check',
	'maxlength' => 255,
));


$status = new \osmf\Form\Builder(__NAMESPACE__ . '\ChangeStatus');
$status->addField('enable', 'ModelChoice', array(
	'required' => FALSE,
	'model' => '\osms\auth\models\User',
	'dbconf' => 'admin',
	'query' => array(
		array('enabled', 'eq', FALSE),
	),
	'attribute' => 'username',
));
$status->addField('disable', 'ModelChoice', array(
	'required' => FALSE,
	'model' => '\osms\auth\models\User',
	'dbconf' => 'admin',
	'query' => array(
		array('enabled', 'eq', TRUE),
	),
	'attribute' => 'username',
));


$pwd_change = new \osmf\Form\Builder(__NAMESPACE__ . '\ChangePassword');
$pwd_change->addField('old_password', 'Password', array(
	'label' => 'Old password',
	'maxlength' => 255,
));
$pwd_change->addField('new_password_1', 'Password', array(
	'label' => 'New password',
	'minlength' => 8,
	'maxlength' => 255,
	'validators' => array(
		new \osmf\Validator\PasswordComplexity(),
	),
));
$pwd_change->addField('new_password_2', 'Password', array(
	'label' => 'Confirmation',
	'required' => FALSE,
	'maxlength' => 255,
));


$user = new \osmf\Form\Builder(__NAMESPACE__ . '\UserBase');
$user->addField('username', 'Char', array(
	'label' => 'Username',
	'minlength' => 3,
	'maxlength' => 255,
	'validators' => array(
		new \osmf\Validator\Username(),
	)
));
$user->addField('password_1', 'Password', array(
	'label' => 'Password',
	'minlength' => 8,
	'maxlength' => 255,
	'validators' => array(
		new \osmf\Validator\PasswordComplexity(),
	),
));
$user->addField('password_2', 'Password', array(
	'label' => 'Confirm',
	'required' => FALSE,
));
$user->addField('role', 'ModelChoice', array(
	'model' => '\osms\auth\models\Role',
	'label' => 'Role',
	'attribute' => 'display_name',
));
$user->addField('cro', 'ModelChoice', array(
	'label' => 'CRO',
	'model' => '\osms\auth\models\User',
	'query' => array(
		array('role', 'eq', 67),
	),
	'attribute' => 'username',
	'required' => FALSE,
));
$user->addField('enabled', 'Boolean', array(
	'label' => 'Enable user',
));


class User extends UserBase
{
	public function isValid()
	{
		$valid = parent::isValid();

		if (!$valid) {
			return FALSE;
		}

		// Check that password 1 equals password 2
		if ($this->cleaned_data['password_1'] !== $this->cleaned_data['password_2']) {
			$this->form_errors[] = new \osmf\Validator\ValidationError('The two passwords have to match');
		}

		// Check that a CRO is set if the role is a customer
		if ($this->cleaned_data['role']->name === 'customer') {
			if ($this->cleaned_data['cro'] === NULL) {
				$role = $this->cleaned_data['role']->display_name;
				$this->form_errors[] = new \osmf\Validator\ValidationError("A CRO has to be set if the $role role is selected");
			}
		} else {
			// Make sure no CRO is set
			$this->cleaned_data['cro'] = NULL;
		}

		return count($this->form_errors) === 0;
	}
}
