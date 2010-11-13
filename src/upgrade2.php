<?php
include 'Global.inc.php';
include 'Modules/Menus.module.php';
?>
<form action="" method="get"><div>
<input type="hidden" name="debug" value="<?php echo $debug; ?>" />
force rename:<input name="force_rename" type="checkbox" /><br />
<input type="submit" name="upgrade" value="Upgrade" />
</div></form>
<?php
if(empty($upgrade))
	exit();

$PAGES	= FileSystem::Filtered_File_List($data_root, ".page");

$fail_list	= array();
$rename	= 0;


foreach($PAGES as $page) {
	$orig	= $data_root."/$page.page";
	$new	= $data_root."/$page.chunk";
	if(!is_readable($orig) || !is_writeable($orig) || (file_exists($new) && !$force_rename) || !rename($orig, $new))
		array_push($fail_list, $orig);
	else
		$rename++;
}

$tlist = get_TOClist();

$pub = new AdminPublish();
$pub->get_data();
$menu = new AdminMenus();
$menu->get_data();
foreach($tlist as $id => $file) {
	if($id != 'AliasList') {
		array_push($pub->data, array('id' => $id, 'enable' => 0, 'alias' => $file['alias']));
		array_push($menu->data, array('menu'=>'TOCList', 'href'=>(empty($file['alias']) ? $id : $file['alias']), 'text'=>$file['title'], 'title'=>$file['title'], 'weight'=>$file['weight']));
	}
}
$pub->put_data();
$menu->put_data();

if(!empty($fail_list))
	echo '<p id="error" style=" margin: 3px; padding: 7px; background-color: #FFB6C1;">
The following files were not renamed - please make them world writeable then run this script again or rename them maunally:<br />
'.implode(",<br />\n", $fail_list)."</p>";
elseif($rename)
	echo "<p>$rename file(s) successfully renamed.</p>";
else
	echo "<p>All filenames are already correct.</p>";

?>