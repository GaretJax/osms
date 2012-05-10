<?php namespace osmf\Database;


class Driver
{
	protected static $instances = array();
	protected $type;
	protected $dbh;

	protected function __construct($type, $host, $name, $user, $pass)
	{
		$this->type = $type;
		$connstring = sprintf('%s:host=%s;dbname=%s', $type, $host, $name);
		$this->dbh = new \PDO($connstring, $user, $pass, array(
			\PDO::ATTR_PERSISTENT => true,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		));
	}

	public static function getInstance($confname=NULL)
	{
		$config = \osmf\Config::getInstance();

		if ($confname === NULL) {
			reset($config->databases);
			$confname = key($config->databases);
		}

		if (!array_key_exists($confname, Driver::$instances)) {
			$conf = $config->databases[$confname];
			Driver::$instances[$confname] = new Driver(
				$conf['type'],
				$conf['host'],
				$conf['name'],
				$conf['user'],
				$conf['pass']
			);
		}

		return Driver::$instances[$confname];
	}

	public function getDatabaseType()
	{
		return $this->type;
	}

	public function beginTransaction()
	{
		return $this->dbh->beginTransaction();
	}

	public function commit()
	{
		return $this->dbh->commit();
	}

	public function rollback()
	{
		return $this->dbh->rollback();
	}


	public function prepareStatement($sql)
	{
		return $this->dbh->prepare($sql);
	}

	public function prepareInsertStatement($table, $fields)
	{
		$values = array_map(
			create_function('$val', 'return sprintf(":%s", $val);'),
			$fields
		);

		$fields = implode(', ', $fields);
		$values = implode(', ', $values);

		$sql = sprintf('INSERT INTO %s ( %s ) VALUES ( %s )', $table, $fields, $values);
		return $this->dbh->prepare($sql);
	}

	public function prepareUpdateStatement($table, $query, $fields)
	{
		$fields = array_map(
			create_function('$val', 'return sprintf("%s=:%s", $val, $val);'),
			$fields
		);

		$query = array_map(
			create_function('$val', 'return sprintf("%s=:%s", $val, $val);'),
			$query
		);

		$query = implode(' AND ', $query);
		$fields = implode(', ', $fields);

		$sql = sprintf('UPDATE %s SET %s WHERE %s', $table, $fields, $query);
		return $this->dbh->prepare($sql);
	}

	public function prepareDeleteStatement($table, $query)
	{
		$query = array_map(
			create_function('$val', 'return sprintf("%s=:%s", $val, $val);'),
			$query
		);

		$query = implode(' AND ', $query);

		$sql = sprintf('DELETE FROM %s WHERE %s', $table, $query);
		return $this->dbh->prepare($sql);
	}

	public function lastInsertId($table, $field='id')
	{
		$seq = implode('_', array($table, $field, 'seq'));
		return intval($this->dbh->lastInsertId($seq));
	}
}
