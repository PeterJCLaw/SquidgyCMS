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
		$menu = 'primary';
		$this->data_file  = $thi->data_root.'/'.strtolower($menu).'.menu';
		$this->get_data();
		multi2dSortAsc($this->data, 'weight');
		?>
<input name="menu" value="primary" type="hidden" />
<table id="admin_menus_tbl" class="admin_tbl"><tr>
	<th class="L" title="The link text - the text you see">Link Text:</th>
	<th title="The link title - the text you see when you hover on the link">Link Title:</th>
	<th title="The URL that the link points to">Link Target:</th>
	<th title="Integers only, low numbers float above high numbers">Weight:</th>
	<th class="R" title="Clear that row">Clear:</th>
</tr><?php
		foreach($this->data as $link) {	//cycle through each of the existing links
			echo $this->make_link_row($i, $link['text'], $link['href'], $link['title'], $link['weight']);
		}
		$count = count($this->data);
		for($j=0; $j<3; $j++) {	//add three blank rows on the end
			echo $this->make_link_row($count+$j);
		} ?>
</table>
<?php return;
	}

	function make_link_row($i, $text = '', $href = '', $title = '', $weight='')
	{
		return '
<tr id="menu_row_'.$i.'">
	<td class="L"><input class="menustext" name="text['."$i]\" value=\"$text\"".' type="text" size="20" /></td>
	<td><input class="menustitle" name="title['."$i]\" value=\"$title\"".' type="text" size="35" /></td>
	<td><input class="menushref" name="href['."$i]\" value=\"$href\"".' type="text" size="50" /></td>
	<td><input class="menusweight" name="weight['."$i]\" value=\"$weight\"".' type="text" maxlength="2" size="2" class="num" /></td>
	<td class="R"><button type="button" onclick="clearRow(this);">Clear row</button></td>
</tr>';
	}

	function submit() {
		global $debug_info, $text, $href, $title, $weight, $menu;

		if(count($text) != count($href))
			return "\nIncorrect parameter count";

		foreach($text as $key => $this_text) {
			$this_href	= $href[$key];
			$this_title	= empty($title[$key]) ? '' : $title[$key];
			$this_weight	= empty($weight[$key]) ? 0 : $weight[$key];

			if(!empty($this_text) && !empty($this_href))	//if it's not blank save it
				array_push($this->data, array('href'=>$this_href, 'text'=>$this_text, 'title'=>$this_title, 'weight'=>$this_weight));
		}
		$this->data_file  = $this->data_root.'/'.strtolower($menu).'.menu';
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
		$this->data_file  = $this->data_root.'/'.strtolower($menu).'.menu';
		$this->get_data();
		
		if(empty($this->data))
			return;
		
		multi2dSortAsc($this->data, 'weight');
		$out	= "\n	<ul class=\"menu\" id=\"$menu\">";
		$last	= count($this->data)-1;
		$i	= 0;
		
		foreach($this->data as $link) {
			$class	= ($last == 0 ? ' class="first last"' : ($i == 0 ? ' class="first"' : ($i == $last ? ' class="last"' : '')));
			$title	= (empty($link['title']) ? '' : ' title="'.$link['title'].(strpos($link['href'], 'http') === 0 ? ', External link' : '').'"');
			$out	.= '
		<li'.$class.'><a href="'.$link['href'].'"'.$title.'>'.$link['text'].'</a></li>';
			$i++;
		}
		return $out."\n	</ul>";
	}
}
?>