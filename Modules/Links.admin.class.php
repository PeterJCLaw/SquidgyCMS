<?php
class AdminLinks extends Admin {
	function AdminLinks() {
		parent::__construct('Manage a list of links');
	}

	function printFormAdmin() {
		global	$debug, $debug_info, $links_file;
		$Links	= get_file_assoc($links_file, array('href', 'text', 'title'));
		multi2dSortAsc($Links, 'title');
		?>
			<table id="admin_links_tbl" class="admin_tbl"><tr>
				<th title="The link text - the text you see">Link Text:</th>
				<th title="The link title - the text you see when you hover on the link" class="T M">Title:</th>
				<th title="The URL that the link points to" class="T M">Link Target:</th>
				<th title="Clear that row" class="T R">Clear:</th>
			</tr><?php
		$Link	= reset($Links);
		$i	= 0;
		while(!empty($Link)) {	//cycle through each of the existing links
			echo $this->make_Link_Row($i, $Link['text'], $Link['href'], $Link['title']);
			$Link	= next($Links);
			$i++;
		}

		for($j=0; $j<3; $j++, $i++) {	//add three blank rows on the end
			echo $this->make_Link_Row($i);
		} ?>
			</table>
<?php return;
	}

	function make_Link_Row($i, $text = '', $href = '', $title = '')
	{
		global $debug_info, $links_file;
		$debug_info	.= "i = $i, text = '$text', href = '$href', title = '$title'\n<br />\n";
		return '
			<tr id="link_row_'.$i.'">
				<td><input class="linkstext" name="text['.$i.']"'.(empty($text) ? '' : " value=\"$text\"").' type="text" size="20" /></td>
				<td class="T M"><input class="linkstitle" name="title['.$i.']"'.(empty($title) ? '' : " value=\"$title\"").' type="text" size="35" /></td>
				<td class="T M"><input class="linkshref" name="href['.$i.']"'.(empty($href) ? '' : " value=\"$href\"").' type="text" size="50" /></td>
				<td class="R"><button type="button" onclick="clearRow(this);">Clear row</button></td>
			</tr>';
	}

	function submit() {
		global $debug_info, $text, $href, $title, $links_file;

		if(count($text) != count($href))
			return "\nIncorrect parameter count";;

		$link_list	= array();

		foreach($text as $key => $this_text) {
			$this_href	= $href[$key];
			$this_title	= empty($title[$key]) ? '' : $title[$key];

			if(!empty($this_text) && !empty($this_href))	//if its enabled get its weight & build the output
				array_push($link_list, "$this_href|:|$this_text|:|$this_title");

			$debug_info	.= "\$this_href = '$this_href',	\$this_text	= '$this_text',	\$this_title	= '$this_title'\n<br />";
		}
		return file_put_stuff($links_file, implode("\n", $link_list), 'w');
	}//*/
}
?>