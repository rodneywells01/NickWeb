<?php
	// define("DB_SERVER", "localhost");
 //  define("DB_USER", "root");
 //  define("DB_PASS", "");
 //  define("DB_NAME", "nickwebsite");
  

define("DB_SERVER", "aa1h2kiicn6toyg.cvpgnxpp51py.us-west-2.rds.amazonaws.com");
define("DB_USER", "rodneywells01");
define("DB_PASS", "justham9");
define("DB_NAME", "ebdb");
define("DB_PORT", "3306");

// define("DB_SERVER", "aa18jnukjbpxjzm.cvpgnxpp51py.us-west-2.rds.amazonaws.com");
// define("DB_USER", "rodneywells01");
// define("DB_PASS", "justham9");
// define("DB_NAME", "ebdb");
// define("DB_PORT", "3306");

  // 1. Create a database connection
  //$connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
try {
  $connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME, DB_PORT);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

 
  // Sample string 
  // $link = mysqli_connect($_SERVER['RDS_HOSTNAME'], 
  //  $_SERVER['RDS_USERNAME'], $_SERVER['RDS_PASSWORD'], 
  //  $_SERVER['RDS_DB_NAME'], $_SERVER['RDS_PORT']);            

  // $link = mysqli_connect(
        //'mydbinstance.abcdefghijkl.us-east-1.rds.amazonaws.com', 
        // 'sa',
        // 'mypassword', 
        // 'mydb', 
        // 3306);            


  // Test if connection succeeded, kill otherwise
  if(mysqli_connect_errno()) {
    die("Database connection failed: " . 
         mysqli_connect_error() . 
         " (" . mysqli_connect_errno() . ")"
    );
  }
?>
