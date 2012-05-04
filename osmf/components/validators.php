<?php namespace osmf\Validator;


class ValidationError extends \Exception
{
}


abstract class Validator
{
	protected abstract function check($value);

	protected function getMessage()
	{
		return 'Invalid value';
	}

	public function assertValid($value)
	{
		$res = $this->check($value);
		if (!is_bool($res)) {
			throw new \Exception('Unexpected return type');
		}

		if (!$res) {
			throw new ValidationError($this->getMessage());
		}
	}
}

class Composite extends Validator
{
	protected $validators;

	public function __construct($validators=array())
	{
		$this->validators = $validators;
	}

	protected function check($value)
	{}

	public function assertValid($value)
	{
		foreach ($this->validators as $validator) {
			$validator->assertValid($value);
		}
	}
}


class PasswordComplexity extends Validator
{
	public function getMessage()
	{
		return 'This value shall contain characters from at least two of the following groups: lowercase letters, uppercase letters, numbers and special characters';
	}

	public function check($value)
	{
		$groups = array(
			'%[a-z]%',        // Lowercase letters
			'%[A-Z]%',        // Uppercase letters
			'%[0-9]%',        // Numbers
			'%[^a-zA-Z0-9]%'  // Special chars
		);

		$hits = 0;

		foreach ($groups as $group) {
			if (preg_match($group, $value)) {
				$hits++;
			}
		}

		return $hits >= 2;
	}
}


class Length extends Validator
{
	protected $min;
	protected $max;

	public function __construct($min=0, $max=PHP_INT_MAX)
	{
		$this->min = $min;
		$this->max = $max;
	}

	protected function getMessage()
	{
		if ($this->max === PHP_INT_MAX) {
			return sprintf('Value shall be at least %d characters long', $this->min);
		}

		return sprintf('Value shall be between %d and %d characters of length',
			$this->min, $this->max);
	}

	public function check($value)
	{
		$length = strlen($value);

		return $length >= $this->min and $length <= $this->max;
	}
}


class Regex extends Validator
{
	protected $regex;

	public function __construct($regex, $delimiter='%')
	{
		$this->regex = $delimiter . '^' . $regex . '$' . $delimiter;
	}

	protected function check($value)
	{
		return preg_match($this->regex, $value) === 1;
	}
}


class Username extends Regex
{
	public function __construct()
	{
		parent::__construct('[a-zA-Z][a-zA-Z0-9]+');
	}

	protected function getMessage()
	{
		return 'A username can contain only alphanumeric characters and has to start with a letter';
	}
}


class SqlColumn extends Regex
{
	public function __construct()
	{
		parent::__construct('[a-zA-Z_][a-zA-Z0-9_]+');
	}

	protected function getMessage()
	{
		return 'Invalid column name';
	}
}


class Choice extends Validator
{
	protected $choices;

	public function __construct($choices)
	{
		$this->choices = $choices;
	}

	protected function check($value)
	{
		return in_array($value, $this->choices, TRUE);
	}

	protected function getMessage()
	{
		return 'Invalid choice';
	}
}


class SqlOperator extends Choice
{
	public function __construct()
	{
		parent::__construct(array(
			'eq', 'neq', 'ieq', 'ineq',
			'gt', 'lt', 'lte', 'gte',
		));
	}
}
