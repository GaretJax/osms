<?php namespace osmf\Form\Field;


class Captcha extends \osmf\Form\Field
{
	public function render($value=NULL, $context=NULL)
	{
		$tpl = new \osmf\Template('forms/fields/captcha.html');
		return $tpl->render(array(
			'context' => $context,
			'label' => $this->label,
			'ref' => $this->ref,
			'size' => \array_get($this->args, 'size', '25'),
		));
	}

	public function clean($form, $value)
	{
		if ($value !== $form->getSession()->get('captcha')) {
			throw new \osmf\Validator\ValidationError('The entered character sequence does not correspond to the displayed one');
		}
	}
}
