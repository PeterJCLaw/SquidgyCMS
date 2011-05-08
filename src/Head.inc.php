<?php
	require_once("Global.inc.php");		//contains Global stuff, including config and functions

list($base_href, $this_page, $page_n)	= get_page_basics();

$page_n		= str_replace("_", " ", $page_n);		//replace any underscores with spaces

$page_head_title	= "$website_name_short handler error";
$page_heading	= "Error!";
$page_n == "mail handler" ? 'Contact_Us.php' : 'Admin';	//lie to the page enabling bit

//actually print the header stuff
echo Content::SquidgyParserFile($template_file, 0, '[[Block::Site-Content]]').'
	<div id="content" class="maincol">
';

