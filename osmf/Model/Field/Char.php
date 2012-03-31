<?php namespace osmf\Model\Field;


class Char extends \osmf\Model\Field
{
	public function toPhp($value)
	{
		return $value;
	}

	public function toDb($value)
	{
		return $value;
	}
}
