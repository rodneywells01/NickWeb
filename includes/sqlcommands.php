<?php global $connection; ?>

<?php 

function sql_read($table, $where) {
	$query = "SELECT * FROM " . $table; 
	if ($where != null) {
		$query .= "WHERE " . $where;
	}
	$result = mysqli_query($connection, $query);
	
	confirm_query($result);
	return $result;
}

function sql_update($table, $columnvalues, $newvalues, $limit1 = false) {
	$query = "UPDATE " . $table . " SET"
}

?>