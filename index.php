<?php
require_once('Global.inc.php');

list($base_href) = get_page_basics();

//figure out which page they want, this returns the id
$page_info = Site::get_requested_id_and_alias();
$page_alias = $page_info['alias'];
$page_id = $page_info['id'];
$content = '';
$script_files = array();
$script_code = '';

if($page_id == 'admin') {
	$page_head_title	= "$website_name_short - Administration";	//the page title
	$page_heading	= "Administration";
	ob_start();
	include 'Admin.php';
	$content = ob_get_contents();
	ob_end_clean();
} else {
	$chunk_file = Content::get_file_from_id($page_id);
	if($page_id === FALSE || $chunk_file === FALSE) {
		header('HTTP/1.0 404 Not Found');
		log_info('404 error', array('page_info' => $page_info, 'chunk_file' => $chunk_file));
		$email_link = email_link("email", 0, "webmaster","$website_name_short $_GET[s] $id  Error whilst following a link from ".$_SERVER["HTTP_REFERER"],0,0,0);
		$content = <<<MSG
<div class="center">
<p>
	You are seeing this page because you are in the page you requested does not exist.
</p><p>
	If an internal Link sent you here please $email_link the Webmaster to report the broken link.
</p><p>
	Please use the button below or your browser's back button to return to the page you were on.
</p><p>
	Happy browsing!
</p><p>
	<button onclick="javascript:history.go(-1)" title="Retrun to the previous page">Back</button>
</div>
MSG;
	$page_head_title	= "$website_name_short - Error";
	$page_heading	= 'Error!';
	} else {
		$chunk_title = Content::get_title_from_id($page_id);
		$content = Content::SquidgyParser($chunk_file);

		if($page_alias == '<home>')
			$page_heading	= $page_head_title	= $website_name_long;
		else {
			$page_head_title	= "$website_name_short - $chunk_title";	//the page title (in the head section)
			$page_heading	= $chunk_title;	//the title shown at the top of the page
		}
		$page_scripts = '';
		foreach($script_files as $file)
			$page_scripts .= '<script type="text/javascript" src="'.$file.'"></script>';
		if(!empty($script_code))
			$page_scripts .= <<<PGSCRPT
<script type="text/javascript">
<!-- hide from non js browsers
$script_code
//-->
</script>
PGSCRPT;

	}
}

echo content::SquidgyParser($template_file, 0, '[[Block::Site-Content]]');
echo <<<CONT
	<div id="content" class="maincol">
$content
	</div><!-- end content div -->

CONT;
echo content::SquidgyParser($template_file, '[[Block::Site-Content]]');

if($debug < 0) {
	echo $debug_info;
	echo show_log();
}
?>