<?php namespace osmf\Auth;


interface IUserModel
{
	public function getRole();
	public function getUserId();
	public function getUsername();
	public static function get($query, $dbconf=NULL);
}
