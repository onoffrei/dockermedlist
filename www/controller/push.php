<?php
$GLOBALS['push_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'endpoint'			=>array('type'=>'text',	),
	'key'				=>array('type'=>'text',	),
	'token'				=>array('type'=>'text',	),
	'browser'			=>array('type'=>'int',	),
	'user'				=>array('type'=>'int',	),
	'date'				=>array('type'=>'datetime',	),
	'lastsent'			=>array('type'=>'datetime',	),
	'isread'			=>array('type'=>'int',	),
);
function push_get($p_arr = array()){
	$ret = null;
	$list = push_grid($p_arr);
	if (isset($list['resp']['rows'])&&(count($list['resp']['rows'])>0)){
		$list = $list['resp'];
		$ret["id"] = intval($list['rows'][0]->id);
		$ret["endpoint"] = $list['rows'][0]->endpoint;
		$ret["key"] = $list['rows'][0]->key;
		$ret["token"] = $list['rows'][0]->token;
		$ret["browser"] = intval($list['rows'][0]->browser);
		$ret["user"] = intval($list['rows'][0]->user);
		$ret["date"] = $list['rows'][0]->date;
		$ret["lastsent"] = $list['rows'][0]->lastsent;
		$ret["isread"] = intval($list['rows'][0]->isread);
	}
	return $ret;
}
function push_grid($p_arr = array()){
	global $push_cols;
	$p_arr['db_cols'] = $push_cols;
	$p_arr['db_table'] = 'push';
	return cs("_cs_grid/get",$p_arr);
}
function push_update($p_arr = array()){
	global $push_cols;
	$p_arr['db_cols'] = $push_cols;
	$p_arr['db_table'] = 'push';
	return cs("_cs_grid/update",$p_arr);
}
require __DIR__ . '/../push/vendor/autoload.php';
use Minishlink\WebPush\WebPush;
function push_send_user($p_arr = array()){
	$ret = array('success'=>false);
	if (!isset($p_arr['user'])||($p_arr['user'] == '')){$ret['error'] = 'user??';return $ret;}
	if (!isset($p_arr['message'])||($p_arr['message'] == '')){$ret['error'] = 'mising message';return $ret;}
	$send = array(
		"message"=>$p_arr['message'],
		"filters"=>array("rules"=>array(array("field"=>"user","op"=>"eq","data"=>$p_arr['user']))),
	);
	return push_send($send);
}
function push_send($p_arr = array()){
	$ret = array('success'=>false,'resp'=>array());
	$ret['p_arr'] = $p_arr;
	if (!defined('cs_push_publickey')){$ret['error'] = 'publickey??';return $ret;}
	if (!isset($p_arr['message'])||($p_arr['message'] == '')){$ret['error'] = 'mising message';return $ret;}
	if (gettype($p_arr['message']) == 'array'){
		$ret['message'] = $p_arr['message'];
	}else{
	$ret['message'] = array(
		'title'=>'Medlist update',
		'message'=>$p_arr['message'],
	);
	}
	$ret['push_grid'] = push_grid($p_arr); 
	if (!isset($ret['push_grid']['success']) || ($ret['push_grid']['success'] != true)) return $ret;
	if(count($ret['push_grid']['resp']['rows']) == 0) {$ret['error'] = 'no entry';return $ret;}
	$auth = array(
		'VAPID' => array(
			'subject' => 'mailto:' . cs_email,
			'publicKey' => cs_push_publickey,
			'privateKey' => cs_push_privatekey, // in the real world, this would be in a secret file
		),
	);
	$webPush = new WebPush($auth);
	$ret['resp']['response'] = array();
	$ret['success'] = true;
	foreach($ret['push_grid']['resp']['rows'] as $ri => $row){
		$log = array();
		$ret['push_cansend_check'] = push_cansend_check(array('row'=>$row));
		if (!isset($ret['push_cansend_check']['success'])||($ret['push_cansend_check']['success'] != true)){return $ret;}
		$log['push_cansend_check'] = $ret['push_cansend_check'];
		if ($ret['push_cansend_check']['resp']){
			if (version_compare(PHP_VERSION, '7.1.20') > 0) {
				$resp = false;
				$log['version'] = PHP_VERSION;
			}else{
				$log['version'] = 'ok';
				$resp = $webPush->sendNotification(
					$row->endpoint,
					json_encode($ret['message']),// $p_arr['message'],
					$row->key,
					$row->token,
					true
				);
			}
			if ($resp == true) {
				$ret['push_update'] = push_update(array('oper'=>'edit','id'=>$row->id,'lastsent'=>date('Y-m-d H:i:s'),'isread'=>0));
				if (!isset($ret['push_update']['success'])||($ret['push_update']['success'] != true)){return $ret;}
			}
			$log['sendNotification'] = $resp;
		}
		$ret['resp']['response'][] = $log;
	}
	return $ret;
}
function push_cansend_check($p_arr = array()){
	$ret = array('success'=>false,'resp'=>false);
	if (!isset($p_arr['row'])){$ret['error'] = 'row?';return $ret;}
	$p_arr['row'] = json_decode(json_encode($p_arr['row']),true);
	
	$ret['users_get'] = cs('users/get',array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['row']['user']),
	))));
	if ($ret['users_get'] == null){$ret['error'] = 'users_get??'; return $ret;}
	$ret['isonline'] = intval($ret['users_get']['status']);
	
	if ((strtotime(date('Y-m-d H:i:s')) - strtotime($ret['users_get']['date'])) > (usersstatus_maxidle)){
		$ret['isonline'] = 0;
	}
	
	if (
		((strtotime(date('Y-m-d H:i:s')) - strtotime($p_arr['row']['lastsent'])) > usersstatus_maxidle)
		|| (intval($p_arr['row']['isread']) > 0)
	){
		$ret['resp'] = true;
	}
	//if ($ret['isonline'] == 1){$ret['resp'] = false;}
	$ret['success'] = true;
	return $ret;
}
function push_cansend_markread($p_arr = array()){
	$ret = array('success'=>false);
	if (!isset($p_arr['user'])){$ret['error'] = 'user?';return $ret;}

	$ret['update_sql'] = 'UPDATE push SET isread = 1 WHERE isread = 0 AND user = ' . $p_arr['user'];
	$ret['update'] = cs("_cs_grid/get",array('db_sql'=>$ret['update_sql']));
	if (!isset($ret['update']['success'])||($ret['update']['success'] != true)){$ret['error'] = 'update??';return $ret;}	

	$ret['success'] = true;
	return $ret;
}
function push_subscribe($p_arr = array()){
	$ret = array('success'=>false);
	$add = array();
	if (!isset($p_arr['endpoint'])){$ret['error'] = 'mising endpoint';return $ret;}
	if (!isset($p_arr['keys']['p256dh'])){$ret['error'] = 'mising key';return $ret;}
	if (!isset($p_arr['keys']['auth'])){$ret['error'] = 'mising token';return $ret;}
	if (!isset($_SESSION['browser_id'])) {$ret['error'] = 'mising cookie';return $ret;}
	$ret['push_get'] = push_get(array(
		"filters"=>array("groupOp"=>"OR","rules"=>array(
			array("field"=>"endpoint","op"=>"eq","data"=>$p_arr['endpoint']),
			array("field"=>"browser","op"=>"eq","data"=>$_SESSION['browser_id'])))
	)); 
	if ($ret['push_get'] != null){
		$r = $GLOBALS['cs_db_conn']->query("UPDATE `push` SET "
			. " `endpoint` = '" . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['endpoint']) . "'"
			. ", `browser` = '" . $GLOBALS['cs_db_conn']->real_escape_string($_SESSION['browser_id']) . "'"
			. ", `key` = '" . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['keys']['p256dh']) . "'"
			. ", `token` = '" . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['keys']['auth']) . "'"
			. ", `date` = '" . date('Y-m-d H:i:s') . "'"
			. " WHERE "
			. "`endpoint` = '" .  $GLOBALS['cs_db_conn']->real_escape_string($p_arr['endpoint']) . "'"
			. " OR `browserid` = '" .  $GLOBALS['cs_db_conn']->real_escape_string($_SESSION['browser_id']) . "'"
			);
		if($r === false) {$ret['error'] = 'sql update'; return $ret;}
		$ret['success'] = true;
	}else{
		$add['endpoint'] = $p_arr['endpoint'];
		$add['key'] = $p_arr['keys']['p256dh'];
		$add['token'] = $p_arr['keys']['auth'];
		$add['browser'] = $_SESSION['browser_id'];
		if (isset($_SESSION['cs_users_id'])) {$add['user'] = $_SESSION['cs_users_id'];}
		$add['date'] = date('Y-m-d H:i:s');
		$add['oper'] = 'add';
		$add['id'] = 'null';
		$ret['push_update'] = push_update($add);
		if (isset($ret['push_update']['success'])&&($ret['push_update']['success'] == true)){
			$ret['success'] = true;
			unset($ret['error']);
		}
	}
	return $ret;
}
function push_unsubscribe($p_arr = array()){
	$ret = array('success'=>false);
	if (!isset($_SESSION['browser_id'])) {$ret['error'] = 'mising cookie';return $ret;}
	if (!isset($p_arr['endpoint'])){$p_arr['endpoint'] = '';}
	$ret['push_get'] = push_get(array(
		"filters"=>array("groupOp"=>"OR","rules"=>array(
			array("field"=>"endpoint","op"=>"eq","data"=>$p_arr['endpoint']),
			array("field"=>"browser","op"=>"eq","data"=>$_SESSION['browser_id'])))
	)); 
	if ($ret['push_get'] != null){
		$r = $GLOBALS['cs_db_conn']->query("DELETE FROM `push` WHERE " 
			. "`endpoint` = '" .  $GLOBALS['cs_db_conn']->real_escape_string($p_arr['endpoint']) . "'"
			. " OR `browser` = '" .  $GLOBALS['cs_db_conn']->real_escape_string($_SESSION['browser_id']) . "'"
			);
		if($r === false) {$ret['error'] = 'sql del'; return $ret;}
	}
	$ret['success'] = true;
	return $ret;
}
?>