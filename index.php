<?php
	include 'Head.inc.php';

$page_file	= "$data_root/".(empty($page_req) ? "1-Home" : $page_req).".page";

if(!is_readable($page_file)) {
	echo "The page you requested doesn't exist!\n<br />\nIf an internal link brought you here then please "
			.email_link("email", 'them', "webmaster", "$website_name_short $page_file failure", 0, 0, $debug_info.$error)
			." the Web Master to inform them of this.<br />";
} else {
	echo SquidgyParser($page_file);
}
	include 'Foot.inc.php';
?>