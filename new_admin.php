
<?php include("index.php"); ?>
<?php confirm_logged_in(); ?>
<link rel="stylesheet" type="text/css" href="stylesheets/logon.css">

<?php 
  // CUSTOMIZE THIS
  if (isset($_POST['submit'])) {
    // User has submitted a form. 
    $admins = find_all_admins();
    $required_fields = array("username", "password");
    validate_presences($required_fields);

    if (empty($errors)) {
      $username = mysql_prep($_POST["username"]);
      $hashed_password = password_encrypt($_POST["password"]);

      $query = "INSERT INTO admins (";
      $query .= " username, hashed_password";
      $query .= ") VALUES (";
      $query .= " '{$username}', '{$hashed_password}'";
      $query .= ")";

      $result = mysqli_query($connection, $query);

      if ($result) {
        // Success
        $_SESSION["message"] = "Admin created.";
        redirect_to("events.php");
      } else {
        // Failure
        $_SESSION["message"] = "Admin failed to create.";
        redirect_to("events.php");
      }
    } else {
      // Errors exist. 
      $_SESSION["errors"] = $errors;
      redirect_to("new_admin.php");
    }
    
  } 
?>

<?php echo message(); ?>
<?php $errors = errors(); ?>
<?php echo form_errors($errors); ?>
<table id="logon">
  <tr>
    <td class="shortrow emphasis">
      Create New Admin
    </td>
    
  </tr>
  <form action="new_admin.php" method="post">
    <tr>
      <td>Username: &nbsp;&nbsp;&nbsp; <input type="text" name="username"> </td>
    </tr>
    <tr>
      <td>Password: &nbsp;&nbsp;&nbsp; <input type="password" name="password"> </td>
    </tr>
    <tr>
      <td class="shortrow emphasis">
        <input type="submit" name="submit" value="Create Admin"/>  
      </td>
      
    </tr>
  </form>
</table>