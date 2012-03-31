<?php namespace osms\Auth;


$user = new \osmf\Model\Builder('User', __NAMESPACE__);
$user->add('username', 'Char');
$user->add('password', 'Char');
$user->createModel();
