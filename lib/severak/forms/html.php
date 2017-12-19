<?php
namespace severak\forms;

class html
{
	/** @var form */
	protected $_form;
	public $fields = [];

	public function __construct(form $form)
	{
		$this->_form = $form;
		$this->fields = array_keys($form->fields);
	}

	protected function _text($value)
	{
		return htmlspecialchars($value);
	}

	protected function _attr($attr=[])
	{
		$out  = ' ';
		foreach ($attr as $key=>$val) {
			if (is_array($val)) continue; // skip options etc

			if ($val===true) {
				$out .= $key . ' ';
			} else {
				$out .= $key . '="' . htmlspecialchars($val, ENT_QUOTES). '" ';
			}
		}
		return $out;
	}
	
	function open($attr=[])
	{
		$attr = $attr + $this->_form->attr;
		return '<form ' . $this->_attr($attr) . '>';
	}
	
	function label($fieldName, $attr=[])
	{
		$form = $this->_form;
		if (empty($form->fields[$fieldName])) throw new usageException('Label ' . $fieldName . ' not defined.');
	
		$attr = $attr + $this->_form->attr;
		$field = $form->fields[$fieldName];
		
		if (in_array($field['type'], ['submit', 'reset', 'checkbox', 'hidden'])) {
			return ''; // these input types has no label
		}
		
		return '<label for="'.$field['id'].'">' . $field['label'] . '</label>';
	}
	
	function field($fieldName, $attr=[])
	{
		$form = $this->_form;
		if (empty($form->fields[$fieldName])) throw new usageException('Label ' . $fieldName . ' not defined.');
	
		$field = $attr + $form->fields[$fieldName];
		$fieldValue = '';
		if ($field['type']!='checkbox' && isset($field['value'])) $fieldValue = $field['value'];
		if (isset($form->values[$fieldName])) $fieldValue = $form->values[$fieldName];
		$out = '';
		
		if ($field['type']=='textarea') {
			// textarea
			$out .= '<textarea ' . $this->_attr($field) . '>';
			$out .= $this->_text($fieldValue);
			$out .= '</textarea>';

		} elseif ($field['type']=='select') {
			// select
			$out .= '<select ' . $this->_attr($field) . '>';
			foreach ($field['options'] as $value=>$text) {
				$_attr = ['value'=>$value];
				if ($fieldValue==$value) {
					$_attr['selected'] = true;
				}
				$out .= '<option '.$this->_attr($_attr).'>' . $this->_text($text) . '</option>';
			}
			$out .= '</select>';
		} else {
			// input
			if ($field['type']=='checkbox' && $fieldValue==$field['value']) {
				$field['checked'] = true;
			}

			if (!in_array($field['type'], ['submit', 'reset', 'password', 'checkbox'])) {
				$field['value'] = $fieldValue;
			}

			if ($field['type']=='checkbox') {
				$out .= ' <label for="'.$field['id'].'" class="'.$field['class'].'">';
				unset($field['class']);
			}
			
			$out .= '<input ' . $this->_attr($field) . '/>';
			
			if ($field['type']=='checkbox') {
				$out .= ' ' . $this->_text($field['label']) . '</label>';	
			}	
		}
		// todo - radio buttons
		
		return $out;
	}	
	
	function close()
	{
		return '</form>';
	}
	
	
	function all()
	{
		$form = $this->_form;
		
		$out = $this->open();
		foreach ($this->fields as $fieldName) {
			$out .= $this->label($fieldName);
			$out .= $this->field($fieldName);
			
			if (!empty($form->errors[$fieldName])) {
				// todo: nechceme spíš pole chyb?
				$out .= '<p class="error-message"><em>' . $this->_text($form->errors[$fieldName]) . '</em></p>';
			}
		}
		$out .= $this->close();
 		
		return $out;
	}

	function __toString()
	{
		return $this->all();
	}

}