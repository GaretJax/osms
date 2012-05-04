<?php namespace osmf\Form\Field;


class Password extends Char
{
	protected $type = 'password';

	public function render($value=NULL, $template=NULL)
	{
		return parent::render();
	}
}
