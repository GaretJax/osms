<?php namespace osms\Form\Field;


class CsrfTokenField extends \osmf\Form\Field
{
	public function render()
	{
		$tpl = new \osmf\Template('forms/fields/csrf_token.html');
		return $tpl->render();

	}
}
