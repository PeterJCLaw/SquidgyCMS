<?php
#name = Modules
#description = Enables management of site modules
#package = Core - required
#type = system
###

class AdminModules extends Admin {
	function AdminModules() {
		parent::__construct();
		$this->data_file = $GLOBALS['admin_file'];
	}

	function printFormAdmin() {
		$this->module_list	= array_map('get_module_info', FileSystem::Filtered_File_List("Modules", ".module.php"));
		$this->module_list_grouped	= group_array_by_key($this->module_list, '#package');
		$this->enabled_modules	= is_readable($this->data_file) ? FileSystem::file_rtrim($this->data_file) : array();

		if($this->debug > 1)
			echo 'Module List: '.print_r($this->module_list, true);

		foreach($this->module_list_grouped as $package => $sub_list) {
?>
<table id="<?php echo str_replace(' ', '_', $package); ?>" class="modules">
	<caption><h4><?php echo $package; ?></h4></caption>
	<tr>
		<th class="L">Enabled:</th><th>Module Name:</th><th class="R">Section Description:</th>
	</tr><?php
			$i = 1;
			foreach($sub_list as $val) {
				$description	= $val['#description'];
				$section	= $val['#name'];
				$section_HTMLid	= str_replace(' ', '_', $section);
				$odd_even	= $i % 2 == 1 ? 'odd' : 'even';
				$i++;

				if($package == 'Core - required' || in_array($section, $this->enabled_modules)) {
					if($package == 'Core - required' || $this->what_depends_on($section))
						$disabled	= ' disabled="disabled"';
					else
						$disabled	= '';
					$checked	= ' checked="checked"'.$disabled;
				} else
					$checked	= '';

				$sect_box	= '<input type="checkbox" class="tick" name="sect['."$section_HTMLid]\" id=\"_enable_$section_HTMLid\"$checked />";

				echo "<tr class=\"$odd_even\">
		<td class=\"L\">$sect_box</td>
		<td><label for=\"_enable_$section_HTMLid\">$section</label></td>
		<td class=\"R\">$description</td>
	</tr>";
			}
?>

</table>
<?php
		}
		return;
	}

	function what_depends_on($module) {
		foreach($this->enabled_modules as $e_mod) {
			if(is_array($this->module_list[$e_mod]['#dependencies']) && in_array($module, $this->module_list[$e_mod]['#dependencies']))
				return true;
		}
		return FALSE;
	}

	function submit() {
	}
}

class BlockModules extends Block {
	function BlockModules() {
		parent::__construct();
	}
	function block($args) {

		if(!is_readable($this->data_file))
			return '<span id="news" style="display: none;"> (The file was not readable)</span>';

	}
}
?>