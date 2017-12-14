<?php
namespace severak\database;

// see https://phpdelusions.net/pdo

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
		$Q = $this->fragment('SELECT * FROM ' . $table);
		$Q = $this->_addJoins($Q);
		$Q = $this->_addWhere($Q, $where);
		return $this->_execute($Q)->fetch(PDO::FETCH_ASSOC);
	}
	
	public function more($table, $where, $order)
	{
		$Q = $this->fragment('SELECT * FROM ' . $table);
		$Q = $this->_addJoins($Q);
		$Q = $this->_addWhere($Q, $where);
		$Q = $this->_addOrder($Q, $order);
		return $this->_execute($Q)->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function count($table, $where)
	{
		$Q = $this->fragment('SELECT count(*) FROM ' . $table);
		$Q = $this->_addJoins($Q);
		$Q = $this->_addWhere($Q, $where);
		return $this->_execute($Q)->fetchColumn();
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
	
	public function fragment($sql, $params=[])
	{
		// todo
	}
	
	
}