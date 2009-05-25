<?php
#name = Contact
#description = Shows a contact form capable of provising the right info for mail_handler.php
#package = Core - optional
#type = content
###

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
class BlockContact extends Block {
	function BlockContact() {
		parent::__construct();
	}

	/* This function returns the tickboxes */
	function tickboxes($item_list, $tick_side = '') {
		global $target, $debug_info, $whole_com_elem_id;
		$left = FALSE;
		if($tick_side == "left")	//if in doubt it goes on the right
			$left = TRUE;

		$out = "<ul class=\"tick_list ".($left ? 'left' : 'right')."\">\n";

		$mini_group	= in_array("Committee", $item_list);

		for($i = 0, $count = 0; $i < count($item_list); $i++) {	//spit as many items as there are
			if($item_list[$i] == "Committee")
				$item	= "Whole Committee";
			else
				$item	= $item_list[$i];

			$check_it	= (($target == $item || ($target == "Whole Committee" && $i < $whole_com_elem_id)) ? ' checked="checked"' : "");
			$onclick	= ($i <= $whole_com_elem_id ? ' onchange="group_tick_2(this)" onclick="group_tick_2(this)"' : "");
			$w_style	= ($i == $whole_com_elem_id ? ' style="font-weight: bold;" id="_Whole_label" for="_Whole"' : "");
			$_whole	= ($i == $whole_com_elem_id ? ' id="_Whole"' : "");

			$debug_info	.= "\$check_it = $check_it\n<br />\$item = $item\n<br />\$target = $target\n<br />\$i = $i\n<br />\$whole_com_elem_id = $whole_com_elem_id\n<br />\n";

			$out .= "	<li><label$w_style>".($left ? '' : $item).'
		<input class="'.($onclick != ''? 'tick_1 ' : '').'tick" type="checkbox" name="target['.$item."]\"$onclick$check_it$_whole />"
					.($left ? $item : "")."\n		</label></li>\n";

		}

		return $out."</ul>\n";
	}


	function block($args) {
		global $debug, $MailingList;
		$tickboxes = $this->tickboxes($job_list, "right");
		$debug_link = $debug ? '?debug=1' : '';
		$out = <<<OUT
<form method="post" action="mail_handler.php$debug_link" id="contact_form" onreset="init()" onsubmit="return Validate_On_Contact_Submit(this)">
<table id="contact_tbl">
	<tr>
		<td colspan="2" class="center">
		<span class="f_right">All fields are required</span>
		<h4>Send an email to the Committee</h4>
$tickboxes
		</td>
	</tr><tr>
		<th><label for="from_name">Name</label></th>
		<td><input id="from_name" name="from_name" class="text_in" type="text" /></td>
	</tr><tr>
		<th><label for="from_email">Email</label></th>
		<td><input id="from_email" name="from_email" class="text_in" type="text" /></td>
	</tr><tr>
		<th><label for="subject">Subject</label></th>
		<td><input id="subject" name="subject" class="text_in" type="text"<?php echo empty($subject) ? '' : " value=\"$subject\""; ?> /></td>
	</tr><tr>
		<th><label for="message">Message</label></th>
		<td><textarea id="message" name="message" class="text_in" rows="6" cols="51"></textarea></td>
	</tr><tr>
OUT;
		if($MailingList['enable'])
			$out .= <<<OUTML
					<td colspan="2" class="center">
						<input class="tick" type="checkbox" name="mailing_list" id="mailing_list" value="mail" />
						<label for="mailing_list">Please include me in the <?php echo "<abbr title=\"$website_name_long\">$website_name_short</abbr>"; ?> mailing list.</label>
					</td>
				</tr><tr>
OUTML;
		$out .= <<<OUTEND
					<td colspan="2" class="center">
						<input class="button" type="reset" value="Reset" />
						<input class="button" id="c_submit" type="submit" value="Send" />
					</td>
				</tr>
			</table>
		</form>
OUTEND;
		return $out;
	}
}
?>