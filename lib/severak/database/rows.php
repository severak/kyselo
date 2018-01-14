<?php
namespace severak\database;
use severak\database\usageException;
use severak\database\query;
use \PDO;

// see https://phpdelusions.net/pdo

class rows
{
	protected $_with = [];
	
	public $pdo;
	public $pages = -1;
	public $log = [];
	
	public function __construct(PDO $pdo)
	{
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo = $pdo;
	}

	public function one($table, $where=[], $order=[])
	{
		$Q = $this->fragment('SELECT '.$this->_what($table).' FROM ' . $table);
		$Q = $this->_addJoins($Q, $table);
		$Q = $this->_addWhere($Q, $where, $table);
		$Q = $this->_addOrder($Q, $order);
		$Q = $this->_addLimit($Q, 1);
		$this->_reset();
		return $this->_execute($Q)->fetch(PDO::FETCH_ASSOC);
	}
	
	public function more($table, $where=[], $order=[], $limit=30)
	{
		$Q = $this->fragment('SELECT '.$this->_what($table).' FROM ' . $table);
		$Q = $this->_addJoins($Q, $table);
		$Q = $this->_addWhere($Q, $where, $table);
		$Q = $this->_addOrder($Q, $order);
		$Q = $this->_addLimit($Q, $limit);
		$this->_reset();
		return $this->_execute($Q)->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function count($table, $where=[])
	{
		$Q = $this->fragment('SELECT count(*) FROM ' . $table);
		$Q = $this->_addJoins($Q, $table);
		$Q = $this->_addWhere($Q, $where, $table);
		$this->_reset();
		return (int) $this->_execute($Q)->fetchColumn();
	}
	
	public function page($table, $where=[], $order=[], $page=1, $perPage=30)
	{
		$Q = $this->fragment('SELECT count(*) FROM ' . $table);
		$Q = $this->_addJoins($Q, $table);
		$Q = $this->_addWhere($Q, $where, $table);
		$count = $this->_execute($Q)->fetchColumn();
		
		$Q = $this->fragment('SELECT '.$this->_what($table).' FROM ' . $table);
		$Q = $this->_addJoins($Q, $table);
		$Q = $this->_addWhere($Q, $where, $table);
		$Q = $this->_addOrder($Q, $order);
		$Q = $this->_addLimit($Q, $perPage);
		$Q = $this->_addOffset($Q, $perPage * ($page-1));
		
		$this->_reset();
		$this->pages = ceil($count/$perPage);
		return $this->_execute($Q)->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function with($table, $from='id', $to='id', $where=[])
	{
		$this->_with[] = ['table'=>$table, 'from'=>$from, 'to'=>$to, 'where'=>$where, 'inner'=>true];
		return $this;
	}
	
	public function insert($table, $data)
	{
		if (!empty($this->_with)) throw new usageException('Method rows::insert doesn\'t work with JOINs.');
		$questions = array_fill(0, count($data), '?');
		$Q = $this->fragment('INSERT INTO ' . $table . '(' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', $questions) . ')', array_values($data));
		$this->_execute($Q);
		$this->_reset();
		return $this->pdo->lastInsertId();
	}
	
	public function update($table, $data, $where)
	{
		if (!empty($this->_with)) throw new usageException('Method rows::update doesn\'t work with JOINs.');
		if (empty($where)) throw new usageException('Method rows::update with empty WHERE is insecure.');
		
		$Q = $this->fragment('UPDATE ' . $table . ' SET');
		$and = '';
		foreach ($data as $k=>$v) {
			$Q = $Q->add($and . $k.'=?', [$v]);
			$and = ', ';
		}
		$Q = $this->_addWhere($Q, $where, $table);
		
		$this->_reset();
		
		return $this->_execute($Q)->rowCount();
	}
	
	public function delete($table, $where)
	{
		if (!empty($this->_with)) throw new usageException('Method rows::delete doesn\'t work with JOINs.');
		if (empty($where)) throw new usageException('Method rows::delete with empty WHERE is insecure.');
		
		$Q = $this->fragment('DELETE FROM ' . $table);
		$Q = $this->_addWhere($Q, $where, $table);
		
		$this->_reset();
		
		return $this->_execute($Q)->rowCount();
	}
	
	public function fragment($sql, $params=[])
	{
		return new query($sql, $params);
	}
	
	public function query($sql, $params)
	{
		if (func_num_args()>1 && !is_array($params)) {
			$args = func_get_args();
			array_shift($args);
			$params = $args;
		}
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
	
	protected function _addOffset($Q, $offset)
	{
		return $Q->add('OFFSET ' . sprintf('%d', $offset));
	}
	
	protected function _execute(query $Q)
	{
		return $this->execute($Q);
	}
	
	public function execute(query $Q)
	{
		$this->log[] = $Q;
		
		$stmt = $this->pdo->prepare($Q->sql);
		$stmt->execute($Q->params);
		
		return $stmt;
	}
	
	protected function _reset()
	{
		$this->_with = [];
		$this->pages = -1;
	}
}