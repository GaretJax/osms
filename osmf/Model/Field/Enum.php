<?php namespace osmf\Model\Field;


class Enum extends \osmf\Model\Field
{
	protected $choices;

	public function __construct($name, $args)
	{
		parent::__construct($name, $args);

		$this->choices = $args['choices'];
		$this->default = array_search($args['default'], $this->choices);
	}

	public function toPhp($value, $dbconf)
	{
		if ($value === NULL) {
			return $this->default;
		}

		// TODO: Check for invalid values

		return $this->choices[$value];
	}

	public function toDb($value)
	{
		if ($value === NULL) {
			$value = $this->default;
		}

		// TODO: Check for invalid values

		return array_search($value, $this->choices);
	}
}

