<?php namespace osms\auth\forms;


$login = new \osmf\Form\Builder(__NAMESPACE__ . '\Login');
$login->addField('username', 'Char', array(
	'label' => 'Username',
));
$login->addField('password', 'Password', array(
	'label' => 'Password',
));
