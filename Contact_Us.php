<?php
	$page_scripts	= <<<SCRIPTS
<script type="text/javascript" src="scripts.js"></script>
<script type="text/javascript">
<!-- hide from non js browsers
function init() {
	window.LOG	+= "\\ninitialising";
	setfocus(document.getElementById('from_name'));
	document.getElementById('c_submit').disabled	= "";
}
window.LOG	= add_loader(init);
//-->
</script>
SCRIPTS;
	include 'Head.inc.php';
?>
		<form method="post" action="mail_handler.php" id="contact_form" onreset="init()" onsubmit="return Validate_On_Contact_Submit(this)">
			<table id="contact_tbl">
				<tr>
					<td colspan="2" class="center">
					<span class="f_right">All fields are required</span>
					<h4>Send an email to the Committee</h4>
						<?php print_tickboxes(Users::list_all(), 0, 'Whole Committee', "right"); ?>
					</td>
				</tr><tr>
					<th><label for="from_name">Name</label></th>
					<td><input id="from_name" name="from_name" class="text_in" type="text" /></td>
				</tr><tr>
					<th><label for="from_email">eMail</label></th>
					<td><input id="from_email" name="from_email" class="text_in" type="text" /></td>
				</tr><tr>
					<th><label for="subject">Subject</label></th>
					<td><input id="subject" name="subject" class="text_in" type="text"<?php echo empty($subject) ? '' : " value=\"$subject\""; ?> /></td>
				</tr><tr>
					<th><label for="message">Message</label></th>
					<td><textarea id="message" name="message" class="text_in" rows="6" cols="51"></textarea></td>
				</tr><tr>
<?php if($MailingList['enable']) { ?>
					<td colspan="2" class="center">
						<input class="tick" type="checkbox" name="mailing_list" id="mailing_list" value="mail" />
						<label for="mailing_list">Please include me in the <?php echo "<abbr title=\"$website_name_long\">$website_name_short</abbr>"; ?> mailing list.</label>
					</td>
				</tr><tr>
<?php } ?>
					<td colspan="2" class="center">
						<input type="hidden" name="ReturnURL" value="<?php echo htmlspecialchars($referrer); ?>" />
						<input type="hidden" name="debug" value="<?php echo $debug; ?>" />
						<input class="button" type="reset" value="Reset" />
						<input class="button" id="c_submit" type="submit" value="Send" />
					</td>
				</tr>
			</table>
		</form>
<?php
	include 'Foot.inc.php';
?>