<?php namespace osmf\Form;


abstract class Field
{
	protected $label;
	protected $required;
	protected $args;
	protected $ref;
	protected $type;

	public function __construct($ref, $args=array())
	{
		$this->label = \array_get($args, 'label');
		$this->required = \array_get($args, 'required', TRUE);
		$this->args = $args;
		$this->ref = $ref;

		if (is_null($this->label)) {
			$this->label = $ref;
		}
	}

	public function clean($value)
	{
		if ($this->required && ($value === NULL || strlen($value) === 0)) {
			throw new ValidationError('This field is required.');
		}
		return $value;
	}

	abstract public function render();
}
