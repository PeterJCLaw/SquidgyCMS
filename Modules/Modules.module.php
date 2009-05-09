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
		$this->module_list	= Module::list_all_with_info();
		$this->module_list_grouped	= group_array_by_key($this->module_list, '#package');
		$this->enabled_modules	= Module::list_enabled(FALSE);

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
				$section	= $val['#id'];
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

				$sect_box	= '<input type="checkbox" class="tick" name="sect['."$section]\" id=\"_enable_$section\"$checked />";

				echo "<tr class=\"$odd_even\">
		<td class=\"L\">$sect_box</td>
		<td><label for=\"_enable_$section\">".$val['#name']."</label></td>
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

	function verify_module($module) {
		return true;
		$info = get_module_info($module);
		include $info['#path'];
		$module = ucwords($module);
		$adminClass = "Admin$module";
		$blockClass = "Block$module";
		if(class_exists($adminClass)) {
			if(has_method($adminClass, 'submit') && has_method($adminClass, 'printFormHeader') && has_method($adminClass, 'printFormAdmin') && has_method($adminClass, 'printFormFooter'))
				$adminPass = true;
			elseif(!empty($val['no_submit']) && $val['no_submit'])	//if there's no submission - eg just providing a link somewhere
				$val['obj']->printFormAdmin();
			else
				$adminPass = FaLSE;
		}
		if(class_exists($blockClass)) {
			$blockPass = true;
		}
		return $adminPass && $blockPass;
	}

	function submit() {
		global $sect;
		$enable_list = array();

		if(!empty($sect))
			foreach($sect as $sect_key => $tmpval) {
				if($tmpval && $this->verify_module($sect_key))
					array_push($enable_list, $sect_key);

				$debug_info	.= "\$sect[$sect_key]=$sect[$sect_key],	\$tmpval	= $tmpval\n<br />";
			}

		$error	.= FileSystem::file_put_stuff($this->data_file, implode("\n", $enable_list), 'w');
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