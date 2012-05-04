<?php namespace osmf\Model\Field;


class Integer extends \osmf\Model\Field
{
	public function toPhp($value, $dbconf)
	{
		return intval($value);
	}

	public function toDb($value)
	{
		if ($value === NULL) {
			$value = $this->default;
		}

		return intval($value);
	}
}
