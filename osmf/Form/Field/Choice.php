<?php namespace osmf\Form\Field;


class Choice extends \osmf\Form\Field
{
	protected function getChoices()
	{
		$choices = $this->args['choices'];
		
		asort($choices);

		if (!$this->required) {
			$choices = array('' => '-- No value --') + $choices;
		}
		return $choices;
	}

	public function render($value=NULL, $template=NULL)
	{
		if ($value === NULL) {
			$value = \array_get($this->args, 'default', NULL);
		}

		$value = intval($value);

		$tpl = new \osmf\Template('forms/fields/choice.html');
		return $tpl->render(array(
			'label' => $this->label,
			'ref' => $this->ref,
			'choices' => $this->getChoices(),
			'value' => $value,
		));
	}

	public function clean($form, $value)
	{
		$choices = $this->getChoices();

		if (!array_key_exists($value, $choices)) {
			throw new \osmf\Validator\ValidationError('Invalid choice');
		}

		return $choices[$value];
	}
}
