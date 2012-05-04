<?php namespace osmf\Form\Field;


class CsrfTokenField extends \osmf\Form\Field
{
	public function render($value=NULL, $template=NULL)
	{
		$tpl = new \osmf\Template('forms/fields/csrf_token.html');
		return $tpl->render();
	}
}
