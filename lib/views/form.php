<?php
// universal pure CSS styled form
// arguments:
// - h2
// - form

// - links (url => button text)



$F = new severak\forms\html($form);
echo $F->open(['class'=>'mt-2']);

if (!empty($h2)) {
	echo '<h2 class="subtitle">' . $h2 . '</h2>';
}

foreach ($F->fields as $fieldName) {
	echo '<div class="field">';

	echo $F->label($fieldName, ['class'=>'label']);
	
	
	echo '<div class="control">';
	
	$attr = ['class'=>'input'];
	if ($form->fields[$fieldName]['type']=='textarea') $attr['class'] = 'textarea'; // TODO - ošetřit medium editor
	if ($form->fields[$fieldName]['type']=='checkbox') $attr['class'] = 'checkbox';
	if ($form->fields[$fieldName]['type']=='select') $attr['class'] = 'select';
	if ($form->fields[$fieldName]['type']=='submit') $attr['class'] = 'button is-primary';
	
	
	if ($attr['class']=='select') { echo '<div class="select">'; }
	
	echo $F->field($fieldName, $attr);
	
	if ($attr['class']=='select') { echo '</div>'; }
	
	if (!empty($form->errors[$fieldName])) {
		echo ' <span class="help is-danger">' . htmlspecialchars($form->errors[$fieldName]) . '</span>';
	}
	echo '</div>';
	echo '</div>';
}

if (!empty($links)) foreach ($links as $url=>$linkText) {
	echo '<div class="field">';
	echo '<div class="control">';
    echo '<a href="'.$url.'" class="button">'.$linkText.'</a>';
    echo '</div>';
    echo '</div>';
}

echo $F->close();