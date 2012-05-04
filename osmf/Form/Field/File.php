<?php namespace osmf\Form\Field;

define("UPLOAD_ERR_EMPTY", 5);

class File extends \osmf\Form\Field
{
	protected $upload_errors = array(
		UPLOAD_ERR_INI_SIZE   => "File too large.",
		UPLOAD_ERR_FORM_SIZE  => "File too large.",
		UPLOAD_ERR_PARTIAL    => "Upload only partially completed.",
		UPLOAD_ERR_NO_FILE    => "No file was uploaded.",
		UPLOAD_ERR_NO_TMP_DIR => "An error occurred while saving the uploaded file.", //"No temporary directory.",
		UPLOAD_ERR_CANT_WRITE => "An error occurred while saving the uploaded file.", //"Can't write to disk.",
		UPLOAD_ERR_EXTENSION  => "An error occurred while saving the uploaded file.", //"File upload stopped by extension.",
		UPLOAD_ERR_EMPTY      => "No file was uploaded."
	);

	public function render($value=NULL, $template=NULL)
	{
		$tpl = new \osmf\Template('forms/fields/file.html');
		return $tpl->render(array(
			'label' => $this->label,
			'ref' => $this->ref,
			'size' => \array_get($this->args, 'size', '25'),
		));
	}

	public function clean($form, $value)
	{
		if ($this->required && ($value === NULL || count($value) === 0)) {
			throw new \osmf\Validator\ValidationError('This field is required');
		}

		if (!$value) {
			// Not required and not set
			return NULL;
		}

		if($value['size'] == 0){
			if ($this->required) {
				$value['error'] = 5;
			} else {
				return NULL;
			}
		}

		if ($value['error'] !== UPLOAD_ERR_OK) {
			throw new \osmf\Validator\ValidationError($this->upload_errors[$value['error']]);
		}

		$chunks = explode('.', $value['name']);
		$ext = $chunks[count($chunks) - 1];
		
		$file = new \osmf\File($value['tmp_name']);
		$type = $file->getType();
		
		$allowed = \array_get($this->args, 'allowed_types', array());
		if (!array_key_exists($type, $allowed)) {
			throw new \osmf\Validator\ValidationError("Invalid file type '$type'");
		}

		if (!in_array($ext, $allowed[$type])) {
			throw new \osmf\Validator\ValidationError("Invalid file extension '$ext'");
		}

		return array(
			'name' => $value['name'],
			'type' => $value['type'],
			'file' => $file,
		);
	}
}
