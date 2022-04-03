<?php
$GLOBALS['logs_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'ip'				=>array('type'=>'text',),
	'browser'			=>array('type'=>'int',),
	'user'				=>array('type'=>'int',),
	'alttype'			=>array('type'=>'int',), //0 type plainurl 1 typespital 2 typedoctor
	'altid'				=>array('type'=>'int',),
	'url'				=>array('type'=>'text',),
	'date'				=>array('type'=>'datetime',),
);
function logs_get($p_arr = array()){
	$ret = null;
	$list = logs_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["ip"] = $list['resp']['rows'][0]->ip;
		$ret["browser"] = intval($list['resp']['rows'][0]->browser);
		$ret["user"] = intval($list['resp']['rows'][0]->user);
		$ret["alttype"] = intval($list['resp']['rows'][0]->alttype);
		$ret["altid"] = intval($list['resp']['rows'][0]->altid);
		$ret["url"] = $list['resp']['rows'][0]->url;
		$ret["date"] = $list['resp']['rows'][0]->date;
	}
	return $ret;
}
function logs_grid($p_arr = array()){
	global $logs_cols;
	$p_arr['db_cols'] = $logs_cols;
	$p_arr['db_table'] = 'logs';
	return cs("_cs_grid/get",$p_arr);
}
function logs_update($p_arr = array()){
	global $logs_cols;
	$p_arr['db_cols'] = $logs_cols;
	$p_arr['db_table'] = 'logs';
	return cs("_cs_grid/update",$p_arr);
}
function logs_add($p_arr = array()){
	$ret = array('success'=>false);
	if (!isset($p_arr['ip'])) $p_arr['ip'] = $_SERVER["REMOTE_ADDR"];
	if (
		(!isset($p_arr['url']))
		&& isset($p_arr['alttype']) 
		&& (intval($p_arr['alttype']) == 0)
	) $p_arr['url'] = $_SERVER["REQUEST_URI"];
	if (!isset($p_arr['browser'])) {
		if (isset($_SESSION['browser_id'])) {$p_arr['browser'] = $_SESSION['browser_id'];}
	};
	if (!isset($p_arr['user'])) {
		if (isset($_SESSION['cs_users_id'])) {$p_arr['user'] = $_SESSION['cs_users_id'];}
	};
	if (!isset($p_arr['date']))  $p_arr['date'] = date('Y-m-d H:i:s');
		
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$ret['logs_update'] = logs_update($p_arr);
	$ret['p_arr'] = $p_arr;
	if (!isset($ret['logs_update']['success'])||($ret['logs_update']['success'] != true)){return $ret;}
	
	$ret['success'] = true;
	return $ret;
}
function logs_count($p_arr = array()){
	$ret = array('success'=>false,'resp'=>array());
	if (!isset($p_arr['alttype'])) {$ret['error'] = 'check parameter - alttype'; return $ret;};
	if (!isset($p_arr['altid'])) {$ret['error'] = 'check parameter - altid'; return $ret;};
	$ret['logs_grid'] = logs_grid(array(
		"filters"=>array("rules"=>array(
			array("field"=>"alttype","op"=>"eq","data"=>$p_arr['alttype']),
			array("field"=>"altid","op"=>"eq","data"=>$p_arr['altid']),
		)),
		"rows"=>1,
	));
	if (!isset($ret['logs_grid']['success']) || ($ret['logs_grid']['success'] != true)) return $ret;
	$ret['resp']['count'] = $ret['logs_grid']['resp']['total'];
	$ret['success'] = true;
	return $ret;
}
function logs_graphdata($p_arr = array()){
	global $logs_cols;
	$p_arr['db_cols'] = $logs_cols;
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$ret = array('success'=>false,'resp'=>array());
	if (!isset($p_arr['alttype'])) {$ret['error'] = 'check parameter - alttype'; return $ret;};
	if (!isset($p_arr['altid'])) {$ret['error'] = 'check parameter - altid'; return $ret;};
	$p_arr['alttype'] = $GLOBALS['cs_db_conn']->real_escape_string($p_arr['alttype']);
	$p_arr['altid'] = $GLOBALS['cs_db_conn']->real_escape_string($p_arr['altid']);
	
	if (!isset($p_arr['days'])) {$p_arr['days'] = 29;};
	$p_arr['days'] = intval($GLOBALS['cs_db_conn']->real_escape_string($p_arr['days']));
	if ($p_arr['days'] > 29 ){$p_arr['days'] = 29;};
	
	$ret['db_sql'] = "select SQL_CALC_FOUND_ROWS count(*) as count, date(date) as date"
		. " from logs"
		. " where (alttype = " . $p_arr['alttype'] . ") and (altid = " . $p_arr['altid'] . ") and (date > '" . date('Y-m-d',	strtotime('-' . $p_arr['days'] . ' day',time())) . "')"
		. " GROUP BY date(`date`)";
	$r = $GLOBALS['cs_db_conn']->query($ret['db_sql']);
	$r1 = $GLOBALS['cs_db_conn']->query('SELECT FOUND_ROWS()');
	if($r === false) {return $ret;}
	$row1 = $r1->fetch_object();
	if (isset($r->num_rows)&&($r->num_rows>0)){
		$ret['grid']['records'] = intval($row1->{'FOUND_ROWS()'});
		$ret['grid']['rows'] = array();
		while($row = $r->fetch_object()){
			$ret['grid']['rows'][] = $row;
		}
	}else{
		$ret['grid']['records'] = 0;
		$ret['grid']['rows'] = array();
	}
	$ret['resp']['viewdata'] = array();
	$j = 0;
	for ($i = $p_arr['days']; $i >= 0; $i--){
		$item = array(
			'date' => date('Y-m-d',	strtotime('-' . $i . ' day',time())),
			'count' => 0,
		);
		if ((isset($ret['grid']['rows'][$j])) && ($ret['grid']['rows'][$j]->date == $item['date'])){
			$item['count'] = intval($ret['grid']['rows'][$j]->count);
			$j++;
		}
		$ret['resp']['viewdata'][] = $item;
	}
	$ret['p_arr'] = $p_arr;
	$ret['success'] = true;
	return $ret;
}
?>