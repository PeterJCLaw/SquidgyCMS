<?php
$target		= "Whole Committee";	//in case none is specified
$_dev		= "";		//the development page flag
$xml_mime	= 0;		//do you want the application/xhtml+xml mime type (assume not, since it breaks under IE6)

	require_once("Global.inc.php");		//contains Global stuff, including config and functions

list($base_href, $this_page, $page_n)	= get_page_basics();

$perm_values	= array('no_google', 'ajax', 'icon_size', 'site');

foreach($perm_values as $key) {
	if(isset($_POST[$key]) || isset($_GET[$key]))
		setcookie($key, $GLOBALS[$key], time()+3600*24*100, $base_href);
}

$page_n		= str_replace("_", " ", $page_n);		//replace any underscores with spaces

if($page_n == "Photos" && isset($dir))	//if its a page that uses the file systems functions
	$place = FileSystem::what_dir_am_i_in($dir);		//if a dir is specified

$page_head_title	= "$website_name_short handler error";
$page_heading	= "Error!";
$page_n == "mail handler" ? 'Contact_Us.php' : 'Admin';	//lie to the page enabling bit

$debug_info	.= @"page_req = '$page_req', this_page = '$this_page'\n<br />\n";

$target = ucwords(str_replace("_", " ", $target));	//for the contact us or admin page
if(empty($target) || $target == "Committee")
	$target	= "Whole Committee";

$referrer	= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $page_n.".php";

if($xml_mime==1)
	header('Content-type: application/xhtml+xml');

//actually print the header stuff
echo content::SquidgyParser($template_file, 0, '[[Block::Site-Content]]').'
	<div id="content" class="maincol">
';

