<?php
namespace severak\forms;
use severak\forms\rules;

class form
{
	public $isValid = true;
	public $errors = [];
	public $values = [];
	public $fields = [];
	public $attr=[];

	protected $_rules;

	public $messages = [
		'required' => 'Field is required.'
	];

	public function __construct($attr=[])
	{
		if (empty($attr['id'])) $attr['id'] = 'form';

		$this->attr = $attr;
	}

	public function field($name, $attr=[])
	{
		if (isset($this->fields[$name])) {
			throw new usageException('Field '.$name.' already defined.');
		}
		$attr['name'] = $name;

		// defaulty
		if (empty($attr['type'])) $attr['type'] = 'text';
		if (empty($attr['label'])) $attr['label'] = ucfirst($name);

		if ($attr['type']=='submit') $attr['value'] = $attr['label'];

		if ($attr['type']=='checkbox' && empty($attr['value'])) $attr['value']=1;
		if ($attr['type']=='select' && empty($attr['options'])) $attr['options']=[];

		// automatic ID
		if (empty($attr['id'])) $attr['id'] = $this->attr['id'] . '_' . $name;
		// ---
		$this->fields[$name] = $attr;

		// implicit rule's
		if (!empty($attr['required'])) $this->rule($name, 'severak\forms\rules::required', $this->messages['required']);
	}

	public function rule($name, $callback, $message)
	{
		$this->_rules[$name][] = ['check'=>$callback, 'message'=>$message];
	}

	public function fill($data)
	{
		foreach ($data as $key=>$val) {
			if (!empty($this->fields[$key])) {
				$this->values[$key] = $val;
			}
		}

		return $this->values;
	}

	public function validate()
	{
		$this->isValid = true;
		foreach ($this->_rules as $name => $rules) {
			$fieldValue = isset($this->values[$name]) ? $this->values[$name] : '';

			foreach ($rules as $rule) {
				$passed = call_user_func_array($rule['check'], [$fieldValue, $this->values]);
				if (empty($passed)) {
					$this->errors[$name] = $rule['message'];
					$this->isValid = false;
					break;
				}
			}
		}
		return $this->isValid;
	}

	function __toString()
	{
		return (string) new html($this);
	}

}