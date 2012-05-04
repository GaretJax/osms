<?php namespace osms\auth\models;

$role = new \osmf\Model\Builder(__NAMESPACE__ . '\Role');
$role->addColumn('name', 'Char');
$role->addColumn('display_name', 'Char');


$attempt = new \osmf\Model\Builder(__NAMESPACE__ . '\BaseAttempt', $table='osms_auth_loginattempt');
$attempt->addColumn('ip', 'IPAddress');
$attempt->addColumn('attempts', 'Integer');


class LoginAttempt extends BaseAttempt
{
	public static function getOrCreate($ipaddress=NULL)
	{
		if ($ipaddress === NULL) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		}

		try {
			$query = static::query();
			return $query->where('ip', 'eq', $ipaddress)->one();
		} catch (\osmf\Model\ObjectNotFound $e) {
			$model = new static();
			$model->ip = $ipaddress;
			$model->attempts = 0;
			$model->save();
			return $model;
		}
	}

	public function inc()
	{
		$this->attempts += 1;
		$this->save();
	}
}


$user = new \osmf\Model\Builder(__NAMESPACE__ . '\BaseUser', $table='osms_auth_user');
$user->addColumn('username', 'Char');
$user->addColumn('password_hash', 'Char');
$user->addColumn('role', 'ForeignKey', array(
	'type' => __NAMESPACE__ . '\Role',
));
$user->addColumn('cro', 'ForeignKey', array(
	'type' => __NAMESPACE__ . '\User',
));
$user->addColumn('enabled', 'Boolean');


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

	public function isEnabled()
	{
		return $this->enabled;
	}

	public static function getEnabledById($userid, $dbconf=NULL, $enabled=TRUE)
	{
		return static::query($dbconf)
			->where('id', 'eq', $userid)
			->and('enabled', 'eq', $enabled)
			->one();
	}

	public function getUsername()
	{
		return $this->_getProperty('username');
	}

	public function getRoleName()
	{
		return $this->_getProperty('role')->name;
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
		return openssl_random_pseudo_bytes($count);
    }

	public function setPassword($password)
	{
		$this->password_hash = $this->hash($password);
	}

	public function checkPassword($password)
	{
		$hash = crypt($password, $this->password_hash);
		return \const_strcmp($hash, $this->password_hash);
	}
}
