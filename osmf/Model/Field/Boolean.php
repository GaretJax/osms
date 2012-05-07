<?php namespace osmf\Model\Field;


class Boolean extends \osmf\Model\Field
{
	public function toPhp($value, $dbconf)
	{
		return intval($value) === 1;
	}

	public function toDb($value)
	{
		if ($value) {
			return 1;
		} else {
			return 0;
		}
	}
}

