<?php 
if isset($_GET['json']) {
	$phpobj = json_decode($_GET["json"], true);

	// Acquire all values. 
	$importantvalues  = array($connection, )
	$connection = $phpobj->connection;

}

?>