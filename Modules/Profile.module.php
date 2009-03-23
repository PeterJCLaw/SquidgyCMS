<?php
#name = Profile
#description = User profiles and public listing thereof
#package = Core - optional
#type = content
#dependencies = Users
###

class AdminProfile extends Admin {
	function AdminProfile() {
		parent::__construct('Change your personal profile');
	}

	function committee_photo_select($curr_img)
	{
		global $debug_info;
		$img_list	= Filtered_Dir_List("Site_Images", "comm_");
		$selected	= "\" selected=\"selected";
		$debug_info .= "\$curr_img=$curr_img\n<br />\n";
		$out	= "";
		foreach($img_list as $image)
		{
			$debug_info .= "\$image=$image\n<br />\n";
			$image	= str_replace(".jpg", "", $image);
			$sel2	= "";
			if($image == str_replace(array("comm_", ".jpg"), "", $curr_img)) {
				$sel2	= $selected;
				$selected	= "";
			}
			$out	.= "<option value=\"$image$sel2\">$image</option>\n				";
		}
		$out	.= "<option value=\"none$selected\">None</option>\n";
		return $out;
	}

	function printFormAdmin() {
		global $debug_info, $username, $site_root;
		include "$site_root/Users/".info_name($username).".comm.php";
		$debug_info	.= "name = '$name', image_path = '$image_path', gender = '$gender', spiel = '$spiel'\n<br />\n";
?>
			<table><tr>
				<th><label for="new_name" title="Your name as you would like it to appear on the site">Name:</label></th>
					<td colspan="2">
						<input class="text" type="text" id="new_name" name="new_name" value="<?php echo stripslashes($name); ?>" title="Your name as you would like it to appear on the site" />
					</td>
					<td rowspan="5" id="pic_cell">
						<a id="pic_preview_link" href="Site_Images/<?php echo comm_pic($image_path); ?>" title="Your image preview">
							<img id="pic_preview" src="Thumbs/<?php echo comm_pic($image_path); ?>" title="Your image preview, click to view larger" alt="Your image preview" width="75px" height="90px" />
						</a>
					</td>
				</tr><tr>
					<th><label for="photo_change" title="Change your picture">Picture:</label></th>
					<td>
						<select id="photo_change" name="photo" title="Change your picture" onchange="change_pic(this.value)">
						<?php echo $this->committee_photo_select($image_path); ?>
						</select>
					</td>
					<td rowspan="4" id="gender_cell">
						<input type="radio" class="radio" id="gender_male" name="n_gender" value="him" <?php echo ($gender == "him" ? "checked=\"checked\" " : ""); ?>/>
						<label for="gender_male">Male</label>
						<br />
						<input type="radio" class="radio" id="gender_female" name="n_gender" value="her" <?php echo ($gender == "her" ? "checked=\"checked\" " : ""); ?>/>
						<label for="gender_female">Female</label>
					</td>
				</tr><tr id="pass_0" style="display: none;">
					<th>Password:</th>
					<td><a class="js_link" onclick="pass_change('show');">Change your password</a></td>
				</tr><tr id="pass_1">
					<th><label for="old_pass" title="Only fill in if changing your password">Old Password:</label></th>
					<td><input class="text" type="password" id="old_pass" name="old_pass"  title="Only fill in if changing your password" /></td>
				</tr><tr id="pass_2">
					<th><label for="new_pass" title="Only fill in if changing your password">New Password:</label></th>
					<td><input class="text" type="password" id="new_pass" name="new_pass"  title="Only fill in if changing your password" /></td>
				</tr><tr id="pass_3">
					<th><label for="confirm_pass" title="Only fill in if changing your password">Confirm Password:</label></th>
					<td><input class="text" type="password" id="confirm_pass" name="confirm_pass"  title="Only fill in if changing your password" /></td>
				</tr></table>
<?php
		$this->printTextarea($spiel);
		return;
	}

	function submit()
	{
		global $new_name, $n_gender, $content, $username, $new_pass, $old_pass, $confirm_pass, $debug_info, $site_root;

		$content	= addslashes($content);	//they get removed by the handler
		$new_name	= addslashes(stripslashes($new_name));	//they get added when sent

		$file = "$site_root/Users/".info_name($username).".comm.php";

		include $file;

		$out_hash	= $pass_hash;
		if(!empty($photo))
			$image_path	= "comm_$photo.jpg";
		else
			$image_path	= "Unknown.jpg";

		if(!empty($old_pass) && !empty($new_pass) && !empty($confirm_pass))	//if they want to change their password and the new password isn't blank
			if(check_pass($username, $old_pass) && ($new_hash = md5($new_pass)) == md5($confirm_pass))	//if the old password is valid & correclty confirmed
				$out_hash	= $new_hash;

		$debug_info	.= @"<b>Password</b> \$out_hash='$out_hash'\n<br />\$new_hash='$new_hash'\n<br />\$pass_hash='$pass_hash'\n<br />
<b>Spiel</b>\n'$content'\n<br />
<b>Photo</b>\n'$image_path'\n<br />
<b>Gender</b>\n'$n_gender'\n<br />
<b>Name</b>\n'$new_name'\n<br />\n";

		$out_val	= "<?php\n\n\$pass_hash	= '$out_hash';\n\n\$image_path	= '$image_path';\n\n\$gender	= '$n_gender';"
					."\n\n\$spiel	= '$content';\n\n\$name	= '$new_name';\n\n?>";

		return  file_put_stuff($file, $out_val, 'w');
	}
}
?>