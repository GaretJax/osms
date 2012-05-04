<?php namespace osmf\Form\Field;


class Boolean extends \osmf\Form\Field
{
	public function render($value=NULL, $template=NULL)
	{
		if ($value === NULL) {
			$value = \array_get($this->args, 'default', NULL);
		}

		$tpl = new \osmf\Template('forms/fields/checkbox.html');
		return $tpl->render(array(
			'label' => $this->label,
			'ref' => $this->ref,
			'value' => $value,
		));
	}

	public function clean($form, $value)
	{
		return intval($value) !== 0;
	}
}

