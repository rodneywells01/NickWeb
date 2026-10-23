<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once(__DIR__ . '/../includes/request.php');
require_once(__DIR__ . '/../includes/validation_functions.php');
require_once(__DIR__ . '/../tools/php-security-policy.php');
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
$checks = 0;
function check($condition, $message) {
    global $checks;
    $checks++;
    if (!$condition) {
        throw new Exception($message);
    }
}
foreach (array('8.2' => 35, '8.3' => 36, '8.4' => 27, '8.5' => 12) as $branch => $patch) {
    check(!php_has_october_2026_fixes($branch . '.' . ($patch - 1)), 'Reject pre-fix release');
    check(php_has_october_2026_fixes($branch . '.' . $patch), 'Accept fixed release');
    check(php_has_october_2026_fixes($branch . '.' . ($patch + 1)), 'Accept later branch patch');
}
foreach (array('7.4.33', '8.1.99', '8.6.0RC4', '8.6.0', '9.0.0', '8.5.12RC1', '8.3.1-vendor') as $version) {
    check(!php_has_october_2026_fixes($version), 'Unknown or backported versions require evidence');
}
check(request_has_valid_shape(array(), array()), 'Empty request is valid');
check(request_has_valid_shape(array('redirect' => 'events', 'event' => '1'), array()), 'Event link works');
check(request_has_valid_shape(array('moveid' => '2', 'movedirection' => '-1'), array()), 'Move link works');
check(request_has_valid_shape(array(), array('username' => 'Nick', 'password' => '0')), 'Flat form works');
check(!request_has_valid_shape(array('event' => array('1')), array()), 'Nested query rejected');
check(!request_has_valid_shape(array(), array('username' => array('Nick'))), 'Nested form rejected');
foreach (array('0', '-1', '1 OR 1', '1.5', '1e2', '1000000000', 'word') as $bad) {
    check(!request_has_valid_shape(array('moveid' => $bad), array()), 'Invalid numeric input rejected');
}
check(!request_has_valid_shape(array('movedirection' => '1'), array()), 'Missing moveid rejected');
check(!request_has_valid_shape(array('moveid' => '1', 'movedirection' => 'word'), array()), 'Invalid direction rejected');

foreach (array(array(), array('username' => array('Nick')), array('username' => '  ')) as $form) {
    $_POST = $form;
    $errors = array();
    validate_presences(array('username'));
    check(isset($errors['username']), 'Missing, array or blank field yields a validation error');
}
foreach (array(array(), array('username' => array('Nick')), array('username' => 'long')) as $form) {
    $_POST = $form;
    $errors = array();
    validate_max_lengths(array('username' => 2));
    check(isset($errors['username']), 'Invalid or long field yields a length error');
}
$_POST = array('username' => '0');
$errors = array();
validate_presences(array('username'));
validate_max_lengths(array('username' => 2));
check(empty($errors), 'String zero remains valid');
echo 'PASS: ' . $checks . ' security checks.' . PHP_EOL;
