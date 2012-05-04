<?php namespace osmf\Model\Field;


class IPAddress extends \osmf\Model\Field
{
	public function toPhp($value, $dbconf)
	{
		return long2ip($value);
	}

	public function toDb($value)
	{
		return ip2long($value);
	}
}
