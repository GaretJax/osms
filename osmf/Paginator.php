<?php namespace osmf;


class Paginator
{
	protected $query;
	protected $count;
	protected $items;

	public function __construct($query, $count)
	{
		$this->query = $query;
		$this->count = $count;
		$this->items = $query->count();
	}

	public function getPage($page)
	{
		$page = max(intval($page), 1);
		$start = $this->count * ($page - 1);
		return $this->query
			->limit($start, $this->count)
			->all();
	}

	public function getPageCount()
	{
		return ceil($this->items / $this->count);
	}
}
