<?php namespace osmf\View;


class Transaction extends \osmf\View
{

	private $db_error = FALSE;

	protected function getProtectedDbconfs()
	{
		return array('default');
	}

	protected function setDbError()
	{
		$this->db_error = TRUE;
	}

	public function preRender()
	{
		foreach ($this->getProtectedDbconfs() as $dbconf) {
			$dbh = \osmf\Database\Driver::getInstance($dbconf);
			$dbh->beginTransaction();
		}
	}

	public function postRender()
	{
		foreach ($this->getProtectedDbconfs() as $dbconf) {
			$dbh = \osmf\Database\Driver::getInstance($dbconf);

			if ($this->db_error) {
				$dbh->rollback();
			} else {
				$dbh->commit();
			}
		}
	}
}
