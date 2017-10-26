<?php
namespace severak\forms;

class html
{
	/** @var form */
	protected $_form;

	public function __construct(form $form)
	{
		$this->_form = $form;
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

			if ($key=='required') continue; // todo: tohle je jen pro test

			if ($val===true) {
				$out .= $key . ' ';
			} else {
				$out .= $key . '="' . htmlspecialchars($val, ENT_QUOTES). '" ';
			}
		}
		return $out;
	}

	function __toString()
	{
		$form = $this->_form;
		
		$out = '<form ' . $this->_attr($form->attr) . '>';

		foreach ($form->fields as $name=>$field) {
			$fieldValue = isset($form->values[$name]) ? $form->values[$name] : '';


			if (!in_array($field['type'], ['submit', 'reset', 'checkbox'])) {
				$out .= '<label for="'.$field['id'].'">' . $field['label'] . '</label>';
				unset($field['label']);
			}

			if ($field['type']=='textarea') {
				$out .= '<select ' . $this->_attr($field) . '>';
				$out .= $this->_text($fieldValue);
				$out .= '</select>';

			} elseif ($field['type']=='select') {
				$out .= '<select ' . $this->_attr($field) . '>';
				foreach ($field['options'] as $key=>$val) {
					$_attr = ['value'=>$val];
					if ($fieldValue===$val) {
						$_attr['selected'] = true;
					}
					$out .= '<option '.$this->_attr($_attr).'>' . $this->_text($val) . '</option>';
				}
				$out .= '</select>';


			} else {

				if ($field['type']=='checkbox' && $fieldValue===$field['value']) {
					$field['checked'] = true;
				}

				if (!in_array($field['type'], ['submit', 'reset', 'password', 'checkbox'])) {
					$field['value'] = $fieldValue;
				}


				$out .= '<input ' . $this->_attr($field) . '/>';
				if ($field['type']=='checkbox') {
					$out .= ' <label for="'.$field['id'].'">' . $field['label'] . '</label>';
				}
			}
			
			if (!empty($form->errors[$name])) {
				// todo: nechceme spíš pole chyb?
				$out .= '<p class="error-message"><em>' . $this->_text($form->errors[$name]) . '</em></p>';
			}
		}

		$out .= '</form>';
		return $out;
	}

}