<?php namespace osmf;


abstract class Form
{
	protected $cleaned = FALSE;
	protected $fields = array();
	protected $values = array();
	protected $errors = array();
	protected $form_errors = array();
	protected $session;

	protected $is_valid = FALSE;

	public $cleaned_data = array();

	public function __construct($data=array(), $files=array(), $session=NULL)
	{
		$data = array_merge($data, $files);
		$this->session = $session;
		$this->fields = static::$properties['fields'];
		$this->populate($data);
	}

	public function getSession()
	{
		return $this->session;
	}

	public function __unset($name)
	{
		unset($this->fields[$name]);
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->fields)) {
			return $this->fields[$name];
		} else {
			$class = get_class($this);
			throw new \Exception("Invalid attribute $name for form $class");
		}
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
				$this->cleaned_data[$ref] = $field->clean($this, $this->values[$ref]);
			} catch (\osmf\Validator\ValidationError $e) {
				$e->label = $field->getLabel();
				$this->errors[$ref] = $e;
			}
		}

		$this->is_valid = count($this->errors) === 0;

		$this->cleaned = TRUE;
	}

	public function render($context=NULL)
	{
		$s ="\n";
		foreach ($this->fields as $ref => $field) {
			$s .= $this->renderField($ref, $context);
		}
		return $s;
	}

	public function renderField($ref, $context=NULL)
	{
		$field = $this->fields[$ref];
		return $field->render(\array_get($this->values, $ref), $context) . "\n";
	}

	public function errorlist()
	{
		$tpl = new Template('forms/errorlist.html');
		return $tpl->render(array(
			'errors' => $this->errors,
			'form_errors' => $this->form_errors,
		));
	}
}
