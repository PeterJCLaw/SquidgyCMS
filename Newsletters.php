<?php
	include 'Head.inc.php';
?>
<?php
	if(stristr($dir, "doc") != FALSE && !$logged_in)
		print_logon_form();
	else
		print_files("news");
?>
<?php
	include 'Foot.inc.php';
?>