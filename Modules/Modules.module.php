<?php
#name = Modules
#description = Enables management of site modules
#package = Core - required
#type = system
###

class AdminModules extends Admin {
	function AdminModules() {
		parent::__construct();
		$this->module_properties = array('#id', '#name', '#package', '#type', '#path', '#dependencies', '#description');
		$this->complex_data = true;
		$this->data_key_column = '#id';
		$this->get_data();
		if(empty($this->data)) {
			log_info('Module listing empty: reloadng', $this->data);
			$this->reload_module_list();
		}
	}

	function printFormAdmin() {
		$module_list_grouped	= group_array_by_key($this->data, '#package');
		ksort($module_list_grouped);

		foreach($module_list_grouped as $package => $sub_list) {
?>
<input type="submit" class="f_right" value="Reload Module List" name="submit" />
<table id="<?php echo str_replace(' ', '_', $package); ?>" class="modules">
	<caption><?php echo $package; ?></caption>
	<tr>
		<th class="L">Enabled:</th><th>Module Name:</th><th class="R">Section Description:</th>
	</tr><?php
			$i = 1;
			foreach($sub_list as $val) {
				$description	= $val['#description'];
				$odd_even	= $i % 2 == 1 ? 'odd' : 'even';
				$id	= $val['#id'];
				$i++;

				if($package == 'Core - required' || !empty($val['enabled'])) {
					if($package == 'Core - required' || $this->what_depends_on($id))
						$disabled	= ' disabled="disabled"';
					else
						$disabled	= '';
					$checked	= ' checked="checked"'.$disabled;
				} else
					$checked	= '';

				$sect_box	= '<input type="checkbox" class="tick" name="sect['."$id]\" id=\"_enable_$id\"$checked />";

				echo "<tr class=\"$odd_even\">
		<td class=\"L\">$sect_box</td>
		<td><label for=\"_enable_$id\">".$val['#name']."</label></td>
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
		$dependencies = array();
		foreach($this->data as $id => $info) {
			if(empty($info['#dependencies']))
				continue;
			$depends_on = str_getcsv($info['#dependencies']);
			if(in_array($module, $depends_on))
				array_push($dependencies, $id);
		}
		return $dependencies;
	}

	function reload_module_list() {
		$old_data = $this->data;
		$this->data = array();
		$info = Module::list_all_with_info();
		foreach($info as $module) {
			foreach($this->module_properties as $col) {
				if(empty($module[$col]))
					$module[$col] = 0;
			}
			$module['enabled'] = empty($old_data[$module['#id']]['enabled']) ? 0 : 1;
			$this->data[$module['#id']] = $module;
		}
		$this->put_data();
	}

	function verify_module($module) {
		return true;
		$info = Module::get_info($module);
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
		global $sect, $submit;
		
		if($submit == 'Reload Module List')
			return $this->reload_module_list();

		if(!empty($sect))
			foreach($sect as $id => $tmpval) {
				if($tmpval && $this->verify_module($id))
					$this->data[$id]['enabled'] = 1;
				else
					$this->data[$id]['enabled'] = 0;
			}

		return $this->put_data();
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