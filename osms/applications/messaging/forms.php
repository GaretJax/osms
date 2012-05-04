<?php namespace osms\messaging\forms;


$message = new \osmf\Form\Builder(__NAMESPACE__ . '\Message');
$message->addField('subject', 'Char', array(
	'label' => 'Subject',
	'maxlength' => 255,
	'minlength' => 3,
));
$message->addField('body', 'Text', array(
	'label' => 'Body',
	'required' => FALSE,
	'maxlength' => 50000,
	'minlength' => 10,
));
$message->addField('attachment', 'File', array(
	'label' => 'Attachment',
	'required' => FALSE,
	'allowed_types' => array(
		'text/plain' => array('txt'),
		'image/png' => array('png'),
		'image/jpeg' => array('jpg', 'jpeg'),
		'application/pdf' => array('pdf'),
		'application/msword' => array('doc'),
		'application/vnd.ms-office' => array('ppt'),
		'application/vnd.ms-excel' => array('xls'),
		'application/vnd.ms-powerpoint' => array('ppt'),
		'application/zip' => array('xlsx', 'docx', 'pptx'),
	)
));


$delete = new \osmf\Form\Builder(__NAMESPACE__ . '\Delete');
$delete->addField('token', 'Char', array(
	'label' => 'Deletion token',
	'maxlength' => 20,
	'minlength' => 5,
));
$delete->addField('action', 'Choice', array(
	'choices' => array(
		'delete-message' => 'delete-message',
		'cancel-request' => 'cancel-request',
	)
));
