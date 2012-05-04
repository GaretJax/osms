<?php namespace osms\messaging\models;


$message = new \osmf\Model\Builder(__NAMESPACE__ . '\Message');
$message->addColumn('sender', 'ForeignKey', array(
	'type' => '\osms\auth\models\User',
));
$message->addColumn('recipient', 'ForeignKey', array(
	'type' => '\osms\auth\models\User',
));
$message->addColumn('subject', 'Char');
$message->addColumn('body', 'Char');
$message->addColumn('attachment', 'File');
$message->addColumn('timestamp', 'DateTime', array(
	'auto_add_now' => TRUE,
));
$message->addColumn('status', 'Enum', array(
	'choices' => array('unread', 'read', 'archived'),
	'default' => 'unread',
));


$token = new \osmf\Model\Builder(__NAMESPACE__ . '\TokenBase', $table='osms_messaging_token');
$token->addColumn('message', 'ForeignKey', array(
	'type' => '\osms\messaging\models\Message',
));
$token->addColumn('token', 'Char');
$token->addColumn('timestamp', 'DateTime', array(
	'auto_add_now' => TRUE,
));


class Token extends TokenBase
{
	public function getToken()
	{
		$token = $this->_getProperty('token');
		if (!$token) {
			$token = $this->generateToken();
			$this->_setProperty('token', $token);
		}

		return $token;
	}

	public function getFormattedToken()
	{
		$token = $this->token;
		return substr($token, 0, 2) . '-' . substr($token, 2, 4) . '-' . substr($token, 6, 2);
	}

	protected function generateToken()
	{
		$chars = 'ABCDEFGHJKMNPQRSTUVWXYZ123456789123456789';
		$token = str_split($chars);
		shuffle($token);
		$token = array_slice($token, 0, 8);
		$token = implode('', $token);

		return $token;
	}
}
