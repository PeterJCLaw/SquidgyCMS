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
	for(i=0; i<moveList.length; i++)
		moveList[i].className	= moveList[i].className.replace('JS_move', 'JS');	//only allow it to be moved once
	if('' != location.hash)
		window.cur_div	= location.hash.substr(1);
	else
		window.cur_div	= divList[0].id;
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
if(!$logged_in) {
	print_logon_form();
	include "Foot.inc.php";
	exit();
}

if(is_readable($admin_file)) {	//get the list of wanted ones
	$toclist	= FileSystem::file_rtrim($admin_file);
} else
	$toclist	= array();

$debug_info	.= "\$toclist = ".implode(", ", $toclist)."\n<br />\n";

include Users::file($username);

$toc_list	= array();
$module_list	= FileSystem::Filtered_File_List("Modules", ".module.php");
foreach($module_list as $section)	//grab all the sections and add their objects to the array
{
	if($section == "Admin")
		continue;

	$module_path = get_module_path($module);

	if($module_path !== FALSE)
		require_once($module_path);

	$class_name	= "Admin$section";
	if(class_exists($class_name)) {
		$sect_obj	= new $class_name();

		$_Admin_list[$section]	= $sect_obj->get_info();
		$_Admin_list[$section]['obj']	= $sect_obj;

		if((in_array($section, $toclist) || $_Admin_list[$section]['grouping'] == -1) && (strtolower($username) == 'webmaster' || strtolower($section) != 'webmaster'))
			array_push($toc_list, $section);	//add to the list if compulsory (allowing for webmaster specials) or requested
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
			<span class="f_left" id="welcome">Welcome, <?php echo first_name($name); ?></span>
			<?php if(isset($success)) echo print_success($success)."\n"; ?>
		</div>
		<p>Please note that only changes to the one form that you submit will be saved.</p>
		<ul id="admin_TOC"><?php

	$tocwidth = 100 / count($toc_list);

	foreach($_Admin_list as $section => $val)
	{
		if(!in_array($section, $toc_list))	//if not asked for then skip the rest
			continue;

		echo '
			<li style="width: '.$tocwidth.'%;" title="'.$val['desc'].'"><a title="'.$val['desc'].'" id="'.$section.'_link" href="#'.$section
				.'" onclick="switch_tabs(\''.$section.'\', 0);">'.$val['section_human'].'</a></li>';
	}
?>

		</ul>
		<div id="admin_divs_holder">
<?php
// loop through all the posible sections
foreach($_Admin_list as $section => $val)
{
	if(!in_array($section, $toc_list))	//if not asked for then skip the rest
		continue;

	echo "\n".'<div id="'.$section.'" class="admin_div"><h3 id="'.$section.'_h3">'.$val['section_human']."</h3>\n";

	if($ajax && strtolower($section) != 'webmaster')
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
		window.PAGE	= '<?php echo empty($page_req) ? '' : $page_req; ?>';
		window.ART	= '<?php echo empty($art_req) ? '' : $art_req; ?>';
	//-->
	</script>
<?php
	include 'Foot.inc.php';
?>