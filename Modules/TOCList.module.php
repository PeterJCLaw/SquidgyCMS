<?php
#name = TOCList
#description = The table of contents and page aliasing for the site
#package = Core - optional
#type = content
###

class AdminTOCList extends Admin {
	function AdminTOCList() {
		parent::__construct(-1, -19);
	}

	function printFormAdmin() {
		global $GEN_pages, $FSCMS_pages;
		$Site_TOClist	= get_TOClist();
		natsort($GEN_pages);
		$Site_pages	= array_merge($GEN_pages, $FSCMS_pages);
		?>
			<table id="admin_manage_tbl" class="admin_tbl"><tr>
				<th title="Click on the page title link to edit the page">Page:</th>
				<th title="Tick the box to enable the page" class="M T">Enable:</th>
				<th title="Give a page a url alias, requires Clean_URLs" class="M T">Alias:</th>
				<th title="Integers only, low numbers float above high numbers" class="M T">Weight:</th>
			</tr><?php
		$check	= '<input type="checkbox" class="tick" name="';
		foreach($Site_pages as $Page) {
			$alias_box	= $weight_box	= $enable_box	= $page_link	= '&nbsp;';
			if(!empty($Page)) {
				$weight_box	= '<input name="weight['.$Page.']"'.(empty($Site_TOClist[$Page]['weight']) ? '' : ' value="'.$Site_TOClist[$Page]['weight'].'"').' type="text" maxlength="2" size="2" class="num" />';
				$enable_box	= $check.'enable['.$Page.']"'.(isset($Site_TOClist[$Page]) ? ' checked="checked"' : '').' />';

				if(in_array($Page, $GEN_pages)) {	//if its a user page
					$alias_box	= '<input name="alias['.$Page.']"'.(empty($Site_TOClist[$Page]['alias']) ? '' : ' value="'.$Site_TOClist[$Page]['alias'].'"').' type="text" class="alias" />';
					if($Page == '1-Home') {
						$alias_box	= '[none]';
						$weight_box	= '[top]';
						$page_name	= "Home";
						$Page	= './';
						$enable_box	= $check.'Home" checked="checked" disabled="disabled" /><input type="hidden" name="enable[Home]" value="on" />';
					} else
						$page_name	= get_GEN_title($Page);

					$page_link	= '<a href="'.(empty($Site_TOClist[$Page]['alias']) ? $Page : $Site_TOClist[$Page]['alias']).'" title="View the \''.$page_name.'\' page">'.$page_name.'</a>';
				} else {
					$alias_box	= '[none]';
					$page_link	= '<a href="'.$Page.'" title="Edit your committee page profile">'.str_replace("_", " ", substr($Page, 0, -4)).'</a>';
				}
			}

			echo '
			<tr>
				<td class="L">'.$page_link.'</td>
				<td class="T M">'.$enable_box.'</td>
				<td class="T M">'.$alias_box.'</td>
				<td class="T M">'.$weight_box.'</td>
			</tr>';
		} ?>
			</table>
<?php return;
	}

	function submit() {
		global $debug_info, $alias, $enable, $weight, $links, $FSCMS_pages, $pages_file, $GEN_pages;
		$error	= "";

		if(!empty($enable)) {
			$toclist	= array();
			foreach($enable as $enable_key => $val) {
				$w	= (int) $weight[$enable_key];
				$a	= $alias[$enable_key];

				if($enable_key == 'Home') {
					$href	= './';
					$title	= 'Home';
					unset($a);
				} elseif(in_array($enable_key, $FSCMS_pages)) {
					$href	= $enable_key;
					$title	= str_replace("_", " ", substr($enable_key, 0, -4));
					if(!is_readable($href))
						continue;
				} elseif(in_array($enable_key, $GEN_pages)) {
					$href	= "?p=$enable_key";
					$title	= get_GEN_title($enable_key);
					if(!is_readable("$this->data_root/$enable_key.page"))
						continue;
				} else {
					$title	= $enable_key;
					$href	= $links[$enable_key];
				}

				if(!empty($val))	//if its enabled get its weight & build the output
					array_push($toclist, "'$enable_key'	=> array('title' => '$title',	'href' => '$href',	'weight' => ".(empty($w) ? 0 : $w).(empty($a) ? '' : ",	'alias' => '$a'").')');

				$debug_info	.= "\$enable[$enable_key]=$enable[$enable_key],	\$val	= $val\n<br />";
			}
			
		if(!empty($alias)) {
			$alias_list	= array();
			foreach($alias as $page => $a) {
				if(!empty($a))
					array_push($alias_list, "'$a' => '$page'");
			}
		}
		array_push($toclist, "'AliasList'	=> array(\n		".implode(",\n		", $alias_list)."\n		)");

			$error	.= FileSystem::file_put_stuff($pages_file, "<?php\n\$Site_TOClist	= array(\n	".implode(",\n	", $toclist)."\n	);\n?>", 'w');
		}

		return $error;
	}//*/
}
?>