<?php
cscheck(array('success'=>isset($GLOBALS['cauta_uridecode'])));
$cauta_uridecode = $GLOBALS['cauta_uridecode'];

$cauta_initparams = cs('cauta/initparams');
cscheck($cauta_initparams);
extract($cauta_initparams['resp']);
$cauta_form = cs('cauta/form',array('cauta_initparams'=>$cauta_initparams));
cscheck($cauta_form);
$is_success = true;

if (count($cauta_uridecode['rest']) < 1){
	$is_success = false;
}
$spitale_get = cs('spitale/get', array("filters"=>array("rules"=>array(
	array("field"=>"uri","op"=>"eq","data"=>$cauta_uridecode['rest'][0])
))));
if ($spitale_get == null) {$is_success = false;}

if ($is_success){
	$logs_count = cs('logs/count', array(
		'alttype'=>1,
		'altid'=>$spitale_get['id'],
	));
	if (!isset($logs_count['success']) || ($logs_count['success'] == false)) {$is_success = false;}
}

$localitati_spitale_sql = 'SELECT localitati.id';
$localitati_spitale_sql .= '	, localitati.denumire';
$localitati_spitale_sql .= '	, localitati.uri';
$localitati_spitale_sql .= ' FROM localitati_spitale';
$localitati_spitale_sql .= ' LEFT JOIN localitati ON localitati_spitale.localitate = localitati.id';
$localitati_spitale_sql .= ' WHERE localitati_spitale.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($spitale_get['id']);
$localitati_spitale_sql .= ' ORDER BY localitati.id ASC';

$localitati_spitale = cs("_cs_grid/get",array('db_sql'=>$localitati_spitale_sql));
cscheck($localitati_spitale);

$localitate_uri = array();
$localitate_denumire = array();
foreach($localitati_spitale['resp']['rows'] as $item_sd){
	$localitate_uri[] = $item_sd->uri;
	$localitate_denumire[] = array(
		'denumire'=>$item_sd->denumire,
		'uri'=>implode('/',$localitate_uri),
	);
}
$localitate_uri  = implode('/',$localitate_uri);

$localitate_uri1 = array();
foreach($cauta_uridecode['items']['localitati'] as $item_sd){
	$localitate_uri1[] = $item_sd['uri'];
}
$localitate_uri1  = implode('/',$localitate_uri1);
if ($localitate_uri != $localitate_uri1){
	header("Location: " . cs_url_scheme . '://' . cs_url_host 
		. '/doctori/' 
		. $localitate_uri
		. '/' . $spitale_get['uri']
	);
	exit;
}
if ($is_success){
	$spitale_images_grid = cs('spitale_images/grid',array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$spitale_get['id']),
	))));
	if ($spitale_images_grid == null) {$is_success = false;}
}
if ($is_success){
	$manager_sql = 'SELECT users.id AS id';
	$manager_sql .= '	, users.email AS email ';
	$manager_sql .= '	, users.status AS status ';
	$manager_sql .= '	, users.uri AS uri ';
	$manager_sql .= '	, users.date AS date ';
	$manager_sql .= '	, users.nume AS nume ';
	$manager_sql .= '	, users.image AS image ';
	$manager_sql .= ' FROM spitale_users';
	$manager_sql .= ' LEFT JOIN users ON spitale_users.user = users.id';
	$manager_sql .= ' WHERE spitale_users.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($spitale_get['id']);
	$manager_sql .= ' AND spitale_users.level >= '. user_level_manager;
	$manager_sql .= ' LIMIT 0,1 ';
	
	$manager = cs("_cs_grid/get",array('db_sql'=>$manager_sql));
	
	if (!isset($manager['success']) || ($manager['success'] == false)) {$is_success = false;}
	if ($manager['resp']['records'] == 0){
		$is_success = false;
	}else{
		$manager = json_decode(json_encode($manager['resp']['rows'][0]),true);		
	}
}
$users_getspitalusers = null;
if ($is_success){
	$pag_maxrows = 10;
	$p_page = 1;
	if (isset($_GET['p_page']) && (intval($_GET['p_page'])>0)){$p_page = intval($_GET['p_page']);}
	
	$users_getspitalusers_sql = "SELECT SQL_CALC_FOUND_ROWS ";
	$users_getspitalusers_sql .= "    users.id";
	$users_getspitalusers_sql .= "    ,users.email";
	$users_getspitalusers_sql .= "    ,users.password";
	$users_getspitalusers_sql .= "    , users.date";
	$users_getspitalusers_sql .= "    , users.nume";
	$users_getspitalusers_sql .= "    , users.uri";
	$users_getspitalusers_sql .= "    , users.image";
	$users_getspitalusers_sql .= "    , users.status";
	$users_getspitalusers_sql .= "    , specializari.denumire as specializari_denumire";
	$users_getspitalusers_sql .= " FROM spitale_users";
	$users_getspitalusers_sql .=  " LEFT JOIN users ON users.id = spitale_users.user";
	$users_getspitalusers_sql .=  " LEFT JOIN specializari_user_spitale ON specializari_user_spitale.spital = spitale_users.spital AND specializari_user_spitale.user = spitale_users.user";
	$users_getspitalusers_sql .=  " LEFT JOIN specializari ON specializari_user_spitale.specializare = specializari.id";
	$users_getspitalusers_sql .=  " WHERE spitale_users.spital = " . $spitale_get['id'];
	$users_getspitalusers_sql .=  " AND specializari.parent = 0";
	$users_getspitalusers_sql .=  " AND specializari_user_spitale.specializare IS NOT NULL";
	$users_getspitalusers_sql .=  " __order__ __limit__";

	$users_getspitalusers = cs("_cs_grid/get",array(
		'db_sql'=>$users_getspitalusers_sql,
		'page'=>$p_page,
		'rows'=>$pag_maxrows,
	));
	
	//$users_getspitalusers = cs('users/getspitalusers',array('spital'=>$spitale_get['id'],'level'=>user_level_doctor));
	if (!isset($users_getspitalusers['success']) || ($users_getspitalusers['success'] == false)) {$is_success = false;}
}
$GLOBALS['mobile_ismobile'] = cs('mobile/ismobile');
if ($GLOBALS['mobile_ismobile'] == true){
	require_once(cs_path . DIRECTORY_SEPARATOR . 'doctori_spital_mob.php' );
}else{
	require_once(cs_path . DIRECTORY_SEPARATOR . 'doctori_spital_desktop.php' );
}
?>