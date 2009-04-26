<?php
#name = Profile
#description = User profiles and public listing thereof
#package = Core - optional
#type = content
#dependencies = Users
###

class AdminProfile extends Admin {
	function AdminProfile() {
		parent::__construct();
	}

	function printFormAdmin() {
		global $debug_info, $username, $site_root;
		include Users::file($username);
		$debug_info	.= "name = '$name', image_path = '$image_path', gender = '$gender', spiel = '$spiel'\n<br />\n";
?>
			<table><tr>
				<th><label for="new_name" title="Your name as you would like it to appear on the site">Name:</label></th>
					<td colspan="2">
						<input class="text" type="text" id="new_name" name="new_name" value="<?php echo stripslashes($name); ?>" title="Your name as you would like it to appear on the site" />
					</td>
					<td rowspan="5" id="pic_cell">
						<a id="pic_preview_link" href="<?php echo Profile::get_image($image_path, 'orig'); ?>" title="Your image preview">
							<img id="pic_preview" src="<?php echo Profile::get_image($image_path); ?>" title="Your image preview, click to view larger" alt="Your image preview" width="75px" height="90px" />
						</a>
					</td>
				</tr><tr>
					<th><label for="photo_change" title="Change your picture">Picture:</label></th>
					<td>
						<select id="photo_change" name="photo" title="Change your picture" onchange="change_pic(this.value)">
						<?php echo Profile::photo_select($image_path); ?>
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
		global $new_name, $n_gender, $content, $username, $new_pass, $old_pass, $confirm_pass, $photo, $debug_info, $site_root;

		$content	= addslashes($content);	//they get removed by the handler
		$new_name	= addslashes(stripslashes($new_name));	//they get added when sent

		$file = Users::file($username);
		include $file;

		$out_hash	= $pass_hash;
		if(!empty($photo))
			$image_path	= "$photo.jpg";
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

		return FileSystem::file_put_stuff($file, $out_val, 'w');
	}
}

class BlockProfile extends Block {
	function BlockProfile() {
		parent::__construct();
	}

	function ListDiv($args) {
		global $job_list;
		$ret	= '
<table id="comm_tbl">';

		foreach($job_list as $job) {
			if(in_array($job, array('Committee', 'Chaplain')))
				continue;
			$info_name = info_name($job);
			include Users::file($info_name);
			$firstname = first_name($name);
			$ret	.= '<tr>
		<td rowspan="2" class="user_pic">
			<a href="'.Profile::get_image($image_path, 'orig').'" title="Click image to view larger"><img src="'.Profile::get_image($image_path).'" alt="'."$firstname, $job".'" title="Click image to view larger" /></a>
		</td><td>
			<h4 id="'.str_replace(".", "_", $info_name).'" class="user_name">'."$job - ".stripslashes($name).'</h4>
			<span class="user_email">
				'.email_link("email $gender", $gender, $info_name, 0,0,0,0).' or use the <a href="Contact_Us.php?target='.$job.'" title="Use the online contact form">contact form</a>.
			</span>
		</td>
	</tr><tr>
		<td class="user_txt">
			'.stripslashes($spiel) //gets rid of any slashes invoked by (')s
			.'
		</td>
	</tr>';
			}
		return $ret.'
</table>';
	}
}

//utilities class for Profile things
class Profile {
	/* This function determines if the profile picture passed is valid, if it is it returns it, else returns a standin image */
	function get_image($image, $type = 'thumb') {
		global $site_root;
		//check that we've been passed a sane image, correct it if not
		if(empty($image) || strpos($image, '.jpg') === FALSE)
			$image = "Unknown.jpg";
		switch($type) {
			case 'orig':	//check the original file
				$path = "$site_root/Users/$image";
				break;
			case 'thumb': //check the thumbnail
			default:
				$path = "$site_root/Users/Thumbs/$image";
		}
		if(!is_readable($path))
			$path	= "Site_Images/Unknown.jpg";

		return $path;
	}

	/*this function generates the photo select box*/
	function photo_select($curr_img)
	{
		global $debug_info, $site_root;
		$img_list	= FileSystem::Filtered_File_List("$site_root/Users", ".jpg");
		$selected	= '" selected="selected';
		$debug_info .= "\$curr_img=$curr_img\n<br />\n";
		$out	= "";

		foreach($img_list as $image) {
			if($image == 'Unknown')	//this is the default image
				continue;
			$debug_info .= "\$image=$image\n<br />\n";
			if($image == str_replace(".jpg", "", $curr_img)) {
				$out	.= "<option value=\"$image$selected\">$image</option>\n				";
				$selected = '';
			} else
				$out	.= "<option value=\"$image\">$image</option>\n				";
		}
		$out	.= "<option value=\"none$selected\">None</option>\n";
		return $out;
	}

}
?>