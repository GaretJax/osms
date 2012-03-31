<?php namespace osmf\Form;


class Builder
{
	protected $fields = array();

	public function add($name, $fieldClass, $args=array())
	{
		$fieldClass = __NAMESPACE__ . '\Field\\' . $fieldClass;

		$field = new $fieldClass($name, $args);
		$this->fields[$name] = $field;

		return $this;
	}

	public function getForm($data=array())
	{
		return new \osmf\Form($this->fields, $data);
	}
}
