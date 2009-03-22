<?php
class BlockGA extends Block {
	function BlockGA() {
		parent::__construct();
	}

	function script($args) {
		list($id) = $args;
		if(!$GLOBAS['no_google'] && !empty($id))
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
?>