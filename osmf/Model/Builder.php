<?php namespace osmf\Model;


class Builder
{
	protected $fields = array();
	protected $name;
	protected $table;

	public function __construct($name, $table=NULL)
	{
		$this->name = $name;
		$this->addColumn('id', 'PrimaryKey');

		if ($table === NULL) {
			$components = explode('\\', $namespace);
			array_push($components, $name);
			$table = strtolower(implode('_', $components));
		}
		$this->table = $table;

		$this->createModel();
	}

	public function addColumn($name, $fieldClass, $args=array())
	{
		$fieldClass = __NAMESPACE__ . '\Field\\' . $fieldClass;

		$field = new $fieldClass($name, $args);
		$this->fields[$name] = $field;

		return $this;
	}

	protected function createModel()
	{
		$namespace = explode('\\', $this->name);
		$class = array_pop($namespace);
		$namespace = implode('\\', $namespace);
		$parent = '\osmf\Model';
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
			'name' => $this->table,
			'fields' => &$this->fields,
		));
	}
}
