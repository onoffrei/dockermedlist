<?php
require_once("_cs_config.php");

if (!isset($_SESSION['cs_users_id'])){
	header("Location: " . cs_url_scheme . '://' . cs_url_host 
		. '/csapi/users/login_html' 
		. '?urlnext=' . urldecode(cs_url_scheme . '://' . cs_url_host . $_SERVER['REQUEST_URI'])
	);
	exit;
}
phpinfo();