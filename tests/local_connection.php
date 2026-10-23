<?php
// Used ONLY in a sanitized temporary copy. Never includes production configuration.
if (getenv('NICKWEB_SYNTHETIC_FIXTURE') !== '1') {
    if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
    throw new RuntimeException('Synthetic fixture mode is required.');
}
$port = filter_var(getenv('NICKWEB_TEST_DB_PORT'), FILTER_VALIDATE_INT);
if (!$port || $port < 1024 || $port > 65535) {
    throw new RuntimeException('A non-default loopback database port is required.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection = mysqli_connect('127.0.0.1', 'nickweb_fixture', 'synthetic-local-only', 'nickweb_test', $port);
mysqli_set_charset($connection, 'utf8mb4');
