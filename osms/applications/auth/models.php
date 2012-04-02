<?php namespace osms\Auth;


$user = new \osmf\Model\Builder('User', __NAMESPACE__);
$user->addColumn('username', 'Char');
$user->addColumn('password', 'Char');
