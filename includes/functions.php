<?php
	function redirect_to($new_location) {
		header("Location: " . $new_location);
		exit;
	}

	function mysql_prep($string) {
		global $connection;
		$escaped_string = mysqli_real_escape_string($connection, $string);
		return $escaped_string;
	}

	function confirm_query($result_set) {
		if (!$result_set) {
			die("Database query failed.");
		}
	}

	function form_errors($errors=array()) {
		$output = "";
		if (!empty($errors)) {
		  $output .= "<div class=\"error\">";
		  $output .= "Please fix the following errors:";
		  $output .= "<ul>";
		  foreach ($errors as $key => $error) {
		    $output .= "<li>";
		    $output .= htmlentities($error);
		    $output .= "</li>";
		  }
		  $output .= "</ul>";
		  $output .= "</div>";
		}
		return $output;
	}


	function password_encrypt($password) {
		$hash_format = "$2y$10$";
		$salt_length = 22; 
		$salt = generate_salt($salt_length); 
		$format_and_salt = $hash_format . $salt;
		$hash = crypt($password, $format_and_salt);
		return $hash;
	}

	function generate_salt($length) {
		$unique_random_string = md5(uniqid(mt_rand(), true));

		// Valid Characters for salt are [a-zA-Z0-9./]
		$base64_string = base64_encode($unique_random_string);

		// But not "+", which is valid in base 64 encoding. 
		$modified_base64_string = str_replace('+', '.', $base64_string);

		// Truncate string to correct length. 
		$salt = substr($modified_base64_string, 0, $length);

		return $salt;
	}

	function password_check($password, $existing_hash) {
		$hash = crypt($password, $existing_hash); 
		if ($hash === $existing_hash) {
			return true;
		} else {
			return false;
		}
	}
	
	function find_all_admins() {
		// Query database for list of admins. 
		global $connection;

		$query = "SELECT * FROM admins ORDER BY username ASC";
		$admin_list = mysqli_query($connection, $query);
		confirm_query($admin_list);
		return $admin_list;
	}

	function find_admin_by_id($admin_id)  {
		global $connection;

		$safe_admin_id = mysqli_real_escape_string($connection, $admin_id);
		$query = "SELECT * FROM admins ";
		$query .= "WHERE id={$safe_admin_id} ";
		$query .= "LIMIT 1";
		$admin_set = mysqli_query($connection, $query); 
		confirm_query($admin_set);

		if($admin = mysqli_fetch_assoc($admin_set)) {
			return $admin;		
		} else {
			return null;
		}

	}

	function find_admin_by_username($username) {
		global $connection;

		$safe_username = mysqli_real_escape_string($connection, $username);
		$query = "SELECT * FROM admins ";
		$query .= "WHERE username = '{$safe_username}' ";
		$query .= "LIMIT 1";
		$admin_set = mysqli_query($connection, $query); 
		confirm_query($admin_set);

		if($admin = mysqli_fetch_assoc($admin_set)) {
			return $admin;	
		} else {
			return null;
		}
	}
	function attempt_login($username, $password) {
		$admin = find_admin_by_username($username); 
		if ($admin) {
			// found admin, now check password. 
			if (password_check($password, $admin['hashed_password'])) {
				// Password matches. 
				return $admin;
			} else {
				// Password does not match. 
				return false;
			}
		} else {
			// admin not found. 
			return false;
		}
	}

	function logged_in() {
		return isset($_SESSION['admin_id']);
	}

	function confirm_logged_in() {
		if(!logged_in()) {
		    // Admin is not logged in.
		    redirect_to("logon.php");
		}
	}

	function compress_table($tablename) {
		// Asssumes id will not be less than optimal (IDS can't auto shrink). 
		global $connection;
		$query = "SELECT * FROM ";
		$query .= $tablename; 
		$subject_set = mysqli_query($connection, $query); 
		confirm_query($subject_set); 

		$optimalid = 1;
		while($row = mysqli_fetch_assoc($subject_set)) {
			if ($row['id'] != $optimalid) {
				// Update row ID. 
				$query = "UPDATE ";
				$query .= $tablename; 
				$query .= " SET "; 
				$query .= "id = {$optimalid} "; 
				$query .= "WHERE id = {$row['id']} "; 
				$query .= "LIMIT 1"; 
				mysqli_query($connection, $query); 
			}

			$optimalid += 1;
		}
	}

	function check_event_delete() {
		if (isset($_GET['delete'])) {
			// User is deleting an event. 
			global $connection;

			$deletetarget = $_GET['delete']; 
			$query = "DELETE FROM events WHERE id = {$deletetarget} LIMIT 1";
			$result = mysqli_query($connection, $query);
			confirm_query($result);
			$_SESSION["message"] = "Event deleted!";
			redirect_to("index.php?redirect=events");
		}
	}

	function check_event_add() {
		if (isset($_POST['add'])) {
			// User is adding an event. 
			global $connection;

			// Determine add target. 
			$query = "SELECT * FROM events";
			$response = mysqli_query($connection, $query);
			$addtarget = (int)mysqli_num_rows($response) + 1;

			// Validate data. 
			$required_fields = array("event_name{$addtarget}" ,"event_location{$addtarget}" ,"event_datetime{$addtarget}" ,"event_description{$addtarget}");
			validate_presences($required_fields);
			foreach ($required_fields as $value) {
				$_POST[$value] = mysql_prep($_POST[$value]);
			}

			// Update Database with new data.
			if (empty($errors)) {
				$query = "INSERT INTO events (";
			    $query .= " name, location, description, datetime";
			    $query .= ") VALUES (";
			    $query .= " '{$_POST["event_name" . $addtarget]}', 
			    			'{$_POST["event_location" . $addtarget]}', 
			    			'{$_POST["event_description" . $addtarget]}', 
			    			'{$_POST["event_datetime" . $addtarget]}'";
			    $query .= ")";

				$result = mysqli_query($connection, $query);
				confirm_query($result);

				$_SESSION["message"] = "Event added!";

			}  else {
				$_SESSION["message"] = "Event add failed! Try again?";
			}
	
			redirect_to("index.php?redirect=events");

		}
	}

	function display_event_row($event, $admin) {
		/*
		Create and display an event row for events.php based on
		- $event, which contains DB Info 
		- $admin, which bools whether an Admin is logged in. 
		*/
		$eventrow = "<tr>";
		$eventrow .= "<td>";

		if ($admin) { 
			$eventrow.= "<a href=\"events.php?delete={$event["id"]}\"><div class=\"mybutton\"><div class=\"centercontent\">Delete Me</div></div></a></td><td>";
			$eventrow .= "<input class=\"datetimeselector\" type=\"text\" style=\"width: 110px;\" name=\"event_datetime{$event["id"]}\" value=\"";
			$eventrow .= date("y-m-d H:i:s", strtotime($event["datetime"]));
			$eventrow .= "\"/>";
		} else {
			$eventrow .= "<div class=\"eventdisplay\"><div class=\"centercontent\">";
			$eventrow .= strtoupper(date("M", strtotime($event["datetime"])));
			$eventrow .= "<br />";
			$eventrow .= strtoupper(date("j", strtotime($event["datetime"])));
			$eventrow .= "</div></div>";
		}
		$eventrow .= "</td><td>";
		if($admin) { $eventrow.= "<input type=\"text\" style=\"width: 100%;\" name=\"event_name{$event["id"]}\" value=\""; }
		$eventrow .= htmlentities($event["name"]);
		if($admin) { $eventrow.= "\" />"; }
		$eventrow .= "</td><td>";	
		if($admin) { $eventrow.= "<input type=\"text\" style=\"width: 100%\" name=\"event_location{$event["id"]}\" value=\""; }					
		$eventrow .= htmlentities($event["location"]); 
		if($admin) { $eventrow.= "\" />"; }
		$eventrow .= "</td><td>";
		$eventrow .= date("g:ia", strtotime($event["datetime"]));
		$eventrow .= "</td>";
		if($admin) { 
			// Add description for editing
			$eventrow .= "<td><textarea class=\"customtextarea\" name=\"event_description{$event["id"]}\">"; 
			$eventrow .= htmlentities($event["description"]);
			$eventrow .= "</textarea></td>"; 
		} 
		$eventrow .= "</tr>";
		echo $eventrow;
	}

	function new_display_event_row($event, $admin) {
		/*
		Create and display an event row for events.php based on
		- $event, which contains DB Info 
		- $admin, which bools whether an Admin is logged in. 
		*/
		$eventrow = "<div id=\"eventrow{$event["id"]}\" style=\"cursor:pointer\" class=\"eventrow";
		if (!$admin) {
			$eventrow .= " hvr-border-fade";
		}
		$eventrow .="\"";
		if (!$admin) {
			$eventrow .= "onclick=\"display_event_description({$event["id"]});\"";	
		} 		
		$eventrow .= "><div class=\"eventcell\">";
		if ($admin) { 
			$eventrow.= "<a href=\"events.php?delete={$event["id"]}\"><div class=\"deletecontainer\"><div class=\"centercontent\">Delete Me</div></div></a></div><div class=\"eventcell\">";
			$eventrow .= "<input class=\"datetimeselector\" type=\"text\" style=\"width: 100px;\" name=\"event_datetime{$event["id"]}\" value=\"";
			$eventrow .= date("y-m-d H:i:s", strtotime($event["datetime"]));
			$eventrow .= "\"/>";
		} else {
			$eventrow .= "<div class=\"eventdisplay\"><div class=\"centercontent\">";
			$eventrow .= strtoupper(date("M", strtotime($event["datetime"])));
			$eventrow .= "<br />";
			$eventrow .= strtoupper(date("j", strtotime($event["datetime"])));
			$eventrow .= "</div></div>";
		}
		$eventrow .= "</div><div id=\"eventname\"class=\"eventcell\">";
		if($admin) { $eventrow.= "<input type=\"text\" style=\"width: 100%;\" name=\"event_name{$event["id"]}\" value=\""; }
		$eventrow .= htmlentities($event["name"]);
		if($admin) { $eventrow.= "\" />"; }
		$eventrow .= "</div><div class=\"eventcell\">";
		if($admin) { $eventrow.= "<input type=\"text\" style=\"width: 100%\" name=\"event_location{$event["id"]}\" value=\""; }					
		$eventrow .= htmlentities($event["location"]); 
		if($admin) { $eventrow.= "\" />"; }
		$eventrow .= "</div><div class=\"eventcell\">";
		$eventrow .= date("g:ia", strtotime($event["datetime"]));
		$eventrow .= "</div>";
		if($admin) { 
			// Add description for editing
			$eventrow .= "<div class=\"eventcell\"><textarea class=\"customtextarea\" name=\"event_description{$event["id"]}\">"; 
			$eventrow .= htmlentities($event["description"]);
			$eventrow .= "</textarea></div>"; 
		} 
		$eventrow .= "</div>";
		echo $eventrow;
	}

	function store_description_content($event) {
		// Use PHP to store hidden info about event in document. 
		// This is to be retrieved using javascript. 
		$hiddeninfo = "<div class=\"hideinfo\" id=\"hideevent{$event["id"]}\">"; 
		$hiddeninfo .= $event["description"];
		$hiddeninfo .= "</div>";

		echo $hiddeninfo;
	}
	function display_song($song, $admin) {
		/* 
		Constructs and displays songrow based on $admin. 
		*/

		$songrow = "<tr>";
		if($admin) {
			// Adding Up/Down buttons and delete button for Admins. 
			$songrow .= "<td class=\"songcontrol\">";
			$safe_id = urlencode($song["id"]);
			$songrow .= "<a href=\"songs.php?movedirection=-1&moveid={$safe_id}\"><img class=\"movearrow hvr-float\" src=\"arrowup.png\"></a>";
			$songrow .= "<br />";
			$songrow .= "<br />";
			$songrow .= "<a href=\"songs.php?movedirection=1&moveid={$safe_id}\"><img class=\"movearrow hvr-sink\" src=\"arrowdown.png\"></a>";
			$songrow .= "</td>";
			$songrow .= "<td class=\"deletesongcell\"><a href=\"songs.php?deleteid={$song["id"]}\"><div onclick=\"return confirm('Are you sure you wish to delete this song?')\" class=\"hvr-border-fade deletecontainer\"><div class=\"deletetext\">Delete Song</div></div></td>";
		} 
		$songrow .= "<td class=\"songcell\" colspan=\"3\">";
		$songrow .= $song["songcode"]; 
		$songrow .= "</td>"; 
		
		$songrow .= "</tr>";

		echo $songrow;
	}

	function fix_song_size($songcode) {
		$desiredheight = "150"; 
		$predictedheight = "450";
		$editedsongcode = str_replace($predictedheight, $desiredheight, $songcode); 
		$mysql_safe_song = mysql_prep($editedsongcode); 
		return $mysql_safe_song;
	}

	function remove_from_table($id, $table, $activepage) {
		global $connection;
		$query = "DELETE FROM {$table} ";
		$query .= "WHERE id = {$id} ";
		$query .= "LIMIT 1"; 

		$result = mysqli_query($connection, $query); 

		if ($result && mysqli_affected_rows($connection) == 1) {
			// Success 
			$_SESSION["message"] = "Item deleted.";
			compress_table($table); // Shrink the table down.
	  	} else {
		    // FAILURE. Awwwwww
		    $_SESSION["message"] = "Item deletion failed!";
	  	}
	  	redirect_to("index.php?redirect={$activepage}");

	}

	function display_contact_icon($admin, $data, $socialmedia) {
		// Construct social media icon based on admin or user. 
		$contacticon = "";
		if(!$admin) {
			$contacticon .= "<div class=\"socialmediawrap hvr-grow\"><a href=\"";
		} else {
			$contacticon .= "<tr><td><a href=\"";
		} 
		// Display social media icon. 
		$contacticon .= $data[$socialmedia]; 
		$contacticon .= "\"><div class=\"iconwrap\"><img src=\"icons/{$socialmedia}.png\"></div></a>";
		if(!$admin) {	
			// Close div for user. 
			$contacticon .= "</div>";
		} else {
			// Close td and add input for admin.
			$contacticon .= "</td><td>";
			$contacticon .= "<input class=\"fullwidth socialinput\"type=\"text\" name=\"{$socialmedia}\" value=\"{$data[$socialmedia]}\"/><br/>"; 
			$contacticon .= "</tr>";
		}			
		echo $contacticon;
	}

	function display_videos($result, $maxvids, $admin) {
		while($video = mysqli_fetch_assoc($result)) {
			// Continually display video rows. 			
			$videorow = "<tr>"; 
			if($admin) {
				// Display admin row. 
			
				// Create admin cell. 
				$videorow .= "<td class=\"admincell\"><ul>";
				
				// Construct up/down arrows.
				$videorow .= "<li>";
				$videorow .= "<a href=\"videos.php?movedirection=-1&moveid={$video["id"]}\"><img class=\"movearrow hvr-float\" src=\"arrowup.png\"></a>";
				$videorow .= "<br/>"; 
				$videorow .= "<br/>"; 
				$videorow .= "<a href=\"videos.php?movedirection=1&moveid={$video["id"]}\"><img class=\"movearrow hvr-sink\" src=\"arrowdown.png\"></a></li>";

				// Construct delete video button. 
				$videorow .= "<li>"; 
				$videorow .= "<a href=\"videos.php?deleteid={$video["id"]}\">";
				$videorow .= "<div onclick=\"return confirm('Are you sure you wish to delete this video?')\" class=\"hvr-border-fade deletecontainer\">";
				$videorow .= "<div class=\"deletetext\">Delete Video</div></div></a></li>";
				$videorow .= "</ul></td>";

				$videorow .= create_video_cell($video);				
			} else {
				// one video in row. 
				$videorow .= "<tr>";
				$videorow .= create_video_cell($video);
			}
			$videorow .= "</tr>";
			echo $videorow;
		}
	}
	
	function create_video_cell($video) {
		$videocell = "<td class=\"videocell\"><div>";
		$videocell .= $video["videocode"];
		$videocell .= "</div></td>";
		return $videocell;
	}

	function tab2nbsp($str)
	{
		// Fix tabbing in text content from database. 
	    return str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $str); 
	}

	function generate_prompt($titletext, $promptcontent, $promptname = "") {
		$form = "<div id=\"messagepromptcontainer{$promptname}\" class=\"messagepromptcontainer\">
						<div id=\"messageprompt\">
							<div onclick=\"remove_prompt_popup('{$promptname}');\" id=\"closeprompt\" class=\"hvr-sweep-to-right\" >
								<div>X</div>
							</div>
							<div class=\"emphasis\" id=\"prompttitle\">{$titletext}</div>
							<div id=\"promptbody\">";
		$form .= $promptcontent;
		$form .= "</div></div></div>";
		echo $form;
	}

	function generate_page_header($activepage) {
		require_once("includes/connection.php");
		require_once("includes/validation_functions.php");
		require_once("includes/session.php");
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheets/{$activepage}.css\">";
		echo "<script type=\"text/javascript\" src=\"scripts/popup.js\"></script>";
	}
	
?>

	