<?php namespace osmf;


abstract class Model
{
	protected $fields = array();
	protected $values = array();
	protected $loadedValues = array();
	protected $table;
	protected $dbh;

	public function __construct($dbconf=NULL)
	{
		$this->fields = static::$properties['fields'];
		$this->table = static::$properties['name'];
		$this->dbh = \osmf\Database\Driver::getInstance($dbconf);
	}

	public function __get($name)
	{
		$this->checkPropertyName($name);
		
		if (array_key_exists($name, $this->values)) {
			return $this->values[$name];
		} else {
			return $this->loadedValues->$name;
		}
	}

	public function __set($name, $value)
	{
		$this->checkPropertyName($name);
		$this->values[$name] = $value;
	}

	public function save()
	{
		$fields = $this->getDirtyFields();

		$values = array();
		foreach ($this->values as $key => $val) {
			// TODO: Clean data
			$values[':' . $key] = $val;
		}

		if ($this->id) {
			$stmt = $this->dbh->prepareUpdateStatement($this->table, array('id'), $fields);
			$values['id'] = $this->id;
		} else {
			$stmt = $this->dbh->prepareInsertStatement($this->table, $fields);
		}

		// TODO: Log query
		//$stmt->debugDumpParams();
		$stmt->execute($values);
	}

	public static function get($id, $dbconf=NULL)
	{
		$properties = static::$properties;
		$fields = $properties['fields'];

		$dbh = \osmf\Database\Driver::getInstance($dbconf);
		$stmt = $dbh->prepareSelectStatement(
			$properties['name'],
			array('id'),
			array_keys($properties['fields'])
		);

		$stmt->execute(array('id' => $id));

		if ($stmt->rowCount() === 0) {
			throw new \Exception('Object not found');
		} else if ($stmt->rowCount() > 1) {
			throw new \Exception('Multiple results returned');
		}

		$dataobj = $stmt->fetch(\PDO::FETCH_LAZY);

		$model = new static();
		$model->loadedValues = $dataobj;
		return $model;
	}

	protected function getDirtyFields()
	{
		return array_keys($this->values);
	}

	protected function getModelProperties()
	{
		$class = get_class($this);
		return $class::$_properties;
	}

	protected function checkPropertyName($name)
	{
		if (!array_key_exists($name, $this->fields)) {
			$class = get_class($this);
			throw new \Exception("Invalid attribute $name for model $class");
		}
	}
}
