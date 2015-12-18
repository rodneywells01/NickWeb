<?php require("includes/connection.php"); ?>
<?php include_once("index.php"); ?>


<?php 
$query = "SELECT * FROM events ORDER BY datetime ASC"; 
				global $connection;
				$eventlistdata = mysqli_query($connection, $query); 

				$admin = true;
				?>
<form action="events.php" method="post">
				<input type="text" name="swagout"/>
				<?php 

				while($event = mysqli_fetch_assoc($eventlistdata)) {
					$eventrow = "<tr><td>";
					$eventrow .= "<div class=\"eventdisplay\"><div class=\"eventcontent\">";

					$eventrow .= strtoupper(date("M", strtotime($event["datetime"])));
					$eventrow .= "<br />";
					$eventrow .= strtoupper(date("j", strtotime($event["datetime"])));
					$eventrow .= "</div></div></td><td>";
					$name1 = "event_name" . $event["id"]; // Name creation for variable
					if($admin) { $eventrow.= "<input type=\"text\" style=\"width: 100px;\" name=\"event_name{$event["id"]}\" value=\""; }
					$eventrow .= htmlentities($event["name"]);
					if($admin) { $eventrow.= "\" />"; }
					$eventrow .= "</td><td>";	
					$name2 = "event_location" . $event["id"]; // Name creation for variable	
					if($admin) { $eventrow.= "<input type=\"text\" style=\"width: 100px\" name=\"event_location{$event["id"]}\" value=\""; }					
					$eventrow .= htmlentities($event["location"]); 
					if($admin) { $eventrow.= "\" />"; }
					$eventrow .= "</td><td>";
					$eventrow .= date("h:ia", strtotime($event["datetime"]));
					$eventrow .= "</td><td>";
					$name3 = "event_description" . $event["id"]; // Name creation for variable
					if($admin) { $eventrow.= "<textarea  name=\"event_description{$event["id"]}\">"; }					
					$eventrow .= htmlentities($event["description"]); 
					if($admin) { $eventrow.= "</textarea>"; }
					$eventrow .= "</td></tr>";
					echo $eventrow;
					
				}
									

				?>
				<tr>
					<td colspan="5">
						<?php if ($admin) { ?>
							<input type=\"submit\" name=\"submit\" value=\"Save Changes\" />
							</form>
						<?php }