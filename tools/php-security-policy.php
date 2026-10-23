<?php
function php_has_october_2026_fixes($version) {
    $minimum = array('8.2' => 35, '8.3' => 36, '8.4' => 27, '8.5' => 12);
    if (!preg_match('/^(8\.[2345])\.([0-9]+)$/D', $version, $parts)) {
        return false;
    }
    return (int)$parts[2] >= $minimum[$parts[1]];
}
