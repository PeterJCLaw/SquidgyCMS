<?php
#name = Menus
#description = Menu management and display
#package = Core - optional
#type = content
###

class AdminMenus extends Admin {
	function AdminMenus() {
		parent::__construct();
		$this->complex_data = true;
	}

	function printFormAdmin() {
		$this->get_data();
		$this->data	= group_array_by_key($this->data, 'menu');
		ksort($this->data);
		$this->data['New'] = array();
		foreach($this->data as $menu => $menu_data) {
			multi2dSortAsc($menu_data, 'weight');
?>
<table class="admin_tbl">
<caption><?php echo 'Menu: <input name="menu_name['.$menu.']" value="'.$menu.'" />'; ?></caption>
<tr>
	<th class="L" title="The link text - the text you see">Link Text:</th>
	<th title="The link title - the text you see when you hover on the link">Link Title:</th>
	<th title="The URL that the link points to">Link Target:</th>
	<th title="Integers only, low numbers float above high numbers">Weight:</th>
	<th class="R" title="Clear that row">Clear:</th>
</tr><?php
		$i=0;
		foreach($menu_data as $link)	//cycle through each of the existing links
			echo $this->make_link_row($i++, $menu, $link['text'], $link['href'], $link['title'], $link['weight']);

		$count = count($menu_data);
		for($j=0; $j<3; $j++)	//add three blank rows on the end
			echo $this->make_link_row($count+$j, $menu);
?>
</table>
<?php	}
		return;
	}

	function make_link_row($i, $menu, $text = '', $href = '', $title = '', $weight='') {
		return '
<tr class="'.($i % 2 == 0 ? 'even' : 'odd').'">
	<td class="L"><input class="menustext" name="text['."$menu][$i]\" value=\"$text\"".' /></td>
	<td><input class="menustitle" name="title['."$menu][$i]\" value=\"$title\"".' /></td>
	<td><input class="menushref" name="href['."$menu][$i]\" value=\"$href\"".' /></td>
	<td><input class="menusweight num" name="weight['."$menu][$i]\" value=\"$weight\"".' maxlength="2" size="2" /></td>
	<td class="R"><button type="button" onclick="clearRow(this);">Clear row</button></td>
</tr>';
	}

	function submit($content=0) {
		list($text, $href, $title, $weight, $menu_name) = array();
		extract($_POST, EXTR_IF_EXISTS);
		global $debug_info;
		
		foreach($menu_name as $menu_id => $new_name) {
			if(count($text[$menu_id]) != count($href[$menu_id]))
				log_error("Incorrect parameter count in menu ($new_name)");

			foreach($text[$menu_id] as $key => $this_text) {
				$this_href	= $href[$menu_id][$key];
				$this_title	= empty($title[$menu_id][$key]) ? '' : $title[$menu_id][$key];
				$this_weight	= empty($weight[$menu_id][$key]) ? 0 : $weight[$menu_id][$key];

				if(!empty($this_text) && !empty($this_href))	//if it's not blank save it
					array_push($this->data, array('menu'=>$new_name, 'href'=>$this_href, 'text'=>$this_text, 'title'=>$this_title, 'weight'=>$this_weight));
			}
		}

		return $this->put_data();
	}
}

class BlockMenus extends Block {
	function BlockMenus() {
		parent::__construct();
		$this->complex_data = true;
	}

	function block($args) {
		list($menu) = $args;
		$this->get_data();
		$this->data	= group_array_by_key($this->data, 'menu');
		$this->data	= $this->data[$menu];

		if(empty($this->data))
			return;

		multi2dSortAsc($this->data, 'weight');
		$out	= "\n<ul class=\"menu\" id=\"$menu-menu\">";
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
