<?php
#name = Contact
#description = Creates a contact form & mechanisms to handle sending of emails.
#package = Core - optional
#type = content
###

class BlockContact extends Block {
	function BlockContact() {
		parent::__construct();
		$SCRIPT	= <<<SCRIPTS
function init() {
	window.LOG	+= "\\ninitialising";
	setfocus(document.getElementById('from_name'));
	document.getElementById('c_submit').disabled	= "";
}
window.LOG	= add_loader(init);
SCRIPTS;
		add_script('file', 'scripts.js');
		add_script('code', $SCRIPT);
	}

	/* This function provides tickboxes which can be linked to an all box */
	function get_tickboxes($item_list, $ticked_list, $all_box=FALSE, $tick_side = '') {
		if(!is_array($ticked_list))
			$ticked_list = array();

		$left = FALSE;
		if($tick_side == "left")	//if in doubt it goes on the right
			$left = TRUE;

		$onclick	= empty($all_box) ? '' : ' onchange="group_tick_2(this, \'_all\')" onclick="group_tick_2(this, \'_all\')"';
		$check_it = '';
		if(in_array('_all', $ticked_list))
			$check_it	= 'checked="checked" ';

		$out = '<ul class="tick_list '.($left ? 'left' : 'right')."\">\n";

		foreach($item_list as $item) {	//spit as many items as there are
			if(in_array($item, $ticked_list))
				$check_it	= 'checked="checked" ';

			$out .= "	<li><label>".($left ? '' : $item).'
			<input class="'.(empty($all_box) ? '' : 'tick_1 ').'tick" type="checkbox" name="target['.$item."]\"$onclick $check_it/>"
				.($left ? $item : '')."\n	</label></li>\n";
		}

		if(!empty($all_box))
			$out .= '	<li><label id="_all_label" for="_all"><strong>'.($left ? '' : $all_box).'
			<input class="tick_1 tick" type="checkbox" name="target[_all]" id="_all"'."$onclick $check_it/>"
				.($left ? $all_box : '')."\n	</strong></label></li>\n";

		return $out."</ul>\n";
	}

	function block($args) {
		list($all_label, $side) = array('Everyone', "right");
		extract($args, EXTR_IF_EXISTS);

		global $debug, $MailingList, $website_name_long, $website_name_short;


		$queryParams = array('type' => 'block', 'module' => 'Contact');
		if ($debug)
		{
			$queryParams['debug'] = $debug;
		}

		$actionUrl = 'ajax.php';
		$query = toQueryString($queryParams);
		$actionUrl .= '?'.$query;

		$debug_link = $debug ? '?debug=1' : '';
		$tickboxes = $this->get_tickboxes(Users::list_all(), 0, $all_label, $side);
		$subject = empty($_GET['subject']) ? '' : 'value="'.$_GET['subject'].'"';
		$out = <<<OUT
<form method="post" action="$actionUrl" id="contact_form" onreset="init()" onsubmit="return Validate_On_Contact_Submit(this)">
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
		<td><input id="subject" name="subject" class="text_in" type="text"$subject /></td>
	</tr><tr>
		<th><label for="message">Message</label></th>
		<td><textarea id="message" name="message" class="text_in" rows="6" cols="51"></textarea></td>
	</tr><tr>
OUT;
		if($MailingList['enable'])
			$out .= <<<OUTML
					<td colspan="2" class="center">
						<input class="tick" type="checkbox" name="mailing_list" id="mailing_list" value="mail" />
						<label for="mailing_list">Please include me in the <abbr title="$website_name_long">$website_name_short</abbr> mailing list.</label>
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

	function ajax()
	{
		$this->handleSendEmail();
		$inform = Inform::getInstance();
		echo $inform->asJSON();
	}

	function callback()
	{
		$this->handleSendEmail();
	}

	function handleSendEmail()
	{
		$requiredVars = array(
			'subject' => 'a subject',
			'message' => 'a message',
			'from_name' => 'your name',
			'from_email' => 'your email address',
			'target' => 'a recipient for your email'
		);

		$gotVars = array();
		$inform = Inform::getInstance();
		$error = False;

		foreach ($requiredVars as $var => $niceName)
		{
			if (!empty($_POST[$var]))
			{
				$gotVars[$var] = $_POST[$var];
			}
			else
			{
				$error = True;
				$inform->error("Please include $niceName.");
			}
		}

		$to = array();
		$target = array();
		log_info('Contact->ajax; $gotVars:', $gotVars);
		extract($gotVars, EXTR_OVERWRITE);
		// Cycle through everyone they might want to send a mail to, to see if they do
		foreach($target as $key => $val)
		{
			if ($val)
			{
				array_push($to, $key);
			}
		}

		if(!$error)
		{
			global $website_form_email, $website_name_short;
			// Add a mail signature, including php version
			$message .= "\n\n-- \nX-Mailer: PHP/".phpversion();

			$email = new Email();
			$email->set_from($website_form_email, $website_name_short.' Web Form');
			$email->set_replyTo($from_email, $from_name);
			$email->set_body(stripslashes($message));
			$email->set_subject(stripslashes($subject));

			// send the mail, checking for errors
			if(!$email->send())
			{
				// TODO: handle this better!
				$inform->error('Sorry, your email failed to send');
			}
			else
			{
				log_info("Successfully sent email", $email);
			}
		}
		log_info('Contact->ajax;', get_defined_vars());
	}
}
