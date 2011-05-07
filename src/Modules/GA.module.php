<?php
#name = Google Analytics
#description = Handles the insertion of a Google Analytics script
#package = Core - optional
#type = content
###

class BlockGA extends Block {
	function BlockGA() {
		parent::__construct();
	}

	function script($args) {
		list($id) = $args;
		if(!$GLOBALS['no_google'] && strtolower($GLOBALS['page_n']) != 'admin' && !empty($id))
			return <<<SCR
<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
	var pageTracker = _gat._getTracker("$id");
	pageTracker._initData();
	pageTracker._trackPageview();
</script>
SCR;
	}
}
