<?php
#name = User
#description = User class
#package = Core - required
#type = admin
###

/* These functions are currently still reliant on info_name() and email(), which are elsewhere */
$username = $_POST['username'];

define(USER_ADMIN, 5);
define(USER_DEVEL, 4);
define(USER_EDITOR, 3);
define(USER_SIMPLE, 2);
define(USER_GUEST, 1);

class User {
	function __construct($id) {
		//load up the info on the user
		$this->id = strtolower($id);
		$this->data_file = $this->file($this->id);
		//this would load the info if it uses the SquidgyCMS data format...
		list($this->data) = FileSystem::get_file_assoc($this->data_file);

		foreach($this->data as $property => $value) {
			$this->$property = $value;
		}
	}

	function User($id) {
		return $this->__construct($id);
	}

	function file($n) {
		return $GLOBALS['site_root'].'/Users/'.info_name($n).'.user.php';
	}

	function has_auth($level) {
		return $this->auth_level >= $level;
	}


	/* This function prints the logon form on any page its needed */
	function print_logon_form() {
		global $username, $debug_info;

		$debug_info	.= "(login_form)\$username=$username\n<br />\n";
?>
<form id="login_form" method="post" action="" onsubmit="this.action = window.location.hash;">
<table id="login"><?php
	if(!empty($username)) {	?>
<caption id="login_fail">Invalid Username or Password!</caption>
<?php } ?>
<tr>
	<th><label for="username">Username:</label></th>
	<td>
		<input type="text" id="username" name="username"<?php echo (!empty($username) ? " value=\"$username\"" : ""); ?> class="text" />
	</td>
</tr><tr>
	<th><label for="login_pass">Password:</label></th>
	<td><input type="password" id="login_pass" name="login_pass" class="text" /></td>
</tr><tr>
	<th><label for="remember_me">Remember Me:</label></th>
	<td><input type="checkbox" id="remember_me" name="remember_me" /></td>
</tr><tr>
	<td colspan="2" class="center">
		<input type="submit" id="login_button" name="login_button" value="Login" />
	</td>
</tr></table>
</form>
<?php }
}

class UserLogin extends User {
	/* This function compares the user to the list in the array $users_arr that's in config.inc.php then logs them in, and creates the cookie etc */
	function UserLogin() {
		$this->logged_in = FALSE;
		list($login_pass, $remember_me) = array();
		extract($_POST, EXTR_IF_EXISTS);
		global $debug_info, $username, $job_list, $cookie_name, $base_href;
		$email_list	= array();

		foreach($job_list as $key => $value) {	//make a list of their usernames - all are lowacase versions of their email uniques
			$email_list[$key]	= strtolower(email($value));	//make them all lowercase
			$debug_info	.= "\$email_list[$key]=".$email_list[$key]."\n<br />";
		}

		$debug_info	.= "(user_login)\$username=$username\n<br />\n";

		if($remember_me)
			$debug_info	.= "Remember Me is on\n<br />\n";

		session_start();	//start the php session, just in case
		$username	= strtolower($username);	//username should be in lowercase, but force it anyway

		if(!empty($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] != "") {	//if they have already logged in and we're propogating a cookie session
			$debug_info	.= "\$_COOKIE[$cookie_name]=".$_COOKIE[$cookie_name]."\n<br />\n";
			if($_COOKIE[$cookie_name] != "committee" && in_array($_COOKIE[$cookie_name], $email_list))	//in_array, just to be sure, 'committee' doesn't have a login
			{
				$username = $_COOKIE[$cookie_name];	//recover the username from the cookie
				$this->logged_in = true;
			}
		} elseif(!empty($_SESSION['user']) /* && $_SESSION['pass_hash'] == $this->pass_hash somehow make this work!*/) {	//if they have already logged in and we're propogating a session
			$debug_info	.= "\$_SESSION['user']=".$_SESSION['user']."\n<br />\n";
			if($_SESSION['user'] != "committee" && in_array($_SESSION['user'], $email_list)) {	//in_array, just to be sure, 'committee' doesn't have a login
				$username = $_SESSION['user'];	//recover the username from the session
				$this->logged_in = true;
			}
		}

		if(!in_array($username, array("committee", "chaplain")) && in_array($username, $email_list)) {	//if they're loggin in for the first time this session, 'committee' and 'chaplain' don't have logins
			if(check_pass($username, $login_pass) && $login_pass != '') {
				if($remember_me)
					setcookie($cookie_name, $username, time()+(60*60*24)*100, $base_href);	//expire in 100 days
				else
					$_SESSION['user']	= $username;

				$debug_info	.= "\$username=$username\n<br />\n";
				$this->logged_in = true;
			}
		}
	}

	/* This function logs the user out */
	function logout() {
		global $debug, $debug_info, $username, $referrer, $success, $website_name, $cookie_name, $base_href;

		session_start();	//start the php session - neccesary i think

		$_SESSION['user'] = $username = "";	//unset all indications of the user being logged in
		if(isset($_COOKIE[$cookie_name]))
			setcookie($cookie_name, "", time()-7200, $base_href);	//set a blank cookie that has already expired

		$ref	= str_replace("success=1", "", $referrer);
		$ref	= str_replace("success=0", "", $ref);
		$ref	= str_replace("logout=1", "", $ref);
		$debug_info	.= "User has been logged out\n<br />\$ref=$ref\n<br />\n";
		header("Location: $ref");
		return;
	}

	/* This function compares the password to the list in the array $users_arr that's in config.inc.php */
	function check_pass($username, $login_pass) {
		global $debug_info, $site_root;
		$debug_info	.= "(check_pass)\$username=$username\n<br />\n";

		include $this->data_file;

		if($pass_hash == md5($login_pass))
			return TRUE;
		else
			return FALSE;
	}
}
?>