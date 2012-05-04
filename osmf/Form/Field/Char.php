<?php namespace osmf\Form\Field;


class Char extends \osmf\Form\Field
{
	protected $type = 'text';

	public function render($value=NULL, $template=NULL)
	{
		if ($value === NULL) {
			$value = \array_get($this->args, 'default', NULL);
		}

		$tpl = new \osmf\Template('forms/fields/input.html');
		return $tpl->render(array(
			'label' => $this->label,
			'ref' => $this->ref,
			'type' => $this->type,
			'value' => $value,
			'size' => \array_get($this->args, 'size', '25'),
			'disabled' => \array_get($this->args, 'disabled', FALSE),
			'placeholder' => \array_get($this->args, 'placeholder', ''),
		));
	}
}
