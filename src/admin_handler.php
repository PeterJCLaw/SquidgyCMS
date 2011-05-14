<?php
	require_once("Global.inc.php");	//contains the File System PHP Gallery functions (both mine and the original ones)

$header_link	=	$error	= "";

//check that they're logged in and have the authority to view the page
if(!$_SITE_USER->is_logged_in() || !$_SITE_USER->has_auth(USER_SIMPLE)) {
	if($_SITE_USER->is_logged_in())
		echo 'You do not have sufficient priviledges to view this page.';
	else
		$_SITE_USER->print_logon_form();
	include "Foot.inc.php";
	exit();
}

$type	= str_replace(" ", "_", ucwords($_POST['type']));
if(empty($type))
	exit('No edit type defined!');

//strip slashes and convert php opening or closing tags to their html equivalents to prevent malicious code from running
if(!empty($_POST['admin_content']))
	$admin_content	= stripslashes(str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $_POST['admin_content']));

log_info(null, array('type' => $type, 'content' => $content));

if($debug) {
	echo "\n<br />POST:\n<br />";
	print_r($_POST);
	echo "\n<br />GET:\n<br />";
	print_r($_GET);
}

require_once("Modules/Module.php");

if(is_readable("Modules/$type.module.php"))
	require_once("Modules/$type.module.php");

$class_name	= "Admin$type";

if(!class_exists($class_name))
	$error	= "\nUnrecognised edit type!\n<br />\n";
else {
	$type_obj	= new $class_name();

	log_info("Class $type exists!", $type_obj->get_my_class());

	$error	= $type_obj->submit($admin_content);

	$header_link	.= "#$type";
}

if(!empty($error) || $debug)
	include "handler.php";
else
{
	Inform::getInstance()->info('Your changes were saved successfully');
	header('Location: admin'.$header_link);
}
