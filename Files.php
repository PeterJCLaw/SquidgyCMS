<?php
	include 'Head.inc.php';
?>
<?php
	if($logged_in)
		print_files("file");
	else
		print_logon_form();
?>
<?php
	include 'Foot.inc.php';
?>