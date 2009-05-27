<?php
#name = Module
#description = Contains module templates for blocks and admin sections
#package = Core - required
#type = system
###

class ModuleTemplate {

	function __construct() {
		global $data_root, $site_root, $debug;
		$this->site_root	= $site_root;
		$this->data_root	= $data_root;
		$this->data_file	= $this->data_root.'/'.strtolower($this->get_my_class()).'.data';
		$this->complex_data	= FALSE;
		$this->data_key_column	= FALSE;
		$this->data	= array();
		$this->debug	= $debug;
	}

	function ModuleTemplate() {
		return $this->__construct();
	}

	function get_my_class() {
		return substr(get_class($this), 5);
	}

	function get_data() {
		if($this->complex_data)
			$this->data = FileSystem::get_file_assoc($this->data_file, $this->data_key_column);
		else
			$this->data = FileSystem::get_file_rtrim($this->data_file);
	}

	function put_data() {
		if($this->complex_data)
			return FileSystem::put_file_assoc($this->data_file, $this->data, true);
		else
			return FileSystem::file_put_contents($this->data_file, implode("\n",$this->data), 'w');
	}

	function get_info() {
		$a['class']	= $this->get_my_class();
		return $a;
	}
}

class Admin extends ModuleTemplate {

	var $section;	//php <5
	var $sect_title;
	var $grouping;
/*	protected $section;
	protected $sect_title;
	protected $grouping;
*/
	function __construct($grouping = 0, $weight = 0) {
		parent::__construct();
		global $_SITE_USER;
		if(!$_SITE_USER->is_logged_in() || !$_SITE_USER->has_auth(USER_SIMPLE)) {
			if($_SITE_USER->is_logged_in())
				echo 'You do not have sufficient priviledges to view this page.';
			else
				$_SITE_USER->print_logon_form();
			exit();
		}

		if(!isset($this->no_submit))
			$this->no_submit	= FALSE;
		$this->section	= $this->get_my_class();
		$this->section_human	= ucwords(str_replace("_", " ", $this->section));

		$info = Module::get_info($this->section);
		if(is_array($info) && isset($info['#name']) && isset($info['#description'])) {
			$this->own_file_info	= $info;
			$this->sect_title		= $info['#description'];
			$this->section_human	= $info['#name'];
		}

		if(!empty($this->debug) && $this->debug > 1) {
			echo "DEBUG = $this->debug\n\$sect_title_in = '$sect_title_in'\n";
			print_r($this);
		}
	}

	function Admin() {
		return $this->__construct();
	}

	function get_desc() {
		return $this->sect_title;
	}

	function get_grouping() {
		return $this->grouping;
	}

	function get_info() {
		$a['desc']	= $this->sect_title;
		$a['grouping']	= $this->grouping;
		$a['weight']	= $this->weight;
		$a['no_submit']	= $this->no_submit;
		$a['data_file']	= $this->data_file;
		$a['section']	= $this->section;
		$a['section_human']	= $this->section_human;
		$a['class']	= get_class($this);
		return $a;
	}

	function delete_file($file) {

		if(!file_exists($file))	//if its not there
			return "\nFile ($file) does not exist! Please inform the Webmaster if you think it should\n<br />\n";

		if(is_readable($file) && !is_writable($file))
			return "\nFile ($file) not writeable - please inform the Webmaster\n<br />\n";

		if(!unlink($file))	//if its writeable this should work
			return "\nFailed to delete file ($file)\n<br />\n";

		return;
	}

	function site_full_path($page) {
		return "$this->data_root/$page.page";
	}

	function change_something_in_all_pages($old, $new) {
		$GEN_pages	= array_map(array($this, 'site_full_path'), Filtered_File_List($this->data_root, ".page"));
		array_push($GEN_pages, $GLOBALS['pages_file']);
		$error	= "";
		foreach($GEN_pages as $page) {	//check if we can modify all the pages
			if(!is_writable($page))
				return "\nUnable to change file id as file ($page) is not writeable - please inform the Webmaster\n<br />\n";
		}
		foreach($GEN_pages as $page) {	//go through all the pages, replacing the old id with the new one, if its present
			$page_content	= file_get_contents($page);
			if(strpos($page_content, $old))
				$error	.= FileSystem::file_put_contents($page, str_replace($old, $new, $page_content), 'w');
		}
		return $error;
	}

	function new_id_needed($old_id, $title) {
		if(empty($old_id) || $old_id == 'new')
			return TRUE;
		$old_title	= get_GEN_title($old_id);
		return ($old_title == $title) ? FALSE : TRUE;
	}

	function print_select_cells($list, $req = '') {
	$type	= $this->section;
	$type_h	= str_replace("_", " ", ucwords($type));
	global $page_n, $debug_info;
	if(!in_array($req, $list))
		$req	= '';
	?>
			<th><label for="<?php echo $type; ?>_sel_td" title="Select an <?php echo $type; ?> to edit"><?php echo $type_h; ?>:</label></th>
			<td id="<?php echo $type; ?>_sel_td">
				<span id="<?php echo $type; ?>_change" style="display: none;" class="JS_show">
					<a onclick="show_change('<?php echo $type; ?>', 0);">Change <?php echo $type_h; ?></a>
				</span>
				<span id="<?php echo $type; ?>_sel_span" class="JS_hide">
					<select id="<?php echo $type; ?>_select">
			<?php
	foreach($list as $file)
	{
		$tmpval = get_GEN_title($file);
		if($file == $req)
			$suc	= ' selected="selected"';
		else
			$suc	= '';
		echo "\n						<option$suc value=\"$file\">$tmpval</option>";
	}
	echo "\n						<option".(empty($req) ? ' selected="selected"' : "")." value=\"new\">New $type_h </option>\n"; ?>
					</select>
					<input type="button" onclick="get('<?php echo $type; ?>', <?php echo (!empty($req) ? get_GEN_id($req) : "'new'"); ?>);" value="Go" class="Go" />
				</span>
			</td>
<?php	}

