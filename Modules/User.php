<?php
#name = User
#description = User class
#package = Core - required
#type = admin
###

/* These functions are currently still reliant on info_name() and email(), which are elsewhere */

$USER_LEVELS = array('USER_ADMIN' => 5, 'USER_DEVELOPER' => 4, 'USER_EDITOR' => 3, 'USER_SIMPLE' => 2, 'USER_GUEST' => 1);
foreach($USER_LEVELS as $U_level => $U_val) {
	define($U_level, $U_val);
}

class User {
	function __construct($id) {
		//load up the info on the user
		$this->id = strtolower($id);
		$this->data_file = $this->file($this->id);
		/* this would load the info if it uses the SquidgyCMS data format...
		list($this->data) = FileSystem::get_file_assoc($this->data_file);

		foreach($this->data as $property => $value) {
			$this->$property = $value;
		}
		*/
		//include the file, this is the way until I fix it
		include $this->data_file;
		$this->pass_hash = $pass_hash;
		$this->image_path = $image_path;
		$this->gender = $gender;
		$this->spiel = $spiel;
		$this->name = $name;
		$this->auth_level = empty($auth_level) ? USER_GUEST : $auth_level;
		$this->_changed = FALSE;
	}

	function User($id) {
		return $this->__construct($id);
	}

	function get_first_name() {
		return first_word($this->name);
	}

	function validate_id($id) {
		return is_file($this->file($id)) && is_readable($this->file($id));
	}

	function file($n) {
		return $GLOBALS['site_root'].'/Users/'.info_name($n).'.user.php';
	}

	function has_auth($level) {
		return $this->auth_level >= $level;
	}

	function has_auth_type($type) {
		switch($type) {
			case 'system':
				return FALSE;
			case 'admin':
				$level = USER_ADMIN;
				break;
			case 'content':
				$level = USER_EDITOR;
				break;
			default:
				$level = USER_SIMPLE;
		}
		return $this->has_auth($level);
	}

	function reset_password() {
		$this->set_property('pass_hash', md5('password'));
	}

	function delete() {
		if(!$GLOBALS['_SITE_USER']->has_auth(USER_ADMIN) && $GLOBALS['_SITE_USER']->id != $this->id) {
			log_error('You do not have the rights to remove users other than your own');
			return FALSE;
		}
		if($this->id != $GLOBALS['_SITE_USER']->id)
			return unlink($this->file);
		log_error('You cannot remove your own user account at the moment');
		return FALSE;
	}

	function set_property($property, $value) {
		if($property[0] == '_') {
			log_error('you cannot change private varaibles');
			return FALSE;
		}
		/*
		 * check that the logged in user has the rights to change things
		 * you can mod your own detils, but not your auth level, unlesss you're an admin
		 */
		if( ($GLOBALS['_SITE_USER']->id == $this->id && $property != 'auth_level')
		  || $GLOBALS['_SITE_USER']->has_auth(USER_ADMIN) ) {
			$this->$property = $value;
			$this->_changed = true;
			return true;
		}
		return FALSE;
	}

	function save() {
		if(!$this->_changed)
			return true;
		$save_properties = array('pass_hash', 'image_path', 'gender', 'spiel', 'name');
		$out = "<?php\n\n";
		foreach($save_properties as $property) {
			$out .= "\$$property = ".var_export($this->$property, true).";\n\n";
		}
		$out .= "\$auth_level = ".substr(var_export($this->$property, true), 1, -1).";\n\n";
		$out .= '?>';
		return FileSystem::file_put_contents($this->data_file, $out);
	}

	/* This function prints the logon form on any page its needed */
	function print_logon_form() {
		global $debug_info;
		$username = $_POST['username'];
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
	/* This function initiates the object, logs the user in if appropriate and creates the cookie etc */
	function UserLogin() {
		session_start();	//start the php session, just in case
		$this->logged_in = FALSE;
		global $debug_info, $cookie_name, $base_href;

		if(!empty($_GET['logout'])) {	//do a logout if requested
			$this->logout();
			return;
		}

		if(!empty($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name.'_hash'])) {	//cookie session - recover stuff from the cookie
			$username = $_COOKIE[$cookie_name];
			$hash = $_COOKIE[$cookie_name.'_hash'];
			$type = 'cookie';
		} elseif(!empty($_SESSION['user']) && !empty($_SESSION['hash'])) {	//PHPSESSION session - recover the username from the session
			$username = $_SESSION['user'];
			$hash = $_SESSION['hash'];
			$type = 'session';
		} else {	//possibly a new login
			$username = strtolower($_POST['username']);	//username should be in lowercase, but force it anyway
			$hash = md5($_POST['login_pass']);
			$remember_me = $_POST['remember_me'];
			$type = 'new';
		}

		$debug_info	.= "(UserLogin)\$username=$username\n<br />\n";

		if(empty($username) || !$this->validate_id($username))	//no login attempt or bad username
			return;

		//since we know that the username is valid we can go ahead and grab the bits from the parent object, this brings in the file info
		parent::__construct($username);

		if(!empty($remember_me))
			$debug_info	.= "Remember Me is on\n<br />\n";

		if($hash == $this->pass_hash && !empty($hash) && !empty($this->pass_hash))	//check the password
			$this->logged_in = true;

		if($type == 'new')
			if(!empty($remember_me)) {	//if they want: set a cookie to expire in 100 days, only valid for this sub-site
				setcookie($cookie_name, $this->id, time()+(60*60*24)*100, $base_href);
				setcookie($cookie_name.'_hash', $this->pass_hash, time()+(60*60*24)*100, $base_href);
			} else {	//set sessions variables
				$_SESSION['user']	= $this->id;
				$_SESSION['hash']	= $this->pass_hash;
			}

		return;
	}

	/* This function checks that the user is logged in */
	function is_logged_in() {
		return $this->logged_in;
	}

	/* This function logs the user out */
	function logout() {
		global $debug_info, $username, $referrer, $cookie_name, $base_href;

		$_SESSION['hash'] = $_SESSION['user'] = $username = "";	//unset all indications of the user being logged in
		if(isset($_COOKIE[$cookie_name]))
			setcookie($cookie_name, "", time()-7200, $base_href);	//set a blank cookie that has already expired
		if(isset($_COOKIE[$cookie_name.'_hash']))
			setcookie($cookie_name.'_hash', "", time()-7200, $base_href);

		$ref	= str_replace("success=1", "", $referrer);
		$ref	= str_replace("success=0", "", $ref);
		$ref	= str_replace("logout=1", "", $ref);
		$debug_info	.= "User has been logged out\n<br />\$ref=$ref\n<br />\n";
		header("Location: $ref");
		return;
	}
}
?>