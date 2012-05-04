<?php namespace osmf\Model;

class Query
{
	protected $predicates = array(
		'eq', 'neq', 'gt', 'lt', 'lte', 'gte', 'ieq', 'ineq'
	);

	protected $where = NULL;
	protected $ordering = array();
	protected $limit = NULL;
	protected $values = array();

	protected $dbconf;
	protected $model;
	protected $columnValidator;
	protected $operatorValidator;

	public function __construct($model, $dbconf=NULL)
	{
		$this->model = $model;
		$this->dbconf = $dbconf;
		$this->columnValidator = new \osmf\Validator\SqlColumn();
		$this->operatorValidator = new \osmf\Validator\SqlOperator();
	}

	public function __call($name, $args)
	{
		if (!in_array($name , array('or', 'and'))) {
			throw new \Exception("Invalid method $name");
		}

		return call_user_func_array(array($this, $name . '_'), $args);
	}

	public function where($column, $operator, $value)
	{
		$this->columnValidator->assertValid($column);
		$this->operatorValidator->assertValid($operator);

		$clause = array($column, $operator, $value);
		$this->where = array(array($clause));

		return $this;
	}

	public function and_($column, $operator, $value)
	{
		if ($this->where === NULL) {
			throw new \Exception('You have to call WHERE prior to and');
		}

		$this->columnValidator->assertValid($column);
		$this->operatorValidator->assertValid($operator);

		$clause = array($column, $operator, $value);

		end($this->where);
		$this->where[key($this->where)][] = $clause;

		return $this;
	}

	public function or_($column, $operator, $value)
	{
		if ($this->where === NULL) {
			throw new \Exception('You have to call WHERE prior to or');
		}

		$this->columnValidator->assertValid($column);
		$this->operatorValidator->assertValid($operator);

		$clause = array($column, $operator, $value);

		$this->where[] = array($clause);

		return $this;
	}

	public function orderBy()
	{
		foreach (func_get_args() as $column) {
			if (startswith($column, '+')) {
				$direction = 'ASC';
				$column = substr($column, 1);
			} elseif(startswith($column, '-')) {
				$direction = 'DESC';
				$column = substr($column, 1);
			} else {
				$direction = 'ASC';
			}
			
			$this->columnValidator->assertValid($column);
			$this->ordering[] = array($column, $direction);
		}

		return $this;
	}

	public function all()
	{
		$query = $this->createQuery();
		$dbh = \osmf\Database\Driver::getInstance($this->dbconf);
		$stmt = $dbh->prepareStatement($query['sql']);
		$model = $this->model;
		return $model::executeStatement($stmt, $query['values'], $this->dbconf);
	}

	public function count()
	{
		$query = $this->createCountQuery();
		$dbh = \osmf\Database\Driver::getInstance($this->dbconf);
		$stmt = $dbh->prepareStatement($query['sql']);
		$model = $this->model;
		$model::executeRawStatement($stmt, $query['values'], $this->dbconf);

		$result = $stmt->fetch(\PDO::FETCH_ASSOC);

		return $result['count'];
	}

	public function one()
	{
		$models = $this->limit(0, 2)->all();

		if (count($models) === 0) {
			throw new ObjectNotFound('Object not found');
		} elseif (count($models) > 1) {
			throw new \Exception('Multiple results returned');
		}

		return $models[0];
	}

	public function limit($start, $count=NULL)
	{
		if ($count === NULL) {
			$count = $start;
			$start = 0;
		}

		$this->limit = array($start, $count);

		return $this;
	}

	public function createCountQuery()
	{
		$model = $this->model;
		$table = $model::getTableName();
		$values = array();

		$sql = sprintf('SELECT COUNT(*) as count FROM %s', $table);

		if ($this->where) {
			$groups = array();
			$this->values = array();

			foreach ($this->where as $group) {
				$group = array_map(array($this, 'createClause'), $group);
				$groups[] = '( ' . implode(' AND ', $group) . ' )';
			}

			$values = $this->values;
			$this->values = array();

			$where = implode(' OR ', $groups);
			$sql .= sprintf(' WHERE %s', $where);
		}

		return array(
			'sql' => $sql,
			'values' => $values
		);

	}

	public function createQuery()
	{
		$model = $this->model;
		$table = $model::getTableName();
		$fields = $model::getFieldNames();
		$fields = implode(', ', $fields);
		$values = array();

		$sql = sprintf('SELECT %s FROM %s', $fields, $table);

		if ($this->where) {
			$groups = array();
			$this->values = array();

			foreach ($this->where as $group) {
				$group = array_map(array($this, 'createClause'), $group);
				$groups[] = '( ' . implode(' AND ', $group) . ' )';
			}

			$values = $this->values;
			$this->values = array();

			$where = implode(' OR ', $groups);
			$sql .= sprintf(' WHERE %s', $where);
		}

		if ($this->ordering) {
			$cb = create_function('$ar', 'return implode(" ", $ar);');
			$plain = array_map($cb, $this->ordering);
			$order = implode(', ', $plain);
			$sql .= sprintf(' ORDER BY %s', $order);
		}

		if ($this->limit) {
			// TODO
			// MySQL
			//$sql .= sprintf(' LIMIT %d, %d', $this->limit[0], $this->limit[1]);
			
			// Postgres
			$sql .= sprintf(' LIMIT %d OFFSET %d', $this->limit[1], $this->limit[0]);
		}

		return array(
			'sql' => $sql,
			'values' => $values
		);
	}

	protected function createClause($group)
	{
		$column = $group[0];
		$predicate = $group[1];
		$value = $group[2];

		if (!in_array($predicate, $this->predicates)) {
			throw new \Exception('Invalid predicate');
		}

		$i = 0;

		do {
			$placeholder = ':' . $column . '__' . $i;
			$i++;
		} while (array_key_exists($placeholder, $this->values));

		$this->values[$placeholder] = array($column, $value);

		$function = 'predicate_' . strtoupper($predicate);
		return $this->$function($column, $placeholder);
	}

	protected function predicate_NEQ($lhs, $rhs)
	{
		return sprintf('%s != %s', $lhs, $rhs);
	}

	protected function predicate_EQ($lhs, $rhs)
	{
		return sprintf('%s = %s', $lhs, $rhs);
	}
}
