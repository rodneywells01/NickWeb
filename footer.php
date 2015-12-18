	</div> <!-- Ending Div for Inner Wrap -->
	</div> <!-- Ending Div for Table -->
	</div> <!-- Ending Div for Wrap -->
	</div> <!-- Ending Div for Content -->
	<div id="footer">
		<div id="footertext">
			Developed by <a style="text-decoration: underline;" href="http://www.rodneywells.com">Rodney Wells</a>. Managed by Nicholas Nasibyan. <?php echo date("Y"); ?>
		</div>
		<?php 
		// Construct js function and text. 
		$redirectfunc = "admin_load();";
		$buttontext = "Admins";
		if ($admin) {
			//Admin logged in. Provide log out functionality. 
			$redirectfunc = "admin_logout();";
			$buttontext = "Log Out";
		}
		?>
		<div id="adminbutton" class="hvr-float hvr-grow hvr-reveal" onclick="<?php echo $redirectfunc;?>"><div class="centercontent"><?php echo $buttontext;?></div></div>
		
		<!-- hvr-underline-from-left -->
	</div>
	</div> <!-- Ending div for Inner Website Content -->
	</div> <!-- Ending Div for Website Content -->
	</div>
</div> <!-- Ending Div for Screen -->
</body>
</html>