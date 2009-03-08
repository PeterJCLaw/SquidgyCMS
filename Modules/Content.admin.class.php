<?php
class AdminContent extends Admin {
	function AdminContent() {
		parent::__construct('Manage website content', -1, -20);
	}

	function printFormAdmin() {
		global $GEN_art, $GEN_pages, $Site_TOClist, $FSCMS_pages;
		natsort($GEN_art);
		natsort($GEN_pages);
		$Site_pages	= array_merge($GEN_pages, $FSCMS_pages);
		?>
			<table id="admin_manage_tbl" class="admin_tbl"><tr>
				<th title="Click on the page title link to edit the page">Page:</th>
				<th title="Tick the box to delete the page, this cannot be undone" class="T R">Delete:</th>
				<th title="Click on the article title link to edit the article">Edit Article:</th>
				<th title="Tick the box to delete the article, this cannot be undone" class="T R">Delete:</th>
			</tr><?php
		$check	= '<input type="checkbox" class="tick" name="';
		$Page	= reset($Site_pages);
		$Art	= reset($GEN_art);
		while(!empty($Page) || !empty($Art))
		{
			$del_box_p	= $weight_box	= $enable_box	= $page_link	= $del_box_a	= $art_link	= '&nbsp;';
			if(!empty($Page)) {
				if(in_array($Page, $GEN_pages)) {	//if its a user page
					if($Page == '1-Home') {
						$page_name	= "Home";
						$del_box_p	= "&nbsp;";
					} else {
						$page_name	= get_GEN_title($Page);
						$del_box_p	= $check.'del['.$Page.'.page]" />';
					}
					$page_link	= '<a href="?page_req='.$Page.'#Page" title="Edit the \''.$page_name.'\' page">'.$page_name.'</a>';
				} else {
					if($Page == 'Committee.php')
						$page_link	= '<a href="#Profile" title="Edit your committee page profile">Committee</a>';
					else
						$page_link	= str_replace("_", " ", substr($Page, 0, -4));
				}
			}
			if(!empty($Art)) {
				$art_name	= get_GEN_title($Art);
				$del_box_a	= $check.'del['.$Art.'.article]" />';
				$art_href	= "?art_req=".$Art;
				$art_link	= '<a href="'.$art_href.'#Article" title="Edit the \''.$art_name.'\' article">'.$art_name.'</a>';
			}

			$Page	= next($Site_pages);
			$Art	= next($GEN_art);

			echo '
			<tr>
				<td class="L">'.$page_link.'</td>
				<td class="T R">'.$del_box_p.'</td>
				<td class="L">'.$art_link.'</td>
				<td class="T R">'.$del_box_a.'</td>
			</tr>';
		} ?>
			</table>
<?php return;
	}

	function submit() {
		global $debug_info, $del, $enable, $weight, $links, $FSCMS_pages, $pages_file, $GEN_pages;
		$error	= "";

		if(!empty($del)) {
			foreach($del as $file => $val) {
				$a	= $this->delete_file("$this->data_root/$file");	//checks the readability then deletes the file, or returns an error
				$error	.= (!empty($a) ? "$a Links to ($file) have not been changed" : $this->change_something_in_all_pages($file, 'File Deleted'));	//change the links to the file, if it was deleted
			}
		}

		if(!empty($enable)) {
			$toclist	= array();
			foreach($enable as $enable_key => $val) {
				$w	= (int) $weight[$enable_key];

				if($enable_key == 'Home') {
					$href	= './';
					$title	= 'Home';
				} elseif(in_array($enable_key, $FSCMS_pages)) {
					$href	= $enable_key;
					$title	= str_replace("_", " ", substr($enable_key, 0, -4));
					if(!is_readable($href))
						continue;
				} elseif(in_array($enable_key, $GEN_pages)) {
					$href	= "?page_req=$enable_key";
					$title	= get_GEN_title($enable_key);
					if(!is_readable("$this->data_root/$enable_key.page"))
						continue;
				} else {
					$title	= $enable_key;
					$href	= $links[$enable_key];
				}

				if(!empty($val))	//if its enabled get its weight & build the output
					array_push($toclist, "'$enable_key'	=> array('title' => '$title',	'href' => '$href',	'weight' => ".(empty($w) ? 0 : $w).')');

				$debug_info	.= "\$enable[$enable_key]=$enable[$enable_key],	\$val	= $val\n<br />";
			}
			$error	.= file_put_stuff($pages_file, "<?php\n\$Site_TOClist	= array(\n	".implode(",\n	", $toclist)."\n	);\n?>", 'w');
		}

		return $error;
	}//*/
}
?>