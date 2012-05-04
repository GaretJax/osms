<?php namespace osmf\Model\Field;


class ForeignKey extends \osmf\Model\Field
{
	protected $type;
	protected $dbconf;

	public function __construct($name, $args)
	{
		parent::__construct($name, $args);

		$this->type = $args['type'];
	}



	protected function get($key, $dbconf)
	{
		$model = $this->type;
		return $model::getById($key, $dbconf);
	}

	public function toPhp($value, $dbconf)
	{
		if ($value === NULL) {
			return NULL;
		}
		return $this->get(intval($value), $dbconf);
	}

	public function toDb($value)
	{
		if ($value === NULL) {
			return NULL;
		}

		if (is_int($value)) {
			return $value;
		}

		return intval($value->id);
	}
}

