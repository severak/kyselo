<?php
// universal pure CSS styled form
// arguments:
// - h2
// - form

if (!empty($h2)) {
	echo '<h2>' . $h2 . '</h2>';
}

$F = new severak\forms\html($form);
echo $F->open(['class'=>'pure-form pure-form-aligned']);
echo '<fieldset>';
foreach ($F->fields as $fieldName) {
	
	if (in_array($form->fields[$fieldName]['type'], ['checkbox', 'submit'])) {
		echo '<div class="pure-controls">';
	} else {
		echo '<div class="pure-control-group">';
	
	}
	
	echo $F->label($fieldName);
	
	$attr = [];
	if ($form->fields[$fieldName]['type']=='submit') $attr['class'] = 'pure-button pure-button-primary';
	if ($form->fields[$fieldName]['type']=='checkbox') $attr['class'] = 'pure-checkbox';
	
	echo $F->field($fieldName, $attr);
	if (!empty($form->errors[$fieldName])) {
		echo ' <span class="pure-form-message-inline kyselo-form-error">' . htmlspecialchars($form->errors[$fieldName]) . '</span>';
	}
	echo '</div>';
}
echo '</fieldset>';
echo $F->close();