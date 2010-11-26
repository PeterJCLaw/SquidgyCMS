<?php
	$ReturnURL	= 'Contact_Us.php';
	require_once("Global.inc.php");		//contains Global stuff, including config and functions

$error	= "";
$to	= array();
if($debug)
	$header	= "Cc: DEBUG Webmaster <".email_addr("webmaster").">"."\r\n";
else
	$header	= '';

if(empty($subject))
	$error	.= "\nPlease include a subject.\n<br />";

if(empty($message))
	$error	.= "\nPlease include a message.\n<br />";

if(empty($from_name))
	$error	.= "\nPlease include your name.\n<br />";

if(empty($from_email))
	$error	.= "\nPlease include your email address.\n<br />";

if(!empty($target["Whole Committee"]))	//if they want to mail everyone then we may as well use the mailing list
{
	array_push($to, "Committee");
	$message	.= "\n\nReply-To: \"$from_name\" <$from_email>";

	if($target["Chaplain"])			//if the Chaplain has been included then include them on the to line
		array_push($to, "Chaplain");

	$debug_info	.= @"\$target[Chaplain]=".$target["Chaplain"]."\n<br />";
}
elseif(!empty($target))
	foreach($target as $key => $val)	//cycle through everyone they might want to send a mail to, to see if they do
	{
		if($val)	//if theres a match then include them on the to line
			array_push($to, $key);

		$debug_info	.= "\$target[$key]=$target[$key]=$val\n<br />";
	}
else
	$error	.= "\nPlease include a recipient for your email.\n<br />";

if(empty($error)) {
	$message	.= "\n\n--\n"."X-Mailer: PHP/".phpversion();	//mail signature, including php version
	$header	.= "From: $website_name_short Web Form <$website_form_email>\r\nReply-To: $from_name <$from_email>\r\n";

	if($MailingList['enable'] && !empty($mailing_list) && $mailing_list == "mail") {	//if they want in on the mailing list
		$message	.= "\nThe mail sender has requested to be included in the $website_name mailing list: ";

		if($MailingList['subscribe']) {
			if(isset($MailingList['password']) && $MailingList['password'] !== FALSE)
				$app	= "approve ".$MailingList['password'].' ';

			mail('majordomo@lists.soton.ac.uk', '', $app."subscribe ".$MailingList['list-name']." $from_email", $header);
		} else
			$message	.= "this email has been copied to the Webmaster.";

		if(!in_array("committee", $to) && !in_array("webmaster", $to) && strpos($header, "web") === FALSE)	//don't mail the webmaster if he's already getting a copy
			$header	.= "Cc: $website_name Webmaster <".email_addr("webmaster").">"."\r\n";

	}

	if(!send_mail($to, stripslashes($subject), stripslashes($message), $header))	//send the mail, checking for errors
		$error	= "\nYour email failed to send. please see \$debug_info for more information";
	else
		$debug_info .= "\nmail() reported no errors.\n<br />";
}
$debug_info	.= @"\$to=$to\n<br />\$subject=$subject\n<br />\$message=$message\n<br />\$header=$header\n<br />";

if(strpos($ReturnURL, "?") === FALSE)
	$success	= "?success=1";
else
	$success	= "&success=1";

if(!empty($error) || $debug)
	include "handler.php";
else
	header("Location: $ReturnURL$success");
