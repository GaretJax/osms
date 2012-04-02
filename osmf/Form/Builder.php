<?php namespace osmf\Form;


class Builder
{
	protected $fields = array();
	protected $name;
	protected $namespace;

	public function __construct($name, $namespace=__NAMESPACE__)
	{
		$this->name = $name;
		$this->namespace = $namespace;
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
		$namespace = $this->namespace;
		$class = $this->name;
		$parent = '\osmf\Form';
		$code = "
		namespace $namespace;
		class $class extends $parent {
			protected static \$properties;
		}";
		eval($code);
		$class = $namespace . '\\' . $class;

		$reflected = new \ReflectionClass($class);

		$fields = $reflected->getProperty('properties');
		$fields->setAccessible(true);
		$fields->setValue(array(
			'fields' => &$this->fields,
		));
	}
}
