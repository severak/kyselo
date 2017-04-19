<?php
namespace severak;
use AdamWathan\Form\FormBuilder;
use AdamWathan\Form\OldInput\OldInputInterface;
use AdamWathan\Form\ErrorStore\ErrorStoreInterface;

// severak\form - extended variant of AdamWathan form class
class form
extends FormBuilder
implements OldInputInterface, ErrorStoreInterface
{
	public $values = [];
	public $errors = [];
	protected $_rules = [];
	
	function __construct()
	{
		$this->setOldInputProvider($this);
		$this->setErrorStore($this);
	}
	
	function hasOldInput()
	{
		return !empty($this->values);
	}
	
	function getOldInput($name)
	{
		return isset($this->values[$name]) ? $this->values[$name] : '';
	}
	
	function hasError($key)
	{
		return !empty($this->errors[$key]);
	}
	
	function getError($key, $format = null)
	{
		return empty($this->errors[$key]) ? '' : $this->errors[$key];
	}
	
	// add validation rule
	public function rule($name, $callback, $message)
	{
		$this->_rules[$name][] = ['check'=>$callback, 'message'=>$message];
	}
	
	// validate using rules
	public function validate()
	{
		$valid = true;
		foreach ($this->_rules as $name => $rules) {
			foreach ($rules as $rule) {
				$value = isset($this->values[$name]) ? $this->values[$name] : null;
				$passed = call_user_func_array($rule['check'], [$value, $this->values]);	
				if (empty($passed)) {
					$this->errors[$name] = $rule['message'];
					$valid = false;
					break;
				}
			}
		}
		return $valid;
	}
	
	// check if form is valid
	function isValid()
	{
		return empty($this->errors);
	}
	
	// display error message
	function showError($name)
	{
		if ($this->errorStore->hasError($name)) {
			return '<span class="pure-form-message">' . htmlspecialchars($this->errorStore->getError($name)) . '</span>';
		}
		
		return '';
	}
}

