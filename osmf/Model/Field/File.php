<?php namespace osmf\Model\Field;


class File extends \osmf\Model\Field
{
	public function __construct($name, $args)
	{
		parent::__construct($name, $args);
	}

	public function toPhp($value, $dbconf)
	{
		if (!$value) {
			return NULL;
		}

		return new \osmf\File($value);
	}

	public function toDb($value)
	{
		if ($value === NULL) {
			return NULL;
		}

		return $value->getPath();
	}
}
