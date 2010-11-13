<?php
#name = User Groups
#description = User Group management
#package = Core - optional
#type = admin
###

class AdminUserGroups extends Admin {
	function AdminUserGroups() {
		parent::__construct();
		$this->complex_data = true;
		$this->u_list = Users::list_all();
		$this->user_list['Select User'] = '';
		foreach($this->u_list as $uid) {
			$u = new User($uid);
			$this->user_list[$uid.(empty($u->name)?'':" ($u->name)")] = $uid;
		}
	}

	function printFormAdmin() {
		$this->get_data();
		$this->data	= group_array_by_key($this->data, 'group');
		ksort($this->data);
		$this->data['New'] = array();
		foreach($this->data as $group => $group_data) {
			multi2dSortAsc($group_data, 'weight');
?>
<table class="admin_tbl">
<caption><?php echo 'Group: <input name="group_name['.$group.']" value="'.$group.'" />'; ?></caption>
<tr>
	<th class="L" title="The user's name (and ID)">User:</th>
	<th title="Integers only, low numbers float above high numbers">Weight:</th>
	<th class="R" title="Clear that row">Clear:</th>
</tr><?php
		$i=0;
		foreach($group_data as $user)	//cycle through each of the existing links
			echo $this->make_row($i++, $group, $user['uid'], $user['weight']);

		$count = count($group_data);
		for($j=0; $j<3; $j++)	//add three blank rows on the end
			echo $this->make_row($count+$j, $group);
?>
</table>
<?php	}
		return;
	}

	function make_row($i, $group, $uid = '', $weight='') {
		return '
<tr class="'.($i % 2 == 0 ? 'even' : 'odd').'">
	<td class="L">'.$this->get_selectbox("user[$group][$i]", $this->user_list, $uid, 'hashtable').'</td>
	<td><input class="groupsweight num" name="weight['."$group][$i]\" value=\"$weight\"".' maxlength="2" size="2" /></td>
	<td class="R"><button type="button" onclick="clearRow(this);">Clear row</button></td>
</tr>';
	}

	function submit($content=0) {
		list($user, $weight, $group_name) = array();
		extract($_POST, EXTR_IF_EXISTS);
		global $debug_info;

		foreach($group_name as $group_id => $new_name) {
			$group = array();
			if(count($user[$group_id]) != count($weight[$group_id]))
				log_error("Incorrect parameter count in group ($new_name)");

			foreach($user[$group_id] as $key => $uid) {
				if(empty($uid) || $group[$uid])	//no blank or duplicate users
					continue;

				$group[$uid] = true;
				$U_weight	= empty($weight[$group_id][$key]) ? 0 : $weight[$group_id][$key];
				array_push($this->data, array('group'=>$new_name, 'uid'=>$uid, 'weight'=>$U_weight));
			}
		}

		return $this->put_data();
	}
}

class BlockUserGroups extends Block {
	function BlockUserGroups() {
		parent::__construct();
		$this->complex_data = true;
	}

	function block($args) {
		list($group) = $args;
		$this->get_data();
		$this->data	= group_array_by_key($this->data, 'menu');
		$this->data	= $this->data[$group];

		if(empty($this->data))
			return;

		multi2dSortAsc($this->data, 'weight');
		$out	= "\n<ul class=\"menu\" id=\"$group-menu\">";
		$last	= count($this->data)-1;
		$i	= 0;

		foreach($this->data as $link) {
			if($link['href'] == '<menu>') {
				$link_html = '[[Block::Menus-block::'.$link['text'].']]';
			} else {
				$title	= (empty($link['title']) ? '' : ' title="'.$link['title'].(strpos($link['href'], 'http') === 0 ? ', External link' : '').'"');
				$link_html = '<a href="'.($link['href'] == '<home>' ? $GLOBALS['base_href'] : $link['href']).'"'.$title.'>'.$link['text'].'</a>';
			}
			$class	= ($last == 0 ? ' class="first last"' : ($i == 0 ? ' class="first"' : ($i == $last ? ' class="last"' : '')));
			$out	.= "\n	<li$class>$link_html</li>";
			$i++;
		}
		return $out."\n</ul>";
	}
}

class UserGroups {
	function users_in_group($name) {
		$data = FileSystem::get_file_assoc($GLOBALS['data_root'].'/usergroups.data');
		multi2dSortAsc($data, 'weight');
		foreach($data as $entry) {
			if($entry['group'] == $name)
				$group_data[] = $entry['uid'];
		}
		return $group_data;
	}
}
?>