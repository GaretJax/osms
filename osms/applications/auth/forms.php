<?php namespace osms\Auth;


$login = new \osmf\Form\Builder('LoginForm', __NAMESPACE__);
$login->addField('username', 'Char', array(
	'label' => 'Username',
));
$login->addField('password', 'Password', array(
	'label' => 'Password',
));
