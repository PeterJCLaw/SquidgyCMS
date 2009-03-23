<?php
class AdminWebmaster extends Admin {
	function AdminWebmaster() {
		parent::__construct('Reset committee passwords or make changes to the Admin area of the site', -1, 20);
	}

	function printPass() {
		$GLOBALS['target'] = "";
		echo "\n<h4>Reset Passwords:</h4>";
		print_tickboxes($GLOBALS['job_list'], "right");
		return;
	}

	function printUsers() {
		global $job_list; ?>
		<h4>Manage Users:</h4>
			<table id="admin_users_tbl" class="admin_tbl"><tr>
				<th title="Their Committee Position" class="L">User:</th>
				<th title="Their Displayed Name" class="M">Name:</th>
				<th title="Integers only, low numbers float above high numbers, zero and null values will not appear in the list" class="T M">Weight:</th>
				<th title="Tick the box to delete the page, this cannot be undone" class="T R">Delete:</th>
			</tr><?php
		foreach($job_list as $Person) {
			if(in_array($Person, array('Committee', 'Chaplain')))
				continue;

			include user_file($Person);

			$del_box	= '<input type="checkbox" class="tick" name="del['.$Person.'.comm.php]" />';
			$weight_box	= '<input name="weight['.$Person.']"'.(empty($job_list[$Person]['weight']) ? '' : ' value="'.$job_list[$Person]['weight'].'"').' type="text" maxlength="2" size="2" class="num" />';

			echo '
			<tr>
				<td class="L">'.$Person.'</td>
				<td class="M">'.$name.'</td>
				<td class="T M">'.$weight_box.'</td>
				<td class="T R">'.$del_box.'</td>
			</tr>';
		}
			echo '
			<tr>
				<td class="L">New User:</td>
				<td class="R" colspan="3"><input name="new_user" type="text" /></td>
			</tr>';
		?>
			</table>
	<?php
		return;
	}

	function printAdminSections() {
	global $_Admin_list, $toclist, $debug; ?>
			<h4>Change Available Admin Sections:</h4>
	<table id="admin_sect_tbl" class="admin_tbl"><tr>
			<th class="L">Section Name:</th><th class="M">Section Description:</th><th class="R">Enabled:</th>
		</tr><?php

		foreach($_Admin_list as $val) {
			if(isset($rows[$val['grouping']]))
				$rows[$val['grouping']]++;
			else
				$rows[$val['grouping']]	= 1;
		}

		if($debug)
			print_r($rows);

		foreach($_Admin_list as $section => $val)
		{
			$sect_title	= $val['desc'];
			$grouping	= $val['grouping'];
			$section_human	= $val['section_human'];

			if($grouping < 0)	//skip the rest if its a compulsory module
				continue;

			if(in_array($section, $toclist))
				$checked	= " checked=\"checked\"";
			else
				$checked	= "";

			$sect_box	= '<input type="checkbox" class="tick" name="sect['."$section]\" id=\"_enable_$section\"$checked />";

			echo "<tr>
					<td class=\"L\"><label for=\"_enable_$section\">$section_human</label></td><td class=\"M\">$sect_title</td>\n";

			if($grouping <= 0)	//normally
				echo "			<td class=\"R\">$sect_box</td>\n";
			elseif(empty($group_done) || $grouping > $group_done) {	//where they're grouped and its a new group
				echo '			<td class="R" rowspan="'.$rows[$grouping].'">'."$sect_box</td>\n";
				$group_done	= $grouping;
			}
			echo "		</tr>";
		}
?>

	</table>
<?php return;
	}

	function printFormAdmin() {
		$this->printPass();
		$this->printAdminSections();
		$this->printUsers();
		return;
	}

	function reset_pass($who)
	{
		global $debug_info, $job_list, $error, $website_name_short, $webmaster_email;

		$file	= user_file($who);	//convert to a filename type and include
		include $file;
		$error .= $this->change_commiittee_files($pass_hash, md5("password"), $file);
		send_mail(email($who), "$who: $website_name_short Website Password Reset", "Dear ".$who
			.",\n\nYour password for the $website_name_long website has been reset to 'password' (without the quotes)."
			."\n\nIf you did not request this and you have not just been elected to the committee then please email the Webmaster ($webmaster_email) and report this error."
			."\n\n$website_name_short Webmaster", "From: $website_name_short Webmaster <$webmaster_email>");
		return;
	}

	function change_commiittee_files($old_val, $new_val, $file)
	{
		global $debug_info;

		$old_val	= "= \"".$old_val;
		$new_val	= "= \"".$new_val;

		$old_file_contents = file_get_contents($file);

		$new_file_contents = str_replace($old_val, $new_val, $old_file_contents);

		$debug_info .= "\$file=$file\n<br />\$new_val=$new_val\n<br />\$old_val=$old_val\n<br />"
				."\$old_file_contents=$old_file_contents\n<br />\$new_file_contents=$new_file_contents\n<br />\n";

		return file_put_stuff($file, $new_file_contents, 'w');
	}

	function submit()
	{
		global $debug_info, $error, $username, $sect, $target, $job_list, $website_name_short, $webmaster_email, $admin_file;

		if(!empty($target))
		{
			foreach($job_list[$i] as $job)	//cycle through everyone you might want to reset the password of
			{
				if((!empty($target[$job]) || !empty($target["Whole Committee"])) && !in_array(strtolower($job), array("chaplain", "committee")))	//if theres a match then include them on the to line
				{
					reset_pass($job);
					$reset_list	.= "$job\n";
				}
				$debug_info	.= "\$target[$job]=$target[$job]\n<br />";
			}
			if(empty($error) || $debug)
				send_mail("Webmaster", "$website_name_short Website Password Reset", "The following passwords have been reset successfully:\n\n$reset_list",
					"From: $website_name_short Webmaster <$webmaster_email>");
		}
		$debug_info	.= "\$target=$target\n<br />\$sect=$sect\n<br />";
		if(!empty($sect))
		{
			$toclist	= array();
			foreach($sect as $sect_key => $tmpval)
			{
				if($tmpval)
					array_push($toclist, $sect_key);

				$debug_info	.= "\$sect[$sect_key]=$sect[$sect_key],	\$tmpval	= $tmpval\n<br />";
			}
			$error	.= file_put_stuff($admin_file, implode("\n", $toclist), 'w');
		}

		return $error;
	}
}
?>