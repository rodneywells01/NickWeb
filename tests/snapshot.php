<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/local_connection.php';
$state = array();
foreach (array('main', 'about', 'contact', 'events', 'songs', 'videos', 'admins') as $table) {
    $state[$table] = mysqli_fetch_all(mysqli_query($connection, "SELECT * FROM `$table` ORDER BY id"), MYSQLI_ASSOC);
}
echo json_encode($state);
