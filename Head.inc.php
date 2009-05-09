<?php
$target		= "Whole Committee";	//in case none is specified
$_dev		= "";		//the development page flag
$xml_mime	= 0;		//do you want the application/xhtml+xml mime type (assume not, since it breaks under IE6)

	require_once("Global.inc.php");		//contains Global stuff, including config and functions

$Site_TOClist	= get_TOClist();
multi2dSortAsc($Site_TOClist, 'weight');

list($base_href, $this_page, $page_n)	= get_page_basics();

$perm_values	= array('no_google', 'ajax', 'icon_size', 'site');

foreach($perm_values as $key) {
	if(isset($_POST[$key]) || isset($_GET[$key]))
		setcookie($key, $GLOBALS[$key], time()+3600*24*100, $base_href);
}

$page_n		= str_replace("_", " ", $page_n);		//replace any underscores with spaces

if($page_n == "Photos" && isset($dir))	//if its a page that uses the file systems functions
	$place = FileSystem::what_dir_am_i_in($dir);		//if a dir is specified

if($page_n == "index") {	//if we're on the index page - we're going to serve a user page
	$page_n	= "Home";
	if(empty($page_req))
		$page_req	= "1-Home";
	if(array_key_exists('AliasList', $Site_TOClist) && array_key_exists($page_req, $Site_TOClist['AliasList']))	//if its a valid alias modify to a GEN_pages id
		$page_req	= $Site_TOClist['AliasList'][$page_req];

	if(array_key_exists($page_req, $Site_TOClist) && in_array($page_req, $GEN_pages)) {	//check that the page exists & is enabled (user pages only)
		$page_n	= get_GEN_title($page_req);
		$page_edit_link	= TRUE;
	}
}

switch ($page_n)	//page name
{
	case "admin handler":
	case "mail handler":
		$page_head_title	= "$website_name_short handler error";
		$page_heading	= "Error!";
		$this_page	= $page_n == "mail handler" ? 'Contact_Us.php' : 'Admin';	//lie to the page enabling bit
		break;
	case "Admin":
		$page_head_title	= "$website_name_short - Administration";	//the page title
		$page_heading	= "Administration";
		$this_page	= 'Admin';
		break;
	case "Home":
		$page_heading	= $page_head_title	= $website_name_long;
		$this_page	= 'Home';
		break;
	default:
		$page_head_title	= "$website_name_short - $page_n";	//the page title (in the head section)
		$page_heading	= $page_n;	//the title at the top of the page
		$this_page	= (empty($page_req) ? str_replace(" ", "_", $page_n).".php" : $page_req);
		break;
}

$debug_info	.= @"page_req = '$page_req', this_page = '$this_page'\n<br />\n";

$target = ucwords(str_replace("_", " ", $target));	//for the contact us or admin page
if(!in_array($target, $job_list) || $target == "Committee")
	$target	= "Whole Committee";

$referrer	= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $page_n.".php";

if($xml_mime==1)
	header('Content-type: application/xhtml+xml');

//actually print the header stuff
echo SquidgyParser($template_file, 0, '[[Block::Site-Content]]').'
	<div id="content" class="maincol">
';

if(!empty($debug) && $debug > 1) {
	print_r($Site_TOClist);
}

if(!isset($Site_TOClist[$this_page]) && !$debug) {
	echo 'This page is under not available - it might be <span title="Come back later to see the vast improvemnts being implemented!">under development</span> '
		.'or it may have been disabled by the Webmaster.';
	include 'Foot.inc.php';
	exit();
}