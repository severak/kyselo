<?php
namespace severak\database;
use severak\database\usageException;

// parametric query - value object
class query
{
	public $sql = '';
	public $params = [];

	public function __construct($sql, $params=[])
	{
		if (count($params) != substr_count($sql, '?')) {
			throw new usageException('Bad number of query params.');
		}
		
		$this->sql = $sql;
		$this->params = $params;
	}
	
	public function add($other, $params=[])
	{
		if (is_string($other)) {
			if (count($params) != substr_count($other, '?')) {
				throw new usageException('Bad number of new query params.');
			}
			return new query(
				$this->sql . ' ' . $other,
				array_merge($this->params, $params)
			);
		} elseif (is_object($other) && $other instanceof query) {
			return new query(
				$this->sql . ' '. $other->sql,
				array_merge($this->params, $other->params)
			);
		}
		throw new usageException('Bad parameters!');
	}
	
	public function interpolate()
	{
		$pdo = new \PDO('sqlite::memory:');
		$sql = $this->sql;
		foreach ($this->params as $param) {
			if (is_null($param)) {
				$param = 'NULL';
			} elseif (is_numeric($param)) {
				$param = sprintf('%d', $param);
			} elseif (is_string($param)) {
				$param = $pdo->quote($param);
			} else {
				// todo: co v tomto případě?
			}
			$sql = preg_replace('~\?~', $param, $sql, 1);
		}
		$pdo = null; // let's GC destroy that
		return $sql;
	}

	function __toString()
	{
		return $this->interpolate();
	}
}