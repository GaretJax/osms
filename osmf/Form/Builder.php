<?php namespace osmf\Form;


class Builder
{
	protected $fields = array();
	protected $name;

	public function __construct($name)
	{
		$this->name = $name;
		$this->createForm();
	}

	public function addField($name, $fieldClass, $args=array())
	{
		$fieldClass = __NAMESPACE__ . '\Field\\' . $fieldClass;

		$field = new $fieldClass($name, $args);
		$this->fields[$name] = $field;

		return $this;
	}

	protected function createForm()
	{
		$namespace = explode('\\', $this->name);
		$class = array_pop($namespace);
		$namespace = implode('\\', $namespace);
		$parent = '\osmf\Form';
		$code = "
		namespace $namespace;
		class $class extends $parent {
			protected static \$properties;
		}";
		eval($code);

		$reflected = new \ReflectionClass($this->name);

		$fields = $reflected->getProperty('properties');
		$fields->setAccessible(true);
		$fields->setValue(array(
			'fields' => &$this->fields,
		));
	}
}
