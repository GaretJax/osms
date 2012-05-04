<?php namespace osmf\Auth;


interface IUserModel
{
	public function getRoleName();
	public function getUserId();
	public function getUsername();
	public function checkPassword($password);
	public function setPassword($password);
	public function isEnabled();
	public static function getEnabledById($userid, $dbconf);
}
