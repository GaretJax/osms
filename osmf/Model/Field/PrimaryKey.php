<?php namespace osmf\Model\Field;


class Primarykey extends \osmf\Model\Field
{
	public function toPhp($value, $dbconf)
	{
		return intval($value);
	}

	public function toDb($value)
	{
		return strval(intval($value));
	}
}

