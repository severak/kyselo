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
		} elseif (is_object($other)) {
			return new query(
				$this->sql . ' '. $other->sql,
				array_merge($this->params, $other->params)
			);
		}
		throw new usageException('Bad parameters!');
	}
}