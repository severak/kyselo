<?php
namespace severak\database;
use usageException;
use severak\database\query;
use \PDO;

// see https://phpdelusions.net/pdo

class rows
{
	protected $_with = [];
	
	public $pdo;
	public $pageCount = -1;
	public $log = [];
	
	public function __construct(PDO $pdo)
	{
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo = $pdo;
	}

	public function one($table, $where=[])
	{
		$Q = $this->fragment('SELECT '.$this->_what($table).' FROM ' . $table);
		$Q = $this->_addJoins($Q, $table);
		$Q = $this->_addWhere($Q, $where, $table);
		return $this->_execute($Q)->fetch(PDO::FETCH_ASSOC);
	}
	
	public function more($table, $where=[], $order=[], $limit=30)
	{
		$Q = $this->fragment('SELECT '.$this->_what($table).' FROM ' . $table);
		$Q = $this->_addJoins($Q, $table);
		$Q = $this->_addWhere($Q, $where, $table);
		$Q = $this->_addOrder($Q, $order);
		$Q = $this->_addLimit($Q, $limit);
		return $this->_execute($Q)->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function count($table, $where=[])
	{
		$Q = $this->fragment('SELECT count(*) FROM ' . $table);
		$Q = $this->_addJoins($Q, $table);
		$Q = $this->_addWhere($Q, $where, $table);
		return (int) $this->_execute($Q)->fetchColumn();
	}
	
	public function page($table, $where=[], $order=[], $page=0, $perPage=30)
	{
		// todo
	}
	
	public function with($table, $from='id', $to='id', $where=[])
	{
		$this->_with[] = ['table'=>$table, 'from'=>$from, 'to'=>$to, 'where'=>$where, 'inner'=>true];
		return $this;
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
		return new query($sql, $params);
	}
	
	protected function _addJoins(query $Q, $table)
	{
		if (empty($this->_with)) {
			return $Q;
		}
		
		foreach ($this->_with as $with) {
			$Q = $Q->add('INNER JOIN ' . $with['table'] . ' ON ' . $table . '.' . $with['from'] . '=' . $with['table'] . '.' . $with['to']);
			if (!empty($with['where'])) {
				$Q = $Q->add('AND')->add($this->_where($with['where'], $with['table']));
			}
		}
		return $Q;
	}
	
	protected function _what($table) {
		if (empty($this->_with)) {
			return '*';
		}
		$joined = [];
		foreach ($this->_with as $with) {
			$joined[] = $with['table'] . '.*';
		}
		return implode(', ', $joined) . ', ' . $table . '.*';
		
	}
	
	protected function _addWhere(query $Q, $where, $table)
	{
		if (empty($where)) {
			return $Q;
		}
		return $Q->add('WHERE')->add($this->_where($where, $table));
	}
	
	protected function _where($where, $table)
	{
		if (is_numeric($where)) {
			return $this->fragment($table.'.id=?', [$where]);
		}
		if (is_object($where) and $where instanceof query) {
			return $where;
		}
		
		$Q = $this->fragment('');
		$and = '';
		foreach ($where as $k=>$v) {
			if (is_object($v) and $v instanceof query) {
				$Q = $Q->add($and)->add($v);
			} elseif (is_array($v)) {
				$questions = array_fill(0, count($v), '?');
				$Q = $Q->add($and . $table.'.'.$k.' IN (' . implode(', ', $questions)  . ')', $v);
			} elseif (is_null($v)) {
				$Q = $Q->add($and . $table.'.'.$k . ' IS NULL');
			} else {
				$Q = $Q->add($and . $table.'.'.$k . '=?', [$v]);
			}
			$and = 'AND ';
		}
		return $Q;
	}
	
	protected function _addOrder($Q, $order)
	{
		if (empty($order)) {
			return $Q;
		}
		$Q = $Q->add('ORDER BY');
		$and = '';
		foreach ($order as $k=>$v) {
			$Q = $Q->add($and . $k  . ' ' .  (strtoupper($v)=='ASC' ? 'ASC' : 'DESC'));
			$and = ', ';
		}
		return $Q;
	}
	
	protected function _addLimit($Q, $limit)
	{
		return $Q->add('LIMIT ' . sprintf('%d', $limit));
	}
	
	protected function _execute(query $Q)
	{
		$this->log[] = $Q->interpolate();
		
		$stmt = $this->pdo->prepare($Q->sql);
		$stmt->execute($Q->params);
		
		$this->_with = [];
		
		return $stmt;
	}
}