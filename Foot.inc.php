<?php
echo "\n	</div><!-- end content div -->\n".SquidgyParser($template_file, '[[Block::Site-Content]]');
if($debug < 0) {
	echo $debug_info;
	echo show_log();
}
?>