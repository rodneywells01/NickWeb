<?php
// Deliberately CLI-only. Never publish a phpinfo() or runtime diagnostic endpoint.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once(__DIR__ . '/php-security-policy.php');

echo 'CLI PHP version: ' . PHP_VERSION . PHP_EOL;
echo 'CLI checks do not establish the web SAPI or vendor backport status.' . PHP_EOL;
$ok = php_has_october_2026_fixes(PHP_VERSION);
if (!$ok) {
    echo 'FAIL: upstream patch level not confirmed. Upgrade or verify vendor CVE evidence.' . PHP_EOL;
}
foreach (array('session.upload_progress.enabled', 'file_uploads') as $setting) {
    $disabled = in_array(strtolower((string)ini_get($setting)), array('', '0', 'off'), true);
    echo ($disabled ? 'PASS: ' : 'FAIL: ') . $setting . ' must be disabled.' . PHP_EOL;
    $ok = $ok && $disabled;
}
if (PHP_VERSION_ID >= 80500 && ini_get('fatal_error_backtraces')) {
    echo 'FAIL: fatal_error_backtraces must be disabled on PHP 8.5.' . PHP_EOL;
    $ok = false;
}
echo $ok ? 'PASS: sampled CLI runtime checks; verify web workers separately.' . PHP_EOL
         : 'FAIL: runtime checks need operator review.' . PHP_EOL;
exit($ok ? 0 : 1);
