<?php
function firebase_register($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'d1');
	if (!defined('cs_firebase_db_name')){$ret['error'] = 'check cs_firebase_db_name'; return $ret;}
	$ret['path'] = 'users';
	if (isset($_SESSION['firebase_path']) && $_SESSION['firebase_path'] != '') $ret['path'] = $_SESSION['firebase_path'];
	if (isset($p_arr['path']) && $p_arr['path'] != '') $ret['path'] = $p_arr['path'];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	$ret['ch_url'] = "https://" . cs_firebase_db_name . ".firebaseio.com/" . $ret['path'] . ".json";
	curl_setopt($ch, CURLOPT_URL, $ret['ch_url']);
	$ret['ch_data'] = json_encode(array(
		'updates'=>array(
			'timestamp'=>date('Y-m-d H:i:s'),
		),
		'secret'=>cs_firebase_secret,
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $ret['ch_data']);
	$ret['ch_resp'] = curl_exec($ch);
	if ($ret['ch_resp'] === false) {return $ret;}
	$ret['ch_resp_json'] = json_decode($ret['ch_resp'],true);
	if (!isset($ret['ch_resp_json']['name'])) {return $ret;}
	$ret['resp']['name'] = $ret['ch_resp_json']['name'];
	$ret['success'] = true;
	return $ret;
}
function firebase_action_dispatch($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	if (!isset($_SESSION['firebase_activeid']) || ($_SESSION['firebase_activeid'] == '')) {$ret['error'] = 'check firebase_activeid'; return $ret;}
	$ret['path'] = 'users';
	$ret['p_arr'] = $p_arr;
	if (isset($_SESSION['firebase_path']) && $_SESSION['firebase_path'] != '') $ret['path'] = $_SESSION['firebase_path'];
	if (isset($p_arr['path']) && $p_arr['path'] != '') $ret['path'] = $p_arr['path'];
	
	if (!isset($p_arr['action'])){$ret['error'] = 'paramenter check action'; return $ret;}
	$ret['firebaseid'] = '';
	if (isset($p_arr['firebaseid'])) $ret['firebaseid'] = $p_arr['firebaseid'];
	if (isset($p_arr['userid'])){
		$ret['users_get'] = cs("users/get",array("filters"=>array("groupOp"=>"AND","rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$p_arr['userid'])
		))));
		if ($ret['users_get'] == null) return $ret;
		$ret['firebaseid'] = $ret['users_get']['firebase'];
	}
	if (isset($p_arr['browserid'])){
		$ret['browser_get'] = cs("browser/get",array("filters"=>array("groupOp"=>"AND","rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$p_arr['browserid'])
		))));
		if ($ret['browser_get'] == null) return $ret;
		$ret['firebaseid'] = $ret['browser_get']['firebase'];
	}
	if ($ret['firebaseid'] == '') {$ret['error'] = 'check firebase id'; return $ret;}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	$ret['ch_url'] = "https://" 
		. cs_firebase_db_name 
		. ".firebaseio.com/" 
		. $ret['path'] . "/" 
		. $ret['firebaseid'] . ".json";
	curl_setopt($ch, CURLOPT_URL, $ret['ch_url']);
	$ret['ch_data'] = json_encode(array(
		'updates'=>array(
			'action'=>$p_arr['action'],
			'timestamp'=>date('Y-m-d H:i:s'),
		),
		'secret'=>cs_firebase_secret,
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $ret['ch_data']);
	$ret['ch_resp'] = curl_exec($ch);
	if ($ret['ch_resp'] === false) {return $ret;}
	$ret['ch_resp_json'] = json_decode($ret['ch_resp'],true);
	if (!isset($ret['ch_resp_json']['updates'])) {return $ret;}
	$ret['success'] = true;
	return $ret;
}
?>