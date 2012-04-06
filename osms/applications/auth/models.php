<?php namespace osms\auth\models;

$role = new \osmf\Model\Builder(__NAMESPACE__ . '\Role');
$role->addColumn('name', 'Char');


$user = new \osmf\Model\Builder(__NAMESPACE__ . '\BaseUser', $table='osms_auth_user');
$user->addColumn('username', 'Char');
$user->addColumn('password_hash', 'Char');
$user->addColumn('role', 'ForeignKey', array(
	'type' => __NAMESPACE__ . '\Role',
));


class User extends BaseUser implements \osmf\Auth\IUserModel
{
	// More info:
	//  * https://gist.github.com/972386
	//  * http://stackoverflow.com/questions/4795385/how-do-you-use-bcrypt-for-hashing-passwords-in-php
	//  * http://security.stackexchange.com/questions/4781/do-any-security-experts-recommend-bcrypt-for-password-storage/6415#6415
	//  * http://www.codinghorror.com/blog/2012/04/speed-hashing.htm
	//  * http://chargen.matasano.com/chargen/2007/9/7/enough-with-the-rainbow-tables-what-you-need-to-know-about-s.html
	//  * http://en.wikipedia.org/wiki/Bcrypt

	
	const HASHING_ROUNDS = 12;

	public function getUserId()
	{
		return $this->id;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getRole()
	{
		return $this->role->name;
	}

	protected function hash($input)
	{
		$salt = sprintf('$2a$%02d$%s', User::HASHING_ROUNDS, $this->getSalt());
		return crypt($input, $salt);
	}

	protected function getSalt()
	{
		$bytes = $this->getRandomBytes(22); // Make sure once base64 encoded, it is at least 22 chars long
		$salt = substr(str_replace('+', '.', base64_encode($bytes)), 0, 22);
		return $salt;
	}

	protected function getRandomBytes($count)
	{
		$hrand = fopen('/dev/urandom', 'rb');
		$bytes = fread($hrand, $count);
		fclose($hrand);
		return $bytes;
    }

	public function setPassword($password)
	{
		$this->password_hash = $this->hash($password);
	}

	protected function const_strcmp($str1, $str2)
	{
		// TODO: Check this (review!)
		if (strlen($str1) != strlen($str2)) {
			return FALSE;
		}

		$result = 0;

		for ($i = 0; $i < strlen($str1); $i++) {
			$result |= ord($str1[$i]) ^ ord($str2[$i]);
		}

		return 0 === $result;
	}

	public function checkPassword($password)
	{
		$hash = crypt($password, $this->password_hash);
		return $this->const_strcmp($hash, $this->password_hash);
	}
}
