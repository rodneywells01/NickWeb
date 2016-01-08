<?php 
	session_start();

	function message() {
    if(isset($_SESSION["message"])) {
      $output =  "<div class=\"message\">";
      $output .=  htmlentities($_SESSION["message"]);
      $output .=  "</div>";

      // Clear message after use.
      $_SESSION["message"] = null;
      return $output;
    }
  }

  function errors() {
  	if(isset($_SESSION["errors"])) {
      $errors = $_SESSION["errors"];
     
      // Clear message after use.
      $_SESSION["errors"] = null;
      return $errors;
    }
  }
?>