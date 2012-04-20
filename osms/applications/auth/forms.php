<?php namespace osms\auth\forms;


$login = new \osmf\Form\Builder(__NAMESPACE__ . '\Login');
$login->addField('username', 'Char', array(
	'label' => 'Username',
));
$login->addField('password', 'Password', array(
	'label' => 'Password',
));


$pwd_change = new \osmf\Form\Builder(__NAMESPACE__ . '\ChangePassword');
$pwd_change->addField('old_password', 'Password', array(
	'label' => 'Old password',
));
$pwd_change->addField('new_password_1', 'Password', array(
	'label' => 'New password',
));
$pwd_change->addField('new_password_2', 'Password', array(
	'label' => 'Confirmation',
));

