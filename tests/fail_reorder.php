<?php
// Inject a synthetic SQL failure halfway through a swap, never in production.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/local_connection.php';
foreach (array('songs', 'videos') as $table) {
    mysqli_query($connection, "CREATE TRIGGER fail_{$table}_swap BEFORE UPDATE ON {$table} FOR EACH ROW BEGIN IF OLD.id = 1 AND NEW.id = 2 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'synthetic swap failure'; END IF; END");
}
