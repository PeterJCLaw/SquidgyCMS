<pre>
<?php
require_once('../src/Modules/FileSystem.php');
require_once('../src/functions_General.inc.php');

//find the current path to this file from the url - ie get the web site root
//figure out the root of this install the location of this file and compare

list($root) = get_page_basics();

$install_root = substr($root, 0, -6).'src/';

$htaccess = <<<HTA
ErrorDocument 400 ${install_root}index.php?id=400
ErrorDocument 401 ${install_root}index.php?id=401
ErrorDocument 403 ${install_root}index.php?id=403
ErrorDocument 404 ${install_root}index.php?id=404
ErrorDocument 500 ${install_root}index.php?id=500

<IfModule mod_rewrite.c>
	RewriteBase $install_root
	RewriteEngine on
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ ${install_root}index.php?s=$1 [L,QSA]
</IfModule>

HTA;

echo htmlspecialchars($htaccess);


