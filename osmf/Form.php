<?php namespace osmf;


abstract class Form
{
	protected $cleaned = FALSE;
	protected $fields = array();
	protected $values = array();
	protected $errors = array();

	protected $is_valid = FALSE;

	public $cleaned_data = array();

	public function __construct($data=array())
	{
		$properties = $this->getModelProperties();
		$this->fields = $properties['fields'];
		$this->populate($data);
	}

	protected function getModelProperties()
	{
		$class = get_class($this);
		return $class::$_properties;
	}

	protected function populate($data)
	{
		foreach ($this->fields as $ref => $field) {
			$this->values[$ref] = \array_get($data, $ref);
		}
		$this->cleaned = FALSE;
	}

	public function isValid()
	{
		if (!$this->cleaned) {
			$this->clean();
		}

		return $this->is_valid;
	}

	protected function clean()
	{
		$this->cleaned_data = array();
		$this->errors = array();

		foreach ($this->fields as $ref => $field) {
			try {
				$this->cleaned_data[$ref] = $field->clean($this->values[$ref]);
			} catch (Form\ValidationError $e) {
				$this->errors[$ref] = $e;
			}
		}

		$this->is_valid = count($this->errors) === 0;

		$this->cleaned = TRUE;
	}

	public function render()
	{
		$s ="\n";
		foreach ($this->fields as $ref => $field) {
			$s .= $field->render(\array_get($this->values, $ref)) . "\n";
		}
		return $s;
	}

	public function errorlist()
	{
		$tpl = new Template('forms/errorlist.html');
		return $tpl->render(array(
			'errors' => $this->errors,
		));
	}
}
