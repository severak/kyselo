<?php
namespace severak\database;

class rows
{
	public $pdo;
	public $pageCount = -1;
	
	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function one($table, $where)
	{
		// todo
	}
	
	public function more($table, $where, $order)
	{
		// todo
	}
	
	public function count($table, $where)
	{
		// todo
	}
	
	public function page($table, $where, $order, $page, $perPage)
	{
		// todo
	}
	
	public function with($table, $from='id', $to='id', $where=[])
	{
		// todo
	}
	
	public function insert($table, $data)
	{
		// todo
	}
	
	public function update($table, $data, $where)
	{
		// todo
	}
	
	public function delete($table, $where)
	{
		// todo
	}
}