<?php namespace osmf\Form\Field;


class Password extends Char
{
	protected $type = 'password';

	public function render($value=NULL)
	{
		return parent::render();
	}
}
