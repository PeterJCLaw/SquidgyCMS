<?php
#name = Theme
#description = Enables site theming
#package = Core - required
#type = content
###

class AdminTheme extends Admin {
	function AdminTheme() {
		parent::__construct();
	}

	function printFormAdmin() {
		$site_theme = Theme::get_site_theme();
		$category['Core']	= FileSystem::Filtered_File_List('Themes', '.template');
		$category['Custom']	= FileSystem::Filtered_File_List('Sites/Custom_Themes', '.template');
		foreach($category as $package => $themes) {
?>
<table id="<?php echo $package.'Theme'; ?>" class="theme">
<caption><?php echo $package.' Theme'; ?></caption>
<tr>
	<th class="L">Theme Name:</th><th class="R">Enabled:</th>
</tr><?php
			$i = 1;
			foreach($themes as $theme) {
				$odd_even	= $i % 2 == 1 ? 'odd' : 'even';
				$id	= "$package|$theme";
				$i++;
				$checked	= $site_theme == $id ? ' checked="checked"' : '';
				$radio_box	= '<input type="radio" class="tick" name="theme" value="'."$id\" id=\"_enable_theme_$id\"$checked />";

				echo "<tr class=\"$odd_even\">
	<td class=\"L\"><label for=\"_enable_theme_$id\">".$theme."</label></td>
	<td class=\"R\">$radio_box</td>
</tr>";
			}
		}
		echo '</table>';
		return;
	}

	function submit($content=0) {
		$theme = $_POST['theme'];

		if(empty($theme))
			log_error('No Theme chosen!');

		$this->data = array($theme);
		return $this->put_data();
	}
}

class BlockTheme extends Block {
	function BlockTheme() {
		parent::__construct();
	}
	function root($args) {
		$site_theme = Theme::get_site_theme();
		list($package, $theme) = explode('|', $site_theme, 2);
		$root = "Themes/";
		return '[[Block::Site-BaseHREF]]'.($package == 'Custom' ? 'Sites/Custom_'.$root : $root);
	}
}

class Theme {
	function get_site_theme() {
		$file = $GLOBALS['data_root'].'/theme.data';
		if(!is_file($file) || !is_readable($file))
			return FALSE;
		$fh = fopen($file, 'r');
		$theme = trim(fgets($fh));
		fclose($fh);
		return $theme;
	}
	function get_site_template() {
		$site_theme = Theme::get_site_theme();
		list($package, $theme) = explode('|', $site_theme, 2);
		if(empty($theme))
			$theme = 'BeSquidgy';
		$file = "Themes/$theme.template";
		return $package == 'Custom' ? 'Sites/Custom_'.$file : $file;
	}
}
?>