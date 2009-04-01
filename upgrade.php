<?php
$opt_list	= array('users');
	include 'Global.inc.php';
?>
<form action="" method="get"><div>
<?php print_tickboxes($opt_list); ?>
<input type="hidden" name="debug" value="<?php echo $debug; ?>" />
<input type="submit" name="upgrade" value="Upgrade" />
</div></form>
<?php
if(!empty($upgrade)) {

	if(!empty($_GET['target']))
		extract($_GET['target'], EXTR_OVERWRITE);

//names first
	$GEN_users_old	= FileSystem::Filtered_File_List($site_root."/Users", ".comm.php");

	$fail_list	= array();
	$rename	= 0;

	if($users)
		foreach($GEN_users_old as $val) {
			$orig	= $site_root."/Users/$val.comm.php";
			if(!is_readable($orig) || !is_writeable($orig) || !rename($orig, $site_root."/Users/$val.user.php"))
				array_push($fail_list, $orig);
			else
				$rename++;
		}

	if(!empty($fail_list))
		echo '<p id="error" style=" margin: 3px; padding: 7px; background-color: #FFB6C1;">
	The following files were not renamed - please make them world writeable then run this script again or rename them maunally:<br />
'.implode(",<br />\n", $fail_list)."</p>";
	elseif($rename)
		echo "<p>$rename file(s) successfully renamed.</p>";
	else
		echo "<p>All filenames are already correct.</p>";

//done
}
//	include 'Foot.inc.php';
?>