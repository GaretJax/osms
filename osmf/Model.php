<?php namespace osmf;


abstract class Model
{
	protected $fields;
	protected $values = array();
	protected $loadedValues = array();
	protected $phpValues = array();
	protected $table;
	protected $dbh;
	protected $dbconf;

	public function __construct($dbconf=NULL, $init=TRUE)
	{
		$this->dbconf = $dbconf;
		$this->fields = static::$properties['fields'];
		$this->table = static::$properties['name'];
		$this->dbh = \osmf\Database\Driver::getInstance($dbconf);

		if ($init) {
			foreach ($this->fields as $k => $key) {
				if ($k === 'id') {
					continue;
				}
				$this->$k = $this->$k;
			}
		}
	}

	protected function _getProperty($name)
	{
		$this->checkPropertyName($name);
		
		if (array_key_exists($name, $this->values)) {
			// Value was modified by user
			return $this->values[$name];
		} elseif (array_key_exists($name, $this->phpValues)) {
			// Values was already converted
			return $this->phpValues[$name];
		} else {
			// First time value is accessed, convert to php
			$val = \array_get($this->loadedValues, $name, NULL);
			$val = $this->fields[$name]->toPhp($val, $this->dbconf);
			$this->phpValues[$name] = $val;
			return $val;
		}
	}

	public function __get($name)
	{
		$getter = 'get' . ucfirst($name);
		if (method_exists($this, $getter)) {
			return $this->$getter($name);
		} else {
			return $this->_getProperty($name);
		}
	}

	protected function _setProperty($name, $value)
	{
		$this->checkPropertyName($name);
		$this->values[$name] = $value;
	}

	public function __set($name, $value)
	{
		$setter = 'set' . ucfirst($name);
		if (method_exists($this, $setter)) {
			$this->$setter($name, $value);
		} else {
			$this->_setProperty($name, $value);
		}
	}

	public static function query($dbconf=NULL)
	{
		$name = get_called_class();
		return new \osmf\Model\Query($name, $dbconf);
	}

	public static function executeRawStatement($stmt, $values, $dbconf)
	{
		$properties = static::$properties;
		$fields = $properties['fields'];

		foreach ($values as $key => $val) {
			$column = $val[0];
			$val = $val[1];
			$values[$key] = $fields[$column]->toDb($val);
		}

		//$stmt->debugDumpParams();
		$stmt->execute($values);

		return $stmt;
	}

	public static function executeStatement($stmt, $values, $dbconf)
	{
		static::executeRawStatement($stmt, $values, $dbconf);
		$instances = array();

		foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $dataobj) {
			$instance = new static($dbconf, FALSE);
			$instance->loadedValues = $dataobj;
			$instances[] = $instance;
		}

		return $instances;
	}

	public function save()
	{
		
		
		$fields = $this->getDirtyFields();

		$values = array();
		foreach ($fields as $k => $key) {
			// Get modified value
			$val = \array_get($this->values, $key, NULL);
			// Convert to DB repr
			$val = $this->fields[$key]->toDb($val);
			// Store for query execution
			$values[':' . $key] = $val;
		}

		if ($this->id) {
			$stmt = $this->dbh->prepareUpdateStatement($this->table, array('id'), $fields);
			$values['id'] = $this->id;
		} else {
			$stmt = $this->dbh->prepareInsertStatement($this->table, $fields);
		}

		//$stmt->debugDumpParams();
		$stmt->execute($values);

		// Retrieve the ID
		if (!$this->id) {
			$this->id = $this->dbh->lastInsertId($this->table);
		}
	}

	public function delete()
	{
		$stmt = $this->dbh->prepareDeleteStatement($this->table, array('id'));
		$stmt->execute(array('id' => $this->id));
	}

	public static function getTableName()
	{
		return static::$properties['name'];
	}

	public static function getFieldNames()
	{
		return array_keys(static::$properties['fields']);
	}

	public static function getById($id, $dbconf=NULL)
	{
		return static::query($dbconf)->where('id', 'eq', $id)->one();
	}

	public function equals($other)
	{
		return get_class($this) == get_class($other) and $this->id === $other->id;
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
