<?php
class AdminArticle_Page extends Admin {
	function AdminArticle_Page() {
		parent::__construct('Create or change pages made from articles');
	}

	function printFormAdmin() {
	global $GEN_art, $GEN_pages, $page_req, $page_n, $debug, $debug_info;
	$i = $num_rows = 1;
	$OUT_P = $out = $page_head_title = "";

	foreach($GEN_pages as $file)
	{
		$name	= get_GEN_title($file);
		$OUT_P	.= "\n						<option value=\"$file\">$name</option>";
	}

	foreach($GEN_art as $file)
	{
		$name	= get_GEN_title($file);
		$out	.= "\n						<option value=\"$file\">$name</option>";
	}
	$last	= "\n						<option value=\"0\" selected=\"selected\">None</option>\n					";


	if(isset($page_req) && in_array($page_req, $GEN_pages))
	{
		include "$this->data_root/".$page_req.".page";

		$page_id	= $page_req;
		$page_head_title	= get_GEN_title($page_req);
		$page_num	= get_GEN_id($page_req);
		$num_rows	= round(count($page_layout)/2);
		$last		= str_replace(" selected=\"selected\"", "", $last);

		foreach($page_layout as $tmpval)
		{
			$out_p[$i]	= str_replace("value=\"$tmpval\"", " value=\"$tmpval\" selected=\"selected\"", $out.$last);
			$i++;
		}
	} else {
		$out_p[1]	= $out_p[2]	= $out.$last;
		$page_id	= 'new';
	}
?>
			<table id="admin_page_tbl"><tr>
				<th><label for="page_title" title="The title of the page">Page Title:</label></th>
				<td><input type="<?php echo ($page_head_title == "Home" ? "hidden" : "text"); ?>" name="page_title" title="The title of the page" value="<?php
					 echo ($page_head_title != "" ? $page_head_title : "New Page"); ?>" /><?php echo ($page_head_title == "Home" ? "Home" : "")."\n"; ?>
					<input type="hidden" name="num_rows" id="num_rows" value="<?php echo $num_rows; ?>" />
					<input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
					</td>
			</tr><tr>
				<th><label for="page_sel_td" title="Select an page to edit">Select Page:</label></th>
				<td id="page_sel_td">
					<span id="page_change" style="display: none;">
						<?php echo $page_req."\n"; ?>
						<a onclick="show_change('page', 0);">Change Page</a>
					</span>
					<span id="page_sel_span"><select id="page_select">
				<?php $OUT_P	= str_replace('value="'.$page_req.'"', 'value="'.$page_req.'" selected="selected"', $OUT_P);	//select the appropriate article
					$OUT_P		= str_replace(">index<", ">Home<", $OUT_P);
					echo $OUT_P."\n						<option".(!$page_req ? ' selected="selected"' : "").">New Page</option>\n					"; ?>
					</select>
					<input type="button" onclick="redir('<?php echo $section; ?>', <?php echo $page_num.", '$page_n'"; ?>);" value="Go" id="Go1" />
					</span>
				</td>
			</tr><tr>
				<th>Page Articles:</th><td class="center"><a onclick="add_article_row()">Add a row of articles</a></td>
			</tr><tr>
				<td colspan="2" class="center">
					<div id="article_select_div">
						<p id="art_sel_p">
<?php for($i=1; $i <= 2*$num_rows; $i+=2) {
	$k = $i+1;
	echo ($i>1 ? "<p>":"")."<select name=\"article_id_$i\" id=\"article_id_$i\">".$out_p[$i]."</select>"
						."<select name=\"article_id_$k\" id=\"article_id_$k\">".$out_p[$k]."</select>
						</p>";
} ?>
					</div>
				</td>
			</tr></table>
<?php return;
	}

	function submit()
	{
		global $page_head_title, $page_id, $num_rows, $header_link, $debug_info, $debug;

		if(empty($page_head_title))
			return "\nNo title provided\n<br />\n";

		$content	= "[[Block::Article-table::";

		for($i=1; $i <= 2*$num_rows; $i+=2)
		{
			$j	= $i+1;
			$a	= $GLOBALS["article_id_$i"];	//get the data into an array
			$b	= $GLOBALS["article_id_$j"];

			if(!empty($a) || !empty($b))	//if one of them isn't empty
				$content	.= "$a||$b".($j < 2*$num_rows ? "||": "]]");

			$debug_info .= "\$a=$a\n<br />\$b=$b\n<br />\n";
		}
		if(strpos($out, "]]") != strlen($content)-1) {
			$out[strlen($content)-1]	= "]";
			$out[strlen($content)]	= "]";
		}

		$old_page_id	= urldecode($page_id);

		if($this->new_id_needed($old_page_id, $page_head_title) && is_writable("$this->data_root/$page_id.page")) {	//if we should and can change stuff
			$page_id	= get_next_id($this->data_root, ".page")."-$page_head_title";
			$error	= $this->change_something_in_all_pages($old_page_id, $page_id);
			if(!empty($error))	//if there's an error then bail
				return $error;
			$error	= $this->delete_file("$this->data_root/".urlencode($old_page_id).".page");
			$page_id	= urlencode($page_id);
		}

		$debug_info .= @"\$old_page_id = '$old_page_id', \$page_id = '$page_id'\n<br />\n";

		$header_link	= "&p=$page_id";

		return file_put_stuff("$this->data_root/".$page_id.".page", $content, 'w');
	}
}
?>