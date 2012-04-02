<?php namespace osms\auth\models;


$user = new \osmf\Model\Builder(__NAMESPACE__ . '\BaseUser', $table='osms_auth_user');
$user->addColumn('username', 'Char');
$user->addColumn('password', 'Char');


class User extends BaseUser implements \osmf\Auth\IUserModel
{
	public function getRole()
	{
		return 'cro';
	}
}
