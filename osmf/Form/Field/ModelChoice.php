<?php namespace osmf\Form\Field;


class ModelChoice extends \osmf\Form\Field\Choice
{
	protected function getChoices()
	{
		$model = $this->args['model'];
		$dbconf = \array_get($this->args, 'dbconf', NULL);

		$query = $model::query($dbconf);
		$filters = \array_get($this->args, 'query', array());

		if ($filters) {
			$filter = array_shift($filters);

			$query->where($filter[0], $filter[1], $filter[2]);

			foreach ($filters as $filter) {
				$query = $query->and($filter[0], $filter[1], $filter[2]);
			}
		}

		$data = $query->all();

		$choices = array();

		$attribute = $this->args['attribute'];

		foreach ($data as $object) {
			$choices[$object->id] = $object->$attribute;
		}

		asort($choices);

		if (!$this->required) {
			$choices = array('' => '-- No value --') + $choices;
		}

		return $choices;
	}

	public function clean($form, $value)
	{
		if (($value === "" or $value === NULL) and !$this->required) {
			return NULL;
		}

		$choices = $this->getChoices();
		$value = intval($value);

		if (!array_key_exists($value, $choices)) {
			throw new \osmf\Validator\ValidationError('Invalid choice');
		}

		$model = $this->args['model'];
		$dbconf = \array_get($this->args, 'dbconf', NULL);

		return $model::getById($value, $dbconf);
	}
}

