<?php namespace osmf\Model;


class Builder
{
	protected $fields = array();
	protected $name;
	protected $namespace;
	protected $table;

	public function __construct($name, $namespace=__NAMESPACE__, $table=NULL)
	{
		$this->name = $name;
		$this->namespace = $namespace;
		$this->add('id', 'PrimaryKey');

		if ($table === NULL) {
			$components = explode('\\', $namespace);
			array_push($components, $name);
			$table = strtolower(implode('_', $components));
		}
		$this->table = $table;
	}

	public function add($name, $fieldClass, $args=array())
	{
		$fieldClass = __NAMESPACE__ . '\Field\\' . $fieldClass;

		$field = new $fieldClass($name, $args);
		$this->fields[$name] = $field;

		return $this;
	}

	public function createModel()
	{
		$namespace = $this->namespace;
		$class = $this->name;
		$parent = '\osmf\Model';
		$code = "
		namespace $namespace;
		class $class extends \osmf\Model {
			protected static \$_properties;
		}";
		eval($code);
		$class = $namespace . '\\' . $class;

		$reflected = new \ReflectionClass($class);

		$fields = $reflected->getProperty('_properties');
		$fields->setAccessible(true);
		$fields->setValue(array(
			'name' => $this->table,
			'fields' => $this->fields,
		));

		runkit_method_copy($class, 'get', 'osmf\Model', '_get');
	}
}
