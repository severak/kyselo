<?php
class brick_saxofon
{
	protected $_openCallbacks = array();
	protected $_closeCallbacks = array();
	protected $_textCallbacks = array();
	protected $_reader = array();

	function registerOpen($name, $callback)
	{
		$this->_openCallbacks[$name] = $callback;
	}
	
	function registerText($name, $callback)
	{
		$this->_textCallbacks[$name] = $callback;
	}
	
	function registerClose($name, $callback)
	{
		$this->_closeCallbacks[$name] = $callback;
	}

	function readFile($name)
	{
		$reader = new XMLReader();
		$reader->open($name);
		$stack = array();
		while ($reader->read()) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				if (!$reader->isEmptyElement) {
					array_push($stack, $reader->name);
					$n = join('/', $stack);
					if (isset($this->_openCallbacks[$n])) {
						call_user_func($this->_openCallbacks[$n]);
					}
				}
			} elseif ($reader->nodeType == XMLReader::TEXT) {
				$n = join('/', $stack);
				if (isset($this->_textCallbacks[$n])) {
					call_user_func($this->_textCallbacks[$n], $reader->value);
				}
			} elseif ($reader->nodeType == XMLReader::END_ELEMENT) {
				$n = join('/', $stack);
				if (isset($this->_closeCallbacks[$n])) {
					call_user_func($this->_closeCallbacks[$n]);
				}
				array_pop($stack);
			}
		}
	}
}