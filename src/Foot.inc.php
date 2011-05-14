<?php
echo "\n	</div><!-- end content div -->\n".Content::SquidgyParserFile($template_file, '[[Block::Site-Content]]');
if($debug < 0) {
	echo show_log();
}
