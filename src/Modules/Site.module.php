<?php
#name = Site
#description = Links etc that are useful in the page template
#package = Core - required
#type = content
###

class BlockSite extends Block {
	function BlockSite() {
		parent::__construct();
	}

	function BaseHREF($args) {
		return $GLOBALS['base_href'];
	}

	function Head($args) {
		global $page_scripts, $page_head_title, $page_n, $scripts_need_login, $base_href;
		$logged_in = $GLOBALS['_SITE_USER']->is_logged_in();
		if(empty($page_scripts) || (!empty($scripts_need_login) && !$logged_in))
			$page_scripts	= '';
		$out	= <<<HeadOne
<title>$page_head_title</title>
<!-- base href="$base_href" / -->
<link rel="stylesheet" type="text/css" href="${base_href}FSPHP.css" />
<link rel="stylesheet" type="text/css" href="${base_href}SquidgyCMS.css" />
HeadOne;

		if($page_n == 'Home' && $logged_in)
			$out	.= "\n<script type=\"text/javascript\" src=\"".$base_href."scripts.js\"></script>\n";

		return $out.$page_scripts;
	}

	function PageTitle($args) {
		global $page_heading, $page_id;

		if(!empty($args) && Publish::get_alias_from_id($page_id) == '<home>') {
			list($front)	= $args;
			return str_replace('<front>', $page_heading, str_replace('<theme>', BlockTheme::root(), $front));
		}

		return $page_heading;
	}

	function EditLogoutLinks($args) {
		global $_SITE_USER, $page_edit_link, $page_req, $page_n, $base_href;

		if(!$_SITE_USER->is_logged_in())
			return FALSE;
		$out	= '
	<ul id="EditLogoutLinks" class="menu">
		<li class="first"><a href="'.$base_href.'admin">Administration</a></li>';

		$edit_URL = Content::edit_URL();
		if(!empty($edit_URL)) {	//no edit link if the URL fails
			$out	.= '
		<li><a href="'.$base_href.$edit_URL.'">Edit Page</a></li>';
		}

		return $out.'
		<li class="last"><a href="'.$base_href.'?logout=1">Log Out</a></li>
	</ul>';
	}

	function Footer($args) {
		global $who_copyright, $website_name_short, $website_name_long, $SquidgyCMS_version;
		list($full)	= $args;
		$out	= '';
		if(!empty($full) && $full == 'full')
			$out	= '
	Site content copyright &copy; '.$who_copyright.'.
	<br />
	All trademarks remain the property of their legal owners.
	<br />
	Broken Links? Feedback? Please
	<a href="Contact_Us.php?subject='.$website_name_short.'%20Website&amp;target=Webmaster" title="Send them an email using our contact form">email</a>
	the <a href="Committee#Webmaster" title="Who the heck is that?">Webmaster</a>.
	Valid <a href="http://validator.w3.org/check/referer" title="HTML Validation Page, External link">XHTML</a>
	and <a href="http://jigsaw.w3.org/css-validator/check/referer" title="CSS Validation Page, External link">CSS</a>.
	<br />
	The views expressed are made on behalf of the '."$website_name_long (<abbr title=\"$website_name_long\">$website_name_short</abbr>)".'<br />
	and are not necessarily the opinions of the <a href="http://www.soton.ac.uk" title="Have a look at their site, External Link">University of Southampton</a>.';

		return '
<div id="copy" class="maincol">'.$out.'
	<br />
	This website is built using the <abbr title="All Squidgy Content Management System">SquidgyCMS</abbr>'.(empty($SquidgyCMS_version) ? '' : " v$SquidgyCMS_version").' which was developed by and copyright &copy;
	<a href="http://users.ecs.soton.ac.uk/pjcl106/" title="Have a look his site, External Link">Peter Law</a>.
</div><!-- end copy -->';
	}

	function Debug($args) {
		global $debug, $error;
		if($debug) {
			$JS_debug_button = "\n<br />\n<pre id=\"JS_debug\"><button onclick=\"this.parentNode.innerHTML = window.LOG\">Debug JS</button></pre>\n";
			return "\n<br />\n<div id=\"error\">\n\$error='".(empty($error) ? '' : nl2br($error)."\n")."'</div>$JS_debug_button".show_log();
		}
	}
}

class Site {
	function get_requested_id_and_alias() {
		list($page, $query) = explode('/',$_GET['s'],2);

		if(strtolower($page) == 'admin')	//the admin page
			return array('alias' => $page, 'id' => 'admin');

		$publish_info = FileSystem::get_file_assoc($GLOBALS['data_root'].'/publish.data');
		$home = FALSE;
		foreach($publish_info as $info) {
			if(!$info['enable'])
				continue;
			if(!empty($page) && in_array($page, $info)) {
				$info['query'] = $query;
				return $info;
			}
			if(in_array('<home>', $info))
				$home = $info;
		}
		$home['query'] = $query;
		if(empty($page))	//no request means the home page
			return $home;
		return FALSE;
	}
}

