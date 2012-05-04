<?php namespace osmf\Form;


abstract class Field
{
	protected $label;
	protected $required;
	protected $args;
	protected $ref;

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

	public function setArg($name, $value)
	{
		$this->args[$name] = $value;
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function clean($form, $value)
	{
		$value = trim($value);

		if ($this->required && ($value === NULL || strlen($value) === 0)) {
			throw new \osmf\Validator\ValidationError('This field is required');
		}

		$validators = array(
			new \osmf\Validator\Length(
				\array_get($this->args, 'minlength', 0),
				\array_get($this->args, 'maxlength', PHP_INT_MAX)
			),
		);

		$validator = new \osmf\Validator\Composite(array_merge(
			$validators,
			\array_get($this->args, 'validators', array())
		));
		$validator->assertValid($value);

		return $value;
	}

	abstract public function render($value=NULL, $template=NULL);
}
