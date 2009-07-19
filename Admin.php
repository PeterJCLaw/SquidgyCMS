<?php
	$scripts_need_login	= TRUE;
	$page_scripts	= <<<SCRIPTS
<script type="text/javascript" src="ajax.js"></script>
<script type="text/javascript" src="scripts.js"></script>
<script type="text/javascript" src="scripts_admin.js"></script>
<!-- script type="text/javascript" src="tinymce_???????/jscripts/tiny_mce.js"></script -->
<script type="text/javascript">
<!--
/*
tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	gecko_spellcheck : true,
	content_css : "Textarea.css",
	doctype : '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'
});
*/

function load_admin()
{
	window.divList	= get_Sub_Class_Elements('admin_divs_holder', 'admin_div');
	window.hideList	= get_Sub_Class_Elements('admin_divs_holder', 'JS_hide');
	window.showList	= get_Sub_Class_Elements('admin_divs_holder', 'JS_show');
	window.moveList	= get_Sub_Class_Elements('admin_divs_holder', 'JS_move');
	for(var i=0; i<moveList.length; i++)
		moveList[i].className	= moveList[i].className.replace('JS_move', 'JS');	//only allow it to be moved once
	if('' != location.hash)
		window.cur_div	= location.hash.substr(1);
	else
		window.cur_div	= divList[0].id.substr(5);
	switch_tabs(window.cur_div);
	return;
}
window.LOG	= add_loader(load_admin);
window.TITLE	= document.title;
location.hash.onchange	= switch_tabs;
//-->
</script>
SCRIPTS;

include 'Head.inc.php';

/* This is the actual page below this point */

//check that they're logged in and have the authority to view the page
if(!$_SITE_USER->is_logged_in() || !$_SITE_USER->has_auth(USER_SIMPLE)) {
	if($_SITE_USER->is_logged_in())
		echo 'You do not have sufficient priviledges to view this page.';
	else
		$_SITE_USER->print_logon_form();
	include "Foot.inc.php";
	exit();
}

//get the list of wanted ones
$module_info	= Module::list_all_with_info();
$enabled_modules	= Module::list_enabled(true);

$debug_info	.= "enabled_modules = ".print_r($enabled_modules, true)."\n<br />\n";

foreach($enabled_modules as $module) {
	$path = Module::get_path($module);

	if($path === FALSE || !$_SITE_USER->has_auth_type($module_info[$module]['type']))
		continue;
	require_once($path);

	$class_name	= "Admin$module";
	if(class_exists($class_name)) {
		$sect_obj	= new $class_name();

		$_Admin_list[$module]	= $sect_obj->get_info();
		$_Admin_list[$module]['obj']	= $sect_obj;
	}
}

multi2dSortAsc($_Admin_list, "weight");
if(!empty($debug) && $debug > 1) {
	echo "printing \$_Admin_list:\n";
	print_r($_Admin_list);
}
?>
	<div id="admin">
		<div class="admin_head">
			<span class="f_left" id="welcome">Welcome, <?php echo $_SITE_USER->get_first_name(); ?></span>
<?php if(isset($success)) echo print_success($success)."\n"; ?>
		</div>
		<p>Please note that only changes to the one form that you submit will be saved.</p>
		<ul id="admin_TOC"><?php

	$toc_width = 100 / count($_Admin_list);

	foreach($_Admin_list as $module => $val) {
		echo '
			<li style="width: '.$toc_width.'%;" title="'.$val['desc'].'"><a title="'.$val['desc'].'" id="Admin'.$module.'_link" href="#'.$module
				.'" onclick="switch_tabs(\''.$module.'\', 0);">'.$val['section_human'].'</a></li>';
	}
?>

		</ul>
		<div id="admin_divs_holder">
<?php
// loop through all the posible sections
foreach($_Admin_list as $section => $val) {
	echo "\n".'<div id="Admin'.$section.'" class="admin_div"><h3 id="Admin'.$section.'_h3">'.$val['section_human']."</h3>\n";

	if($ajax)
		echo '<br />Loading Section...';
	else
		print_Admin_Section($val);

	echo "\n</div>";
}	//end foreach section in Admin-list
?>
		</div><!-- end gen_txt div -->
	</div><!-- end admin div -->
	<script type="text/javascript">
	<!--
		window.AJAX_enabled	= <?php echo $ajax; ?>;
		window.DATA_root	= '<?php echo $data_root; ?>';
		window.SITE_root	= '<?php echo $site_root; ?>';
		window.PAGE	= '<?php echo empty($page_req) ? '' : $page_req; ?>';
		window.ART	= '<?php echo empty($art_req) ? '' : $art_req; ?>';
	//-->
	</script>
<?php include 'Foot.inc.php'; ?>