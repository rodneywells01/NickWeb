<?php

$errors = array();

function fieldname_as_text($fieldname) {
	$fieldname = str_replace("_", " ", $fieldname);
	$fieldname = ucfirst($fieldname);
	return $fieldname;
}
// * presence
// use trim() so empty spaces don't count
// use === to avoid false positives
// empty() would consider "0" to be empty
function has_presence($value) {
	return isset($value) && $value !== "";
}

function validate_presences($required_fields) {
	global $errors;
	foreach ($required_fields as $field) {
		validate_presence($field);
	}
}
function validate_presence($required_field) {
	global $errors;
	$value = isset($_POST[$required_field]) ? $_POST[$required_field] : null;
	if (!is_string($value) || !has_presence(trim($value))) {
		$errors[$required_field] = fieldname_as_text($required_field) . " can't be blank!";
	}		
	
}
// * string length
// max length
function has_max_length($value, $max) {
	return strlen($value) <= $max;
}
function validate_max_lengths($fields_with_max_lengths) {
	global $errors;
	// Expects an assoc. array
	foreach($fields_with_max_lengths as $field => $max) {
		$value = isset($_POST[$field]) ? $_POST[$field] : null;
	  if (!is_string($value) || !has_max_length(trim($value), $max)) {
	    $errors[$field] = fieldname_as_text($field) . " is too long";
	  }
	}
}

// * inclusion in a set
function has_inclusion_in($value, $set) {
	return in_array($value, $set);
}

function verify_str_contains($needles, $haystack) {
	/*
	Posts to errors if str fails to contain value. s
	*/
	global $errors;
	foreach ($needles as $needle) {
		if (strpos($haystack, $needle) === false) {
			$errors[$needle] = "\"{$needle}\" could not be found in pasted value!"; 
		} 
}

}


?>
