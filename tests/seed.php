<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/local_connection.php';
if (mysqli_fetch_row(mysqli_query($connection, 'SELECT DATABASE()'))[0] !== 'nickweb_test') {
    throw new RuntimeException('Refusing to seed any other database.');
}
mysqli_multi_query($connection, file_get_contents(__DIR__ . '/schema.sql'));
do {
    if ($result = mysqli_store_result($connection)) { mysqli_free_result($result); }
} while (mysqli_more_results($connection) && mysqli_next_result($connection));
$statement = mysqli_prepare($connection, 'INSERT INTO admins (username, hashed_password) VALUES (?, ?)');
$username = 'fixture-admin';
$hash = password_hash('local-fixture-password', PASSWORD_BCRYPT);
mysqli_stmt_bind_param($statement, 'ss', $username, $hash);
mysqli_stmt_execute($statement);
echo "Synthetic fixture schema and records created.\n";
