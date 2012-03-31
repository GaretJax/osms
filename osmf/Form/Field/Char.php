<?php namespace osmf\Form\Field;


class Char extends \osmf\Form\Field
{
	protected $type = 'text';

	public function render($value=NULL)
	{
		$tpl = new \osmf\Template('forms/fields/input.html');
		return $tpl->render(array(
			'label' => $this->label,
			'ref' => $this->ref,
			'type' => $this->type,
			'value' => $value,
			'size' => \array_get($this->args, 'size', '25'),
			'placeholder' => \array_get($this->args, 'placeholder', ''),
		));
	}
}
