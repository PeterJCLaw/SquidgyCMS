<?php
#name = Themes
#description = Enables management of site themes
#package = Core - required
#type = system
###

class AdminThemes extends Admin {
	function AdminThemes() {
		parent::__construct();
	}

	function printFormAdmin() {
		$site_theme = Themes::get_site_theme();
		$category['Core']	= FileSystem::Filtered_File_List('Themes', '.template');
		$category['Custom']	= FileSystem::Filtered_File_List('Sites/Custom_Themes', '.template');
		foreach($category as $package => $themes) {
?>
<table id="<?php echo $package.'Themes'; ?>" class="themes">
<caption><?php echo $package.' Themes'; ?></caption>
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

class BlockThemes extends Block {
	function BlockThemes() {
		parent::__construct();
	}
	function block($args) {

		if(!is_readable($this->data_file))
			return '<span id="themesblah" style="display: none;"> (The file was not readable)</span>';

	}
}

class Themes {
	function get_site_theme() {
		$file = $GLOBALS['data_root'].'/themes.data';
		if(!is_file($file) || !is_readable($file))
			return FALSE;
		$fh = fopen($file, 'r');
		$theme = trim(fgets($fh));
		fclose($fh);
		return $theme;
	}
	function get_site_template() {
		$site_theme = Themes::get_site_theme();
		list($package, $theme) = explode('|', $site_theme, 2);
		if(empty($theme))
			$theme = 'BeSquidgy';
		$file = "Themes/$theme.template";
		return $package == 'Custom' ? 'Sites/Custom_'.$file : $file;
	}
}
?>