<?php namespace osmf\Model\Field;


class DateTime extends \osmf\Model\Field
{
	protected $auto_add;

	public function __construct($name, $args)
	{
		parent::__construct($name, $args);

		$this->auto_add = \array_get($args, 'auto_add_now', FALSE);
	}

	public function toPhp($value, $dbconf)
	{
		return new \DateTime($value);
	}

	public function toDb($value)
	{
		if ($value === NULL) {
			if ($this->auto_add) {
				$value = new \DateTime();
			} else {
				return NULL;
			}
		}

		return $value->format('c');
	}
}
