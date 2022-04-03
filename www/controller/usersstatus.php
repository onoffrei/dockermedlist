<?php
$GLOBALS['usersstatus_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'hunter'			=>array('type'=>'int',	),
	'pray'				=>array('type'=>'int',	),
	'status'			=>array('type'=>'int',	),
	'date'				=>array('type'=>'datetime',	),
);
function usersstatus_get($p_arr = array()){
	$ret = null;
	$list = usersstatus_grid($p_arr);
	if (isset($list['resp']['rows'])&&(count($list['resp']['rows'])>0)){
		$list = $list['resp'];
		$ret["id"] = intval($list['rows'][0]->id);
		$ret["hunter"] = intval($list['rows'][0]->hunter);
		$ret["pray"] = intval($list['rows'][0]->pray);
		$ret["status"] = intval($list['rows'][0]->status);
		$ret["date"] = $list['rows'][0]->date;	
	}
	return $ret;
}
function usersstatus_grid($p_arr = array()){
	global $usersstatus_cols;
	$p_arr['db_cols'] = $usersstatus_cols;
	$p_arr['db_table'] = 'usersstatus';
	return cs("_cs_grid/get",$p_arr);
}
function usersstatus_update($p_arr = array()){
	global $usersstatus_cols;
	$ret['p_arr'] = $p_arr;
	$p_arr['db_cols'] = $usersstatus_cols;
	$p_arr['db_table'] = 'usersstatus';
	return cs("_cs_grid/update",$p_arr);
}
function usersstatus_test($p_arr = array()){
	$ret = array('success'=>false);
	$ret['timecurent'] = strtotime(date('Y-m-d H:i:s'));
	$ret['timeuser'] = strtotime($_SESSION['cs_users_date']);
	$ret['dif'] = $ret['timeuser'] - $ret['timecurent'];
	$ret['success'] = true;
	return $ret;
}
function usersstatus_on_logout($p_arr = array()){
	if (!defined('usersstatus_maxidle')){$ret['error'] = 'usersstatus_maxidle??'; return $ret;}
	//cahnge status (where i am pray)
	//notify (where i am pray)
	//clear watch list(where i hunter)
	//table user field status -- allreadu done
}
function usersstatus_check($p_arr = array()){
	$ret = array('success'=>false,'resp'=>0);
	if (!defined('usersstatus_maxidle')){$ret['error'] = 'usersstatus_maxidle??'; return $ret;}
	$ret['p_arr'] = $p_arr;
	if ((!isset($p_arr['user']))||($p_arr['user'] == '')){$ret['error'] = 'missing field user'; return $ret;}
	
	$ret['users_get'] = cs('users/get',array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['user']),
	))));
	if ($ret['users_get'] == null){$ret['error'] = 'users_get??'; return $ret;}
	
	if ((strtotime(date('Y-m-d H:i:s')) - strtotime($ret['users_get']['date'])) > (usersstatus_maxidle)){
		if ($ret['users_get']['status'] == 1){
			$ret['usersstatus_statuschange_action'] = cs('usersstatus/statuschange_action',array(
				'user'=>$p_arr['user'],
				'status'=>0,
			));
			if (!isset($ret['usersstatus_statuschange_action']['success'])||($ret['usersstatus_statuschange_action']['success'] != true)){$ret['error'] = 'usersstatus_statuschange_action??';return $ret;}
		}
	}else{
		$ret['resp'] = 1;
	}
	$ret['success'] = true;
	return $ret;
}
function usersstatus_statuschange_action($p_arr = array()){
	$ret = array('success'=>false,'status'=>0);
	if (!defined('usersstatus_maxidle')){$ret['error'] = 'usersstatus_maxidle??'; return $ret;}
	$ret['p_arr'] = $p_arr;
	if ((!isset($p_arr['user']))||($p_arr['user'] == '')){$ret['error'] = 'missing field user'; return $ret;}
	if ((!isset($p_arr['status']))||($p_arr['status'] === '')){$ret['error'] = 'missing field status'; return $ret;}
	if (intval($p_arr['status']) == 1) {
		$ret['oldstatus'] = 0;
	}else{
		$ret['oldstatus'] = 1;
	}
	
	$ret['users_update'] = cs('users/update', array(
		"oper"=>"edit",
		"id"=>$p_arr['user'],
		"status"=>$p_arr['status'],
	));
	if (!isset($ret['users_update']['success']) || ($ret['users_update']['success'] != true)) {$ret['error'] = 'users_update??';return $ret;}
	
	//ping
	if (isset($_SESSION['firebase_activeid']) && ($_SESSION['firebase_activeid'] != '') && (intval($p_arr['status']) == 0)) {
		$ret['action_dispatch_param'] = array(
			'path'=>'users',
			'userid'=>$p_arr['user'],
			'action'=>array(
				'name'=>'ping',
			),
		);
		$ret['firebase_action_dispatch'] = cs('firebase/action_dispatch',$ret['action_dispatch_param']);
	}

	//notify
	$ret['usersstatus_grid'] = cs('usersstatus/grid',array(
		"filters"=>array("rules"=>array(
			array("field"=>"pray","op"=>"eq","data"=>$p_arr['user']),
			array("field"=>"status","op"=>"eq","data"=>$ret['oldstatus']),
		)),
		'rows'=>50,
	));
	if (!isset($ret['usersstatus_grid']['success']) || ($ret['usersstatus_grid']['success'] != true)) {$ret['error'] = 'usersstatus_grid??'; return $ret;}
	foreach($ret['usersstatus_grid']['resp']['rows'] as $us){
		if (isset($_SESSION['firebase_activeid']) && ($_SESSION['firebase_activeid'] != '')) {
			$ret['action_dispatch_param'] = array(
				'path'=>'users',
				'userid'=>$us->hunter,
				'action'=>array(
					'name'=>'usersstatus_userchange',
					'param'=>array(
						'user'=>$p_arr['user'],
						'status'=>$p_arr['status'],
					),
				),
			);
			$ret['firebase_action_dispatch'] = cs('firebase/action_dispatch',$ret['action_dispatch_param']);
		}
	}
	if ($ret['usersstatus_grid']['resp']['records'] > 0){
		$ret['update_sql'] = 'UPDATE usersstatus SET ' 
				. 'status = ' . $p_arr['status'] 
				. ', date = \'' . date('Y-m-d H:i:s') 
			. '\' WHERE pray = ' . $p_arr['user'] . ' AND status = ' . $ret['oldstatus'];
		$ret['update'] = cs("_cs_grid/get",array('db_sql'=>$ret['update_sql']));
		if (!isset($ret['update']['success'])||($ret['update']['success'] != true)){$ret['error'] = 'update??';return $ret;}	
	}
	$ret['p_arr'] = $p_arr;
	$ret['success'] = true;
	return $ret;
}
function usersstatus_watchlist_add($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array(),'log'=>array());
	if (!defined('usersstatus_maxidle')){$ret['error'] = 'usersstatus_maxidle??'; return $ret;}
	if ((!isset($p_arr['list']))||($p_arr['list'] == '')){$ret['error'] = 'missing field list'; return $ret;}
	if (gettype($p_arr['list'])!='array') {$ret['error'] = 'field list not array'; return $ret;}
	
	//if (count($p_arr['list']) == 0) {$ret['error'] = 'field list empty array'; return $ret;} //do not uncoment, this is like ping, permant present, js constant call onload
	
	if (isset($_SESSION['cs_users_id'])){
		$ret['mestatus'] = 1;
		$ret['mestatusnot'] = 0;
	}else{
		$ret['mestatus'] = 0;
		$ret['mestatusnot'] = 1;
	}
	if (isset($p_arr['pathname'])){
		if (substr($p_arr['pathname'], 0, strlen('/mesaje')) === '/mesaje'){
			if (isset($_SESSION['cs_users_id'])){
				$ret['push_cansend_markread'] = cs('push/cansend_markread',array('user'=>$_SESSION['cs_users_id']));
				if (!isset($ret['push_cansend_markread']['success'])||($ret['push_cansend_markread']['success'] != true)){return $ret;}
			}
		}
	}
	if (isset($p_arr['logs'])){
		$ret['logs_add'] = cs('logs/add',$p_arr['logs']);
		if (!isset($ret['logs_add']['success'])||($ret['logs_add']['success'] != true)){return $ret;}
	}
	if (isset($p_arr['pushSubscription'])){
		$ret['pushupdate_sql'] = 'UPDATE push SET date = \'' .date('Y-m-d H:i:s') . '\' ' 
			. ' WHERE ' 
			. ' endpoint = \'' . $p_arr['pushSubscription']['endpoint'] . '\''
			. ' AND date < \'' . date('Y-m-d H:i:s',strtotime('-1 day',strtotime(date('Y-m-d H:i:s')))) . '\''
			. ' LIMIT 1'
			;
		$ret['pushupdate'] = cs("_cs_grid/get",array('db_sql'=>$ret['pushupdate_sql']));
		if (!isset($ret['pushupdate']['success'])||($ret['pushupdate']['success'] != true)){$ret['error'] = 'pushupdate??';return $ret;}	
	}
	if (isset($_SESSION['users_id'])){
		//my status changet for others??
		$ret['log'][] = 'check if my status changet for others';
		$ret['usersstatus_grid1'] = cs('usersstatus/grid',array(
			"filters"=>array("rules"=>array(
				array("field"=>"pray","op"=>"eq","data"=>$_SESSION['users_id']),
				array("field"=>"status","op"=>"eq","data"=>$ret['mestatusnot']),
			)),
			'rows'=>50,
		));
		if (!isset($ret['usersstatus_grid1']['success']) || ($ret['usersstatus_grid1']['success'] != true)) {$ret['error'] = 'usersstatus_grid1??'; return $ret;}
		if ($ret['usersstatus_grid1']['resp']['records'] > 0){
			$ret['log'][] = true;
			$ret['usersstatus_statuschange_action'] = cs('usersstatus/statuschange_action',array(
				'user'=>$_SESSION['users_id'],
				'status'=>$ret['mestatus'],
			));
			if (!isset($ret['usersstatus_statuschange_action']['success'])||($ret['usersstatus_statuschange_action']['success'] != true)){$ret['error'] = 'usersstatus_statuschange_action??';return $ret;}
		}else{
			$ret['log'][] = false;
		}
		$ret['log'][] = 'check if list contains new items';
		////sof
		$ret['usersstatus_grid'] = cs('usersstatus/grid',array(
			"filters"=>array("rules"=>array(
				array("field"=>'hunter',"op"=>"eq","data"=>$_SESSION['users_id']),
				array("field"=>"pray","op"=>"in","data"=>$p_arr['list']),
			)),
			'rows'=>50,
		));
		if (!isset($ret['usersstatus_grid']['success']) || ($ret['usersstatus_grid']['success'] != true)) {$ret['error'] = 'usersstatus_grid??'; return $ret;}
		$ret['list'] = array();
		if ($ret['usersstatus_grid']['resp']['records'] > 0){
			foreach($p_arr['list'] as $id){
				$isin = false;
				foreach($ret['usersstatus_grid']['resp']['rows'] as $aid){
					if (intval($aid->pray) == intval($id)){
						$isin = true;
						break;
					}
				}
				if ($isin == false){
					$ret['list'][] = intval($id);
				}
			}
		}else{
			$ret['list'] = $p_arr['list'];
		}
		$ret['tmp'] = array();
		if (count($ret['list']) > 0){
			$date = date('Y-m-d H:i:s');
			foreach($ret['list'] as $li){
				if (intval($li) > 0){
					$ret['usersstatus_check'] = cs('usersstatus/check',array('user'=>$li));
					if (!isset($ret['usersstatus_check']['success'])||($ret['usersstatus_check']['success'] != true)){$ret['error'] = 'usersstatus_check??';return $ret;}
					$ret['tmp'][] = '(' 
						. $_SESSION['users_id']
						. ', ' . $li 
						. ', ' . $ret['usersstatus_check']['resp'] 
						. ', ' . '\'' . $date . '\'' 
						. ')';
				}
			}
		}
		if (count($ret['tmp']) > 0){
			$ret['log'][] = true;
			$ret['insert_sql'] = 'INSERT INTO usersstatus (hunter, pray, status, date) VALUES ' . implode(', ',$ret['tmp']);
			$ret['insert'] = cs("_cs_grid/get",array('db_sql'=>$ret['insert_sql']));
			if (!isset($ret['insert']['success'])||($ret['insert']['success'] != true)){$ret['error'] = 'insert??';return $ret;}
		}else{
			$ret['log'][] = false;
		}
		//////eof
		$ret['log'][] = 'check if list valability expired';
		$ret['date'] = date('Y-m-d H:i:s');
		$ret['usersstatus_grid2'] = cs('usersstatus/grid',array(
			"filters"=>array("rules"=>array(
				array("field"=>"hunter","op"=>"eq","data"=>$_SESSION['users_id']),
			)),
			'sidx'=>'date',
			'sord'=>'asc',
		));
		if (!isset($ret['usersstatus_grid2']['success']) || ($ret['usersstatus_grid2']['success'] != true)) {$ret['error'] = 'usersstatus_grid2??'; return $ret;}
		if ($ret['usersstatus_grid2']['resp']['records'] > 0){
			if ((strtotime($ret['date']) - strtotime($ret['usersstatus_grid2']['resp']['rows'][0]->date)) >= (usersstatus_maxidle)){
				$ret['log'][] = true;
				foreach($ret['usersstatus_grid2']['resp']['rows'] as $aid){
					$ret['usersstatus_check2'] = cs('usersstatus/check',array('user'=>$aid->pray));
					if (!isset($ret['usersstatus_check2']['success'])||($ret['usersstatus_check2']['success'] != true)){$ret['error'] = 'usersstatus_check2??';return $ret;}
					if (intval($ret['usersstatus_check2']['resp']) != intval($aid->status)){
						$ret['usersstatus_statuschange_action2'] = cs('usersstatus/statuschange_action',array(
							'user'=>$aid->pray,
							'status'=>$ret['usersstatus_check2']['resp'],
						));
						if (!isset($ret['usersstatus_statuschange_action2']['success'])||($ret['usersstatus_statuschange_action2']['success'] != true)){$ret['error'] = 'usersstatus_statuschange_action2??';return $ret;}
					}
				}
				$ret['update_sql'] = 'UPDATE usersstatus SET date = \'' .$ret['date'] . '\' WHERE hunter = ' . $_SESSION['users_id'];
				$ret['update'] = cs("_cs_grid/get",array('db_sql'=>$ret['update_sql']));
				if (!isset($ret['update']['success'])||($ret['update']['success'] != true)){$ret['error'] = 'update??';return $ret;}	
			}else{
				$ret['log'][] = false;
			}
		}
		///return current list
		$ret['usersstatus_grid3'] = cs('usersstatus/grid',array(
			"filters"=>array("rules"=>array(
				array("field"=>"hunter","op"=>"eq","data"=>$_SESSION['users_id']),
			)),
			'sidx'=>'date',
			'sord'=>'asc',
			'rows'=>50,
		));
		if (!isset($ret['usersstatus_grid3']['success']) || ($ret['usersstatus_grid3']['success'] != true)) {$ret['error'] = 'usersstatus_grid3??'; return $ret;}
		$ret['resp'] = $ret['usersstatus_grid3']['resp'];
		if (isset($_SESSION['cs_users_id'])){
			$ret['resp']['timer'] = true;
		}
		$ret['resp']['date'] = $ret['date'];
		if (!isset($_SESSION['cs_users_id'])) unset($_SESSION['users_id']);
	}
	if (isset($_SESSION['cs_users_id'])){
		$ret['resp']['mesaje_countnew'] = cs('mesaje/countnew');
		if (!isset($ret['resp']['mesaje_countnew']['success']) || $ret['resp']['mesaje_countnew']['success'] == false) {return $ret;}
		$ret['resp']['mesaje_countnew'] = $ret['resp']['mesaje_countnew']['resp'];
		
		$ret['resp']['programari_countnew'] = cs('programari/countnew');
		if (!isset($ret['resp']['programari_countnew']['success']) || $ret['resp']['programari_countnew']['success'] == false) {return $ret;}
		$ret['resp']['programari_countnew'] = $ret['resp']['programari_countnew']['resp'];
		
	}
	$ret['p_arr'] = $p_arr;
	$ret['success'] = true;
	return $ret;
}
function usersstatus_pong($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array(),'log'=>array());
	if (!defined('usersstatus_maxidle')){$ret['error'] = 'usersstatus_maxidle??'; return $ret;}
	if (!isset($_SESSION['cs_users_id'])){$ret['error'] = 'not logged??'; return $ret;}
	if (isset($_SESSION['cs_users_id'])){
		$ret['mestatus'] = 1;
		$ret['mestatusnot'] = 0;
	}else{
		$ret['mestatus'] = 0;
		$ret['mestatusnot'] = 1;
	}
	if (isset($_SESSION['users_id'])){
		//my status changet for others??
		$ret['log'][] = 'check if my status changet for others';
		$ret['usersstatus_grid1'] = cs('usersstatus/grid',array(
			"filters"=>array("rules"=>array(
				array("field"=>"pray","op"=>"eq","data"=>$_SESSION['users_id']),
				array("field"=>"status","op"=>"eq","data"=>$ret['mestatusnot']),
			)),
			'rows'=>50,
		));
		if (!isset($ret['usersstatus_grid1']['success']) || ($ret['usersstatus_grid1']['success'] != true)) {$ret['error'] = 'usersstatus_grid1??'; return $ret;}
		if ($ret['usersstatus_grid1']['resp']['records'] > 0){
			$ret['log'][] = true;
			$ret['usersstatus_statuschange_action'] = cs('usersstatus/statuschange_action',array(
				'user'=>$_SESSION['users_id'],
				'status'=>$ret['mestatus'],
			));
			if (!isset($ret['usersstatus_statuschange_action']['success'])||($ret['usersstatus_statuschange_action']['success'] != true)){$ret['error'] = 'usersstatus_statuschange_action??';return $ret;}
		}else{
			$ret['log'][] = false;
		}
	}
	$ret['p_arr'] = $p_arr;
	$ret['success'] = true;
	return $ret;
}
?>