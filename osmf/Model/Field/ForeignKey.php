<?php namespace osmf\Model\Field;


class ForeignKey extends \osmf\Model\Field
{
	protected $type;

	public function __construct($name, $args)
	{
		parent::__construct($name, $args);

		$this->type = $args['type'];
	}

	public function get($key)
	{
		$model = $this->type;
		return $model::get(array('id' => intval($key)));
	}

	public function toPhp($value)
	{
		return intval($value);
	}

	public function toDb($value)
	{
		return strval(intval($value));
	}
}

