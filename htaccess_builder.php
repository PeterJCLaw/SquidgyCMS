<?php
include 'Modules/FileSystem.php';
include 'functions_General.inc.php';

//find the current path to this file from the url - ie get the web site root
//figure out the root of this install the location of this file and compare

list($install_root) = get_page_basics();

$htaccess = <<<HTA
ErrorDocument 400 ${install_root}index.php?id=400
ErrorDocument 401 ${install_root}index.php?id=401
ErrorDocument 403 ${install_root}index.php?id=403
ErrorDocument 404 ${install_root}index.php?id=404
ErrorDocument 500 ${install_root}index.php?id=500

<IfModule mod_rewrite.c>
	RewriteEngine on
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ ${install_root}index.php?page_req=$1 [L,QSA]
</IfModule>

HTA;

echo '<code><pre>'.htmlspecialchars($htaccess).'</pre></code>';


?>