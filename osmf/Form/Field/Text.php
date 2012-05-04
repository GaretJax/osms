<?php namespace osmf\Form\Field;


class Text extends \osmf\Form\Field
{
	
	public function render($value=NULL, $template=NULL)
	{
		if ($value === NULL) {
			$value = \array_get($this->args, 'default', NULL);
		}

		$tpl = new \osmf\Template('forms/fields/textarea.html');
		return $tpl->render(array(
			'label' => $this->label,
			'ref' => $this->ref,
			'value' => $value,
		));
	}
}

