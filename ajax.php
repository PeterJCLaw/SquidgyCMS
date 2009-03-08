<?php
require_once('Global.inc.php');

if($type == 'admin') {
	require_once("Classes/Admin.class.php");

	if(!is_readable("Classes/$class.admin.class.php")) {
		print_Admin_Section(array('fail'));
		exit();
	}

	require_once("Classes/$class.admin.class.php");

	$class_name	= "Admin$class";
	if(class_exists($class_name)) {
		$thisobj	= new $class_name();

		$host_arr	= $thisobj->get_info();
		$host_arr['obj']	= $thisobj;

		print_Admin_Section($host_arr);
	}
} elseif($type == 'block') {
	require_once("Classes/Block.class.php");

	if(!is_readable("Classes/$class.block.class.php")) {
		print_Admin_Section(array('fail'));
		exit();
	}

	require_once("Classes/$class.block.class.php");

	if(class_exists($class) && method_exists($class, 'ajax')) {
		$thisobj	= new $class;
		echo $thisobj->ajax();
	} else {
		echo "Ajax method not defined in class $class.";
	}
	
} elseif($type == 'preview') {
} else {
	echo "Request type not recognised.";
}

if($debug)
	echo "\$debug_info = <br />$debug_info<br />";
?>