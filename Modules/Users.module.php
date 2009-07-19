<?php
#name = Users
#description = User managment
#package = Core - required
#type = admin
###

class AdminUsers extends Admin {
	function AdminUsers() {
		parent::__construct(-1, 20);
	}

	function printFormAdmin() { ?>
<table id="admin_users_tbl" class="admin_tbl"><tr>
	<th title="Their user id" class="L">User:</th>
	<th title="Their displayed name" class="M">Name:</th>
	<th title="Their administrative priviledges" class="M">Priviledges:</th>
	<th title="Tick the box to reset the user's password, this cannot be undone" class="T M">Reset Password:</th>
	<th title="Tick the box to delete the user, this cannot be undone" class="T R">Delete:</th>
</tr><?php
		$user_list = Users::list_all();
		global $USER_LEVELS;
		foreach(array_keys($USER_LEVELS) as $level) {
			$level_options[ucwords(strtolower(substr($level, 5)))] = $level;
		}
		foreach($user_list as $id) {
			$User = new User($id);

			$del_box	= '<input type="checkbox" class="tick" name="del['.$id.']" />';
			$reset_box	= '<input type="checkbox" class="tick" name="pass_reset['.$id.']" />';
			$level_box	= $this->get_selectbox('rights', $level_options, array_search($User->auth_level, $USER_LEVELS), 0);

			echo '
<tr>
	<td class="L">'.$id.'</td>
	<td class="M">'.$User->name.'</td>
	<td class="M">'.$level_box.'</td>
	<td class="T M">'.$reset_box.'</td>
	<td class="T R">'.$del_box.'</td>
</tr>';
		}
		echo '
<tr>
	<td class="L">New User:</td>
	<td class="R" colspan="4"><input name="new_user" type="text" /></td>
</tr>';
		?>
</table>
	<?php
		return;
	}

	function reset_pass($who) {
		global $debug_info, $website_name_short, $webmaster_email;

		$file	= Users::file($who);	//convert to a filename type and include
		include $file;
		$error = $this->change_user_file($pass_hash, md5('password'), $file);
		send_mail(email($who), "$who: $website_name_short Website Password Reset", "Dear ".$who
			.",\n\nYour password for the $website_name_long website has been reset to 'password' (without the quotes)."
			."\n\nIf you did not request this and you have not just been elected to the committee then please email the Webmaster ($webmaster_email) and report this error."
			."\n\n$website_name_short Webmaster", "From: $website_name_short Webmaster <$webmaster_email>");
		return $error;
	}

	function change_user_file($old_val, $new_val, $file) {
		global $debug_info;

		$old_val	= "= \"".$old_val;
		$new_val	= "= \"".$new_val;

		$old_file_contents = file_get_contents($file);

		$new_file_contents = str_replace($old_val, $new_val, $old_file_contents);

		$debug_info .= "\$file=$file\n<br />\$new_val=$new_val\n<br />\$old_val=$old_val\n<br />"
				."\$old_file_contents=$old_file_contents\n<br />\$new_file_contents=$new_file_contents\n<br />\n";

		return FileSystem::file_put_contents($file, $new_file_contents, 'w');
	}

	function delete_user($id) {
		return unlink(User::file($id));
	}

	function submit($content=0) {
		list($pass_reset, $del) = array();
		extract($_POST, EXTR_IF_EXISTS);
		global $debug_info, $username, $website_name_short, $webmaster_email;

		$reset_list = $reset_error = $del_list = $del_error = '';

		if(!empty($del)) {
			foreach($del as $user => $val) {
				if(!empty($val) && $this->delete_user($user))
					$del_list .= "\n$user";
				elseif(!empty($val))
					$del_error .= "\n$user";
			}
			if(!empty($del_error))
				$del_error = "\n\nThe following Users' passwords failed: \n$del_error";
			$subject['del'] = "Deletion";
			$body['del'] = "The following users have been deleted successfully:\n\n$del_list $del_error";
		}

		if(!empty($pass_reset)) {
			foreach($pass_reset as $user => $val) {
				if(!empty($val)) {
					$reset_error = $this->reset_pass($user);
					if(empty($reset_error))
						$reset_list .= "\n$user";
					else
						$reset_error .= "\n$user";
				}
			}
			if(!empty($reset_error))
				$reset_error = "\n\nThe following Users' passwords failed: \n$reset_error";
			$subject['reset'] = "Password Reset";
			$body['reset'] = "The following passwords have been reset successfully:\n\n$reset_list $reset_error";
		}

		if(!empty($subject['del']) && !empty($subject['reset'])) {
			$subject = implode(' and ', $subject);
			$body = implode("\n\n", $body);
		} else {
			$subject = implode('', $subject);
			$body = implode('', $body);
		}

		send_mail("Webmaster", "$website_name_short Website User $subject", $body,
				"From: $website_name_short Webmaster <$webmaster_email>");

		return $reset_error.$del_error;
	}
}

class Users {
	function list_all() {
		return FileSystem::Filtered_File_List($GLOBALS['site_root'].'/Users/', '.user.php');
	}
}
?>