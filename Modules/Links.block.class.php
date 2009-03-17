<?php
class Links extends Block {
	function Links() {
		parent::__construct();
	}

	function block($args) {
		$Links	= get_file_assoc($this->data_file, array('href', 'text', 'title'));
		
		if(empty($Links))
			return;
		
		multi2dSortAsc($Links, 'title');
		$Link	= reset($Links);
		$out	= "\n	<ul class=\"links_list\">";
		$last	= count($Links)-1;
		$i	= 0;
		
		while(!empty($Link)) {
			$class	= ($last == 0 ? ' class="first last"' : ($i == 0 ? ' class="first"' : ($i == $last ? ' class="last"' : '')));
			$title	= (empty($Link['title']) ? '' : ' title="'.$Link['title'].(strpos($Link['href'], 'http') === 0 ? ', External Link' : '').'"');
			$out	.= '
		<li'.$class.'><a href="'.$Link['href'].'"'.$title.'>'.$Link['text'].'</a></li>';
			$Link	= next($Links);
			$i++;
		}
		$out	.="\n	</ul>";
		return $out;
	}
}
?>