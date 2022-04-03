<?php
require_once("_cs_config.php");
$php_sapi_name = php_sapi_name();
if ($php_sapi_name == 'cli'){
	exit();
	cron_planif();
	echo "run";
	exit();
}
if (!isset($_SESSION['cs_users_id'])){
	header("Location: " . cs_url_scheme . '://' . cs_url_host 
		. '/csapi/users/login_html' 
		. '?urlnext=' . urldecode(cs_url_scheme . '://' . cs_url_host . $_SERVER['REQUEST_URI'])
	);
	exit;
}
$is_success = true;
if ($is_success){
	$spitale_users_getlevel = cs('spitale_users/getlevel');
	if (isset($spitale_users_getlevel['success']) && ($spitale_users_getlevel['success'] == true)) {
		$is_success = true;
	}		
}

if ($spitale_users_getlevel['resp'] < user_level_admin) { 
	$page_error = 'you are not autorized to this level..';; 
	$is_success = false;
}

?>
<?php
function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari medicale online</title>
	<meta name="robots" content="noindex, nofollow" />	
	<style>
	</style>
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
} 
$GLOBALS['header_ob'] = header_ob();
require_once(cs_path . DIRECTORY_SEPARATOR . 'header.php' );
?>
<?php

function cron_planif($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array());
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');

	$ret['spitale_users_grid'] = cs('spitale_users/grid',array("rows"=>200,"filters"=>array("rules"=>array(
		array("field"=>"autoplanificare","op"=>"eq","data"=>1),
		array("field"=>"cronnext","op"=>"le","data"=>date('Y-m-d')),
	))));
	if (!isset($ret['spitale_users_grid']['success']) || ($ret['spitale_users_grid']['success'] != true)) {$ret['error'] = 'error spitale_users_grid '; return $ret;}
	$ret['spitale_users_arr'] = array();
	if ($ret['spitale_users_grid']['resp']['records'] > 0){
		foreach($ret['spitale_users_grid']['resp']['rows'] as $spitale_users){
			$planifauto_generate = cs('planifauto/generate',array(
				'personal'=>array(
					array(
						'spital'=>$spitale_users->spital,
						'doctor'=>$spitale_users->user,
					)
				)
			));
			$ret['spitale_users_arr'][] = $planifauto_generate;
			if (!isset($planifauto_generate['success']) || ($planifauto_generate['success'] != true)) {$ret['error'] = 'error planifauto_generate '; return $ret;}
		}
	}
	return $ret;
}

$cron_planif = cron_planif();
?>
<?php 
function footer_ob(){
	global $legenda_grid,$spital_activ;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>