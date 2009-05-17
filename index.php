<?php
	include 'Head.inc.php';

$page_file	= "$data_root/".(empty($page_req) ? "1-Home" : $page_req).".page";

if(!is_readable($page_file)) {
//	header('HTTP/1.1 404 Not Found');
	$email_link = email_link("email", 0, "webmaster","$website_name_short $page_req $id  Error whilst following a link from ".$_SERVER["HTTP_REFERER"],0,0,0);
?>
	<div class="center">
	<p>
		You are seeing this page because you are in the page you requested does not exist.
	</p><p>
		If an internal Link sent you here please <?php echo $email_link; ?>
		the <?php echo ( isset($Site_TOClist['AliasList']['Committee']) ? '<a href="Committee#webmaster">Webmaster</a>' : 'Webmaster'); ?> to report the broken link.
	</p><p>
		Please use the button below or your browser's back button to return to the page you were on.
	</p><p>
		Happy browsing!
	</p><p>
		<button onclick="javascript:history.go(-1)">Back</button>
	</div>

<?php } else {
	echo Content::SquidgyParser($page_file);
}
	include 'Foot.inc.php';
?>