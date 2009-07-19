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
		global $debug_info;
		$user = new UserProfile($GLOBALS['_SITE_USER']->id);
		$debug_info	.= "user = '".print_r($user, true);"'\n<br />\n";
?>
<table><tr>
	<th><label for="new_name" title="Your name as you would like it to appear on the site">Name:</label></th>
	<td colspan="2">
		<input class="text" type="text" id="new_name" name="new_name" value="<?php echo $user->name; ?>" title="Your name as you would like it to appear on the site" />
	</td>
	<td rowspan="5" id="pic_cell">
		<a id="pic_preview_link" href="<?php echo $user->get_image('orig'); ?>" title="Your image preview">
			<img id="pic_preview" src="<?php echo $user->get_image(); ?>" title="Your image preview, click to view larger" alt="Your image preview" width="75px" height="90px" />
		</a>
	</td>
</tr><tr>
	<th><label for="photo_change" title="Change your picture">Picture:</label></th>
	<td>
		<?php echo $this->photo_select($user->image_path); ?>
	</td>
	<td rowspan="4" id="gender_cell">
		<input type="radio" class="radio" id="gender_male" name="n_gender" value="him" <?php echo ($user->gender == "him" ? "checked=\"checked\" " : ""); ?>/>
		<label for="gender_male">Male</label>
		<br />
		<input type="radio" class="radio" id="gender_female" name="n_gender" value="her" <?php echo ($user->gender == "her" ? "checked=\"checked\" " : ""); ?>/>
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
		$this->printTextarea($user->spiel);
		return;
	}

	/*this function generates the photo select box*/
	function photo_select($curr_img) {
		global $debug_info;
		$img_list	= FileSystem::Filtered_File_List("$this->site_root/Users", ".jpg");
		$debug_info .= "\$curr_img=$curr_img\n<br />\n";
		return $this->get_selectbox('photo" id="photo_change" title="Change your picture" onchange="change_pic(this.value);', $img_list, str_replace(".jpg", "", $curr_img));
	}

	function submit($content=0) {
		list($new_name, $n_gender, $new_pass, $old_pass, $confirm_pass, $photo) = array();
		extract($_POST, EXTR_IF_EXISTS);
		global$debug_info, $username;

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

		return FileSystem::file_put_contents($file, $out_val, 'w');
	}
}

class BlockProfile extends Block {
	function BlockProfile() {
		parent::__construct();
	}

	function ListDiv($args) {
		$ret	= '
<table id="comm_tbl">';
		$user_list = Users::list_all();

		foreach($user_list as $id) {
			$user = new UserProfile($id);
			$ret	.= '<tr>
	<td rowspan="2" class="user_pic">
		<a href="'.$user->get_image('orig').'" title="Click image to view larger"><img src="'.$user->get_image().'" alt="'."$firstname, $id".'" title="Click image to view larger" /></a>
	</td><td>
		<h4 id="'.str_replace(".", "_", $info_name).'" class="user_name">'."$id - ".stripslashes($user->name).'</h4>
		<span class="user_email">
			'.email_link("email $user->gender", $user->gender, $info_name, 0,0,0,0).' or use the <a href="Contact_Us.php?target='.$id.'" title="Use the online contact form">contact form</a>.
		</span>
	</td>
</tr><tr>
	<td class="user_txt">
		'.stripslashes($user->spiel) //gets rid of any slashes invoked by quote marks or similar
		.'
	</td>
</tr>';
		}
		return $ret.'</table>';
	}
}

//User Profile object, built on the main User object
class UserProfile extends User {
	function UserProfile($id) {
		parent::__construct($id);
	}

	/* This function determines if the profile picture is valid, if so it returns it's path, else returns the path to a stand in image */
	function get_image($type = 'thumb') {
		global $site_root;
		//check that we've been passed a sane image, correct it if not
		if(empty($this->image_path) || strpos($this->image_path, '.jpg') === FALSE)
			$this->image_path = "Unknown.jpg";
		switch($type) {
			case 'orig':	//check the original file
				$path = "$site_root/Users/$this->image_path";
				break;
			case 'thumb': //check the thumbnail
			default:
				$path = "$site_root/Users/Thumbs/$this->image_path";
		}
		if(!is_readable($path))
			$path	= "Site_Images/Unknown.jpg";

		return $path;
	}
}
?>