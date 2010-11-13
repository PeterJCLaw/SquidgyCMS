<?php
	include 'Head.inc.php';
?>
<?php
$referrer	= array_shift(explode('?', basename($referrer)));

if(empty($error))
	$error	= "\nAn unknown error occured";

echo "You generated an error! Please "
	.email_link("email", "them", "webmaster", "$website_name_short/handler.php error", 0, 0, $debug_info.$error)
	." the Web Master if you believe that the script was in error.\n<br />The details of the error are:
	<p id=\"error\" style=\"margin: 3px; padding: 7px; background-color: #FFB6C1;\">".nl2br($error)."\n</p>";

?>
	Once you have feel free to <a href="<?php echo $referrer."?success=0"; ?>" title="Did you email the webmaster?">return</a> to the page you were on page.
<?php
	include 'Foot.inc.php';
?>