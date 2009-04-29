<?php
$debug_info	= "";
$debug		= 0;
$ajax		= 1;	//just to be sure
$allow_logging	= true;

if(!empty($_COOKIE))
	extract($_COOKIE, EXTR_OVERWRITE);

if(!empty($_GET))
	extract($_GET, EXTR_OVERWRITE);

if(!empty($_POST))
	extract($_POST, EXTR_OVERWRITE);

if(empty($site) || !is_readable("Sites/$site/config.inc.php")) {
	if(is_readable("Sites/config.default.php"))
		require_once("Sites/config.default.php");
	else
		exit('Invalid site requested.');
}
$site_root	= "Sites/$site";
$data_root	= "$site_root/Data";

require_once("functions_FSPHP.inc.php");	//contains the File System PHP Gallery functions (both mine and the original ones)
require_once("functions_login.inc.php");	//contains the login functions
require_once("functions_General.inc.php");	//contains my general functions - the file system gallery ones are now separate
require_once($site_root."/config.inc.php");			//these files are now included in all the cathsoc pages since I'm using lots of functions
/*load the required core modules*/
require_once("Modules/FileSystem.php");
require_once("Modules/Module.php");
require_once("Modules/Users.module.php");

if($allow_logging)
	require_once("Modules/Log.php");
else {	//dummy functions to prevent errors
	function log_error($t, $v = '') { }
	function log_info($t, $v = '') { }
	function show_log($t = '') { }
}

$admin_file	= "$data_root/admin.data";	//for the admin page - which sections to show
$pages_file	= "$data_root/pages.data";	//for all pages - the main table of contents
$template_file	= "Sites/Custom_Themes/$site.template";	//the site template

$SquidgyCMS_version	= 0.01;

//things we can expect to be passed, which always need fiddling
if(empty($p))
	unset($p);
else
	$page_req	= urlencode($p);
if(empty($a))
	unset($a);
else
	$art_req	= urlencode($a);

//use cookies only to handle session stuff
$debug_info		.="\n ini_set(\"session.use_only_cookies\", \"1\") = ".ini_set("session.use_only_cookies", "1")."\n<br />";
$debug_info		.="\n ini_set('url_rewriter.tags', '') = ".ini_set('url_rewriter.tags', '')."\n<br />";

$referrer		= isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : "";

if(has_method('FileSystem', "filtered_file_list")) {
	if(is_readable($data_root)) {	//make the page and article arrays
		$GEN_pages	= FileSystem::Filtered_File_List($data_root, ".page");
		$GEN_art	= FileSystem::Filtered_File_List($data_root, ".article");
	} else {
		$GEN_pages	= array();
		$GEN_art	= array();
	}

	// make a list of committee members whose information is available
	$debug_info .= "building \$joblist\n<br />\n";
	$job_list	= FileSystem::Filtered_File_List("$site_root/Users", ".user.php");
	array_push($job_list, "Committee");
	foreach($job_list as $key => $value)
		$job_list[$key]	= str_replace(".", " ", $value);

	$whole_com_elem_id	= array_search("Committee", $job_list);
} else {
	$GEN_pages	= array();
	$GEN_art	= array();
	$job_list	= array();
}

$FSCMS_pages	= array('Contact_Us.php', 'Newsletters.php', 'Photos.php', 'Files.php');	//the ones that the the system provides

$logged_in	= FALSE;	//just in case

if(!empty($logout))
	user_logout();

$logged_in	= user_login();
?>