<?php
$debug		= 0;
$ajax		= 1;	//just to be sure
$allow_logging	= true;

if(!empty($_COOKIE))
	extract($_COOKIE, EXTR_OVERWRITE);

if(!empty($_GET))
	extract($_GET, EXTR_OVERWRITE);

if(empty($site) || !is_readable("Sites/$site/config.inc.php")) {
	if(is_readable("Sites/config.default.php"))
		require_once("Sites/config.default.php");
	elseif(is_readable("Sites/default/config.inc.php"))
		$site = 'default';
	else
		exit('Invalid site requested.');
}
$site_root	= "Sites/$site";
$data_root	= "$site_root/Data";

require_once("functions_General.inc.php");	//contains my general functions - the file system gallery ones are now separate
require_once($site_root."/config.inc.php");			//these files are now included in all the cathsoc pages since I'm using lots of functions
/*load the required core modules*/
require_once("Modules/Email.php");
require_once("Modules/FileSystem.php");
require_once("Modules/Inform.php");
require_once("Modules/Module.php");
require_once("Modules/User.php");
require_once("Modules/Content.module.php");
require_once("Modules/Publish.module.php");
require_once("Modules/Site.module.php");
require_once("Modules/Theme.module.php");
require_once("Modules/Users.module.php");

if($allow_logging)
	require_once("Modules/Log.php");
else {	//dummy functions to prevent errors
	function log_error($t, $v = '') { }
	function log_info($t, $v = '') { }
	function show_log($t = '') { }
}

$template_file = Theme::get_site_template();	//the site template

$SquidgyCMS_version	= 0.01;

//things we can expect to be passed, which always need fiddling
if(empty($p))
	unset($p);
else
	$page_req	= urlencode($p);

//use cookies only to handle session stuff
log_info("Global ini_set('session.use_only_cookies', '1')", ini_set('session.use_only_cookies', '1'));
log_info("Global ini_set('url_rewriter.tags', '')", ini_set('url_rewriter.tags', ''));

$referrer		= isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : "";

$_SITE_USER	= new UserLogin();