	function printFormHeader() {
	global $debug; ?>
<form id="Admin<?php echo $this->section ?>_form" action="admin_handler.php<? echo $debug ? '?debug=1' : ''; ?>" method="<? echo $debug ? 'get' : 'post'; ?>" onsubmit="return Validate_On_Admin_Submit(this)">
<div class="admin_form_head">
	<span class="f_right JS_move">
		<input type="submit" name="submit" value="Save - <?php echo $this->section_human; ?>" />
		<br />
		<input type="reset" value="Reset - <?php echo $this->section_human; ?>" />
	</span>
	<?php echo $this->sect_title; ?>:
	<input type="hidden" name="type" value="<?php echo $this->section; ?>" />
</div>
<div class="admin_form">
<?php }

	function printTextarea($text = '') { ?>
	<textarea name="admin_content" id="Admin<?php echo $this->section; ?>_content" rows="12" cols="71"><?php echo htmlspecialchars(stripslashes($text)); ?></textarea>
<?php }

	function printFormFooter() { ?>
</div>
</form>
<?php }

	/* This function generates a Time selector */
	function genTimeSelector($prefix, $hour = 19, $min = 0)
	{
		global $debug_info;
		echo "\n<select name=\"${prefix}hour\">";

		for($i = 0; $i <= 23; $i++)
			echo "\n	<option value=\"$i\"" . ($i == $hour ? ' selected="selected"' : "") . ">$i</option>";

		echo "\n</select> : "
			."\n<select name=\"${prefix}minute\" >";

		for($i = 0; $i <= 56; $i++)
		{
			if($i % 5 == 0) {
				$debug_info .= "\$i=$i\n<br />\n";
				echo "\n	<option value=\"$i\"" . ($i == $min ? ' selected="selected"' : "") . ">".($i<10?"0":"")."$i</option>";
			}
		}

		echo "\n</select>\n";
	}

	/* This function generates a date selector. from MRBS, with tweaks by me */
	function genDateSelector($prefix, $form_id, $day = 0, $month = 0, $year = 0, $max = 1, $min = 0)
	{
		if($day == 0)	$day	= date("d");	//if the current date isn't supplied
		if($month == 0)	$month	= date("m");
		if($year == 0)	$year	= date("Y");

		echo "\n<select name=\"${prefix}day\">";

		for($i = 1; $i <= 31; $i++)
			echo "\n	<option value=\"$i\"" . ($i == $day ? ' selected="selected"' : "") . ">$i</option>";

		echo "\n</select>"
			."\n<select name=\"${prefix}month\" onchange=\"ChangeOptionDays(this.form,'$prefix')\">";

		for($i = 1; $i <= 12; $i++)
		{
			$m = strftime("%b", mktime(0, 0, 0, $i, 1, $year));
			echo "
		<option value=\"$i\"" . ($i == $month ? ' selected="selected"' : "") . ">$m</option>";
		}

		echo "\n</select>"
			."\n<select name=\"${prefix}year\" onchange=\"ChangeOptionDays(this.form,'$prefix')\">";

		$min	+= $year;
		$max	+= $year;

		for($i = $min; $i <= $max; $i++)
			echo "\n	<option value=\"$i\"" . ($i == $year ? ' selected="selected"' : "") . ">$i</option>";

		echo '
	</select>
	<script type="text/javascript">
		<!--
		// fix number of days for the $month/$year that you start with'."
		ChangeOptionDays(document.forms['$form_id'], '$prefix');
		// -->
	</script>\n";
	}
}

class Block extends ModuleTemplate {

	function __construct() {
		return parent::__construct();
	}

	function Block() {
		return $this->__construct();
	}
}

class Module {
	function get_info($module) {
		$file = Module::get_path($module);
		if($file === FALSE)
			return FALSE;
		$fh	= fopen($file, 'r');
		fgets($fh);
		while($line = rtrim(fgets($fh))) {
			if($line == '###')
				break;
			list($key, $value) = explode('=', $line);
			$info[rtrim($key)] = trim($value);
		}
		fclose($fh);

		$info['#id']	= $module;
		$info['#path']	= $file;

		return $info;
	}

	function get_path($module) {
		if(is_readable("Modules/$module.module.php") || is_readable("Sites/Custom_Modules/$module.module.php")) {
			if(is_readable("Modules/$module.module.php"))
				return "Modules/$module.module.php";
			else
				return "Sites/Custom_Modules/$module.module.php";
		}
		log_info("Module '$module' is not readable or does not exist");
		return FALSE;
	}

	function list_all() {
		return FileSystem::Filtered_File_List("Modules", ".module.php");
	}

	function list_all_with_info() {
		return array_map(array('Module','get_info'), Module::list_all());
	}

	function list_enabled($include_required_modules = FALSE) {
		$modules_file = $GLOBALS['data_root'].'/modules.data';
		$modules_info = is_readable($modules_file) ? FileSystem::get_file_assoc($modules_file, '#id') : array();
		if(empty($modules_info))
			$modules_info = Module::list_all_with_info();
		$enabled_modules = array();
		foreach($modules_info as $info)
			if(!empty($info['enabled']) || ($include_required_modules && $info['#package'] == 'Core - required'))
				array_push($enabled_modules, $info['#id']);

		return $enabled_modules;
	}
}
?>