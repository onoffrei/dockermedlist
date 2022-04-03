<?php
$GLOBALS['programari_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'start'				=>array('type'=>'datetime',),
	'stop'				=>array('type'=>'datetime',),
	'doctor'			=>array('type'=>'int',),
	'specializare'		=>array('type'=>'int',),
	'spital'			=>array('type'=>'int',),
	'user'				=>array('type'=>'int',),
	'observatii'		=>array('type'=>'text',),
	'isread'			=>array('type'=>'int',),
);
$GLOBALS['programari_minuteserviciu'] = 15;
function programari_get($p_arr = array()){
	$ret = null;
	$list = programari_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["start"] = $list['resp']['rows'][0]->start;
		$ret["stop"] = $list['resp']['rows'][0]->stop;
		$ret["doctor"] = intval($list['resp']['rows'][0]->doctor);
		$ret["specializare"] = intval($list['resp']['rows'][0]->specializare);
		$ret["spital"] = intval($list['resp']['rows'][0]->spital);
		$ret["user"] = intval($list['resp']['rows'][0]->user);
		$ret["observatii"] = $list['resp']['rows'][0]->observatii;
		$ret["isread"] = intval($list['resp']['rows'][0]->isread);
	}
	return $ret;
}
function programari_grid($p_arr = array()){
	global $programari_cols;
	$p_arr['db_cols'] = $programari_cols;
	$p_arr['db_table'] = 'programari';
	return cs("_cs_grid/get",$p_arr);
}
function programari_update($p_arr = array()){
	global $programari_cols;
	$p_arr['db_cols'] = $programari_cols;
	$p_arr['db_table'] = 'programari';
	return cs("_cs_grid/update",$p_arr);
}
function programari_countnew($p_arr){
	$ret = array('success'=>false,'resp'=>0);
	if(!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'login first'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$ret['countnew_sql'] = 'SELECT count(*) AS count';
	$ret['countnew_sql'] .= ' FROM programari ';
	$ret['countnew_sql'] .= ' WHERE programari.doctor = '. $_SESSION['cs_users_id'];
	$ret['countnew_sql'] .= ' AND programari.isread = 0 ';
	$ret['countnew_sql'] .= ' LIMIT 0,1 ';
	$ret['data'] = cs("_cs_grid/get",array('db_sql'=>$ret['countnew_sql']));
	if (!isset($ret['data']['success']) || $ret['data']['success'] == false) {return $ret;}
	if ($ret['data']['resp']['records'] != 0){
		$ret['resp'] = intval($ret['data']['resp']['rows'][0]->count);
	}	
	$ret['success'] = true;
	return $ret;
}
function programari_monthget($p_arr){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}	
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}	
	if (!isset($p_arr['m'])){ $ret['error'] = 'check param - m'; return $ret;}
	$p_arr['m'] = intval($p_arr['m']);
	if (!isset($p_arr['y'])){ $ret['error'] = 'check param - y'; return $ret;}
	$p_arr['y'] = intval($p_arr['y']);
	$lastday = intval(date("t",strtotime($p_arr['y'].'-'.$p_arr['m'].'-1')));
	//6 (programari pe ora) X 8 (ore) X 31 (zile) = 1488
	$ret['programari_grid'] = cs('programari/grid',array("sidx"=>"start","sord"=>"asc","rows"=>1488,"filters"=>array("rules"=>array(
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"start","op"=>"ge","data"=>date("Y-m-d 00:00:00",strtotime($p_arr['y'].'-'.$p_arr['m'].'-1'))),
		array("field"=>"stop","op"=>"le","data"=>date("Y-m-t 23:59:59",strtotime($p_arr['y'].'-'.$p_arr['m'].'-1'))),
	))));
	if (!isset($ret['programari_grid']['success']) || ($ret['programari_grid']['success'] != true)) {return $ret;}
	$pi = 0;
	for ($d = 1; $d <= $lastday; $d++){
		$day = array();
		if (($ret['programari_grid']['resp']['records'] > 0) && ($pi < $ret['programari_grid']['resp']['records'])){
			while(($pi < $ret['programari_grid']['resp']['records'])
				&&(strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$d . ' 00:00:00')<=strtotime($ret['programari_grid']['resp']['rows'][$pi]->start))
				&& (strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$d . ' 23:59:59')>=strtotime($ret['programari_grid']['resp']['rows'][$pi]->start))
			){
				$day[] = $ret['programari_grid']['resp']['rows'][$pi];
				$pi++;
			}
		}
		$ret['resp'][] = $day;
	}
	$ret['success'] = true;
	return $ret;
}
function programari_daycheck($p_arr){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}	
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}	
	if (!isset($p_arr['d'])){ $ret['error'] = 'check param - d'; return $ret;}
	$p_arr['d'] = intval($p_arr['d']);
	if (!isset($p_arr['m'])){ $ret['error'] = 'check param - m'; return $ret;}
	$p_arr['m'] = intval($p_arr['m']);
	if (!isset($p_arr['y'])){ $ret['error'] = 'check param - y'; return $ret;}
	$p_arr['y'] = intval($p_arr['y']);
	$lastday = intval(date("t",strtotime($p_arr['y'].'-'.$p_arr['m'].'-1')));
	
	global $programari_minuteserviciu;
	$ret['timp'] = 30;
	$ret['detaliiservicii_timp'] = cs('detaliiservicii/get', array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"numedetaliu","op"=>"eq","data"=>'timp'),
	))));
	if ($ret['detaliiservicii_timp'] != null) {$ret['timp'] = intval($ret['detaliiservicii_timp']['valoaredetaliu']);} 
	
	//6 (programari pe ora) X 8 (ore) = 48
	$ret['programari_grid'] = cs('programari/grid',array("sidx"=>"start","sord"=>"asc","rows"=>48,"filters"=>array("rules"=>array(
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"start","op"=>"ge","data"=>date("Y-m-d 00:00:00",strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$p_arr['d']))),
		array("field"=>"stop","op"=>"le","data"=>date("Y-m-d 23:59:59",strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$p_arr['d']))),
	))));
	if (!isset($ret['programari_grid']['success']) || ($ret['programari_grid']['success'] != true)) {return $ret;}
	
	$ret['planificare_grid'] = cs('planificare/grid',array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"start","op"=>"ge","data"=>date("Y-m-d 00:00:00",strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$p_arr['d']))),
		array("field"=>"stop","op"=>"le","data"=>date("Y-m-d 23:59:59",strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$p_arr['d']))),
	))));
	if (!isset($ret['planificare_grid']['success']) || ($ret['planificare_grid']['success'] != true)) {return $ret;}
	
	if ($ret['planificare_grid']['resp']['records'] != 0){
		$ret['start'] = $ret['planificare_grid']['resp']['rows'][0]->start;
		$ret['stop'] = $ret['planificare_grid']['resp']['rows'][0]->stop;
		while(
			(strtotime($ret['start']) < strtotime($ret['stop']))
			&& ((strtotime($ret['start']) + ($ret['timp']*60)) <= strtotime($ret['stop']))
			){
			$ret['interval'] = array();
			$ret['interval']['start'] = $ret['start'];
			$ret['interval']['stop'] = date("Y-m-d H:i:s",strtotime($ret['start']) + ($ret['timp']*60));
			$ret['interval']['status'] = 'disponibil';
			foreach ($ret['programari_grid']['resp']['rows'] as $programare){
				if ($programare->start == $ret['interval']['start']){
					$ret['interval']['status'] = 'epuizat';
					break;
				}
			}
			$ret['start'] = $ret['interval']['stop'];
			$ret['resp'][] = $ret['interval'];
		}
	}
	
	$ret['success'] = true;
	return $ret;
}
function programari_monthcheck($p_arr){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}	
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}	
	if (!isset($p_arr['m'])){ $ret['error'] = 'check param - m'; return $ret;}
	$p_arr['m'] = intval($p_arr['m']);
	if (!isset($p_arr['y'])){ $ret['error'] = 'check param - y'; return $ret;}
	$p_arr['y'] = intval($p_arr['y']);
	global $programari_minuteserviciu;
	$ret['timp'] = 30;
	$ret['detaliiservicii_timp'] = cs('detaliiservicii/get', array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"numedetaliu","op"=>"eq","data"=>'timp'),
	))));
	if ($ret['detaliiservicii_timp'] != null) {$ret['timp'] = intval($ret['detaliiservicii_timp']['valoaredetaliu']);} 	

	$ret['programari_monthget'] = cs('programari/monthget',array("spital"=>$p_arr['spital'],"doctor"=>$p_arr['doctor'],"m"=>$p_arr['m'],"y"=>$p_arr['y']));
	if (!isset($ret['programari_monthget']['success']) || ($ret['programari_monthget']['success'] != true)) {return $ret;}
	$ret['planificare_monthget'] = cs('planificare/monthget',array("spital"=>$p_arr['spital'],"doctor"=>$p_arr['doctor'],"m"=>$p_arr['m'],"y"=>$p_arr['y']));
	if (!isset($ret['planificare_monthget']['success']) || ($ret['planificare_monthget']['success'] != true)) {return $ret;}
	
	for ($i = 0; $i < count($ret['programari_monthget']['resp']); $i++){
		$day = array();
		if ($ret['planificare_monthget']['resp'][$i]['start'] != ''){
			$day['maxitems'] = intval((strtotime($ret['planificare_monthget']['resp'][$i]['stop']) - strtotime($ret['planificare_monthget']['resp'][$i]['start'])) / ($ret['timp']*60));
			$day['items'] = $ret['programari_monthget']['resp'][$i];
			if ($day['maxitems'] <= count($ret['programari_monthget']['resp'][$i])){
				$day['status'] = 'epuizat';
			}else{
				$day['status'] = 'disponibil';
			}
		}
		$ret['resp'][] = $day;
	}
	
	$ret['success'] = true;
	return $ret;
}
function programari_setappoint($p_arr){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	if (!isset($_SESSION['cs_users_id'])){ $ret['error'] = 'login first'; return $ret;}	
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}	
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}	
	if (!isset($p_arr['specializare'])){ $ret['error'] = 'check param - specializare'; return $ret;}	
	if (!isset($p_arr['start'])){ $ret['error'] = 'check param - start'; return $ret;}	
	$p_arr['start'] = date("Y-m-d H:i:00",strtotime($p_arr['start']));
	$ret['p_arr'] = $p_arr;
	
	$ret['timp'] = 30;
	$ret['detaliiservicii_timp'] = cs('detaliiservicii/get', array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"numedetaliu","op"=>"eq","data"=>'timp'),
	))));
	if ($ret['detaliiservicii_timp'] != null) {$ret['timp'] = intval($ret['detaliiservicii_timp']['valoaredetaliu']);} 
	$ret['stop'] = date("Y-m-d H:i:s",strtotime($p_arr['start']) + ($ret['timp']*60));

	$ret['programari_get_param'] = array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"start","op"=>"eq","data"=>$p_arr['start']),
	)));
	$ret['programari_get'] = cs('programari/get',$ret['programari_get_param']);
	if ($ret['programari_get'] != null) { $ret['error'] = 'allready in use'; return $ret;}
	
	$ret['programari_update_param'] = array(
		'oper'=>'add',
		'id'=>null,
		'spital'=>$p_arr['spital'],
		'doctor'=>$p_arr['doctor'],
		'specializare'=>$p_arr['specializare'],
		'observatii'=>$p_arr['observatii'],
		'start'=>$p_arr['start'],
		'stop'=>$ret['stop'],
		'user'=>$_SESSION['cs_users_id'],
	);
	$ret['programari_update'] = programari_update($ret['programari_update_param']);
	if (!isset($ret['programari_update']['success'])||($ret['programari_update']['success'] != true)){return $ret;}

	$ret['push_send_user'] = cs('push/send_user',array(
		'user'=>$p_arr['doctor'],
		'message'=>array(
			'title'=>'Medlist Programare noua',
			'message'=>'Un nou client tocmai si-a facut o programare'
		),
	));
	$ret['programari_mailinfo'] = cs('programari/mailinfo',array('doctor'=>$p_arr['doctor'],'spital'=>$p_arr['spital']));
	
	$ret['success'] = true;
	return $ret;
}
function programari_mailinfo($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'', 'log'=>array());
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}	
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}	
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');

	$ret['pacient_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id']),
	))));
	if ($ret['pacient_get'] == null){return $ret;}

	set_error_handler(function ($severity, $message, $file, $line) {
		throw new \ErrorException($message, $severity, $severity, $file, $line);
	});
	$headers = 'MIME-Version: 1.0' . "\r\n"
		. 'Content-type: text/html; charset=iso-8859-1' . "\r\n"
		. 'From: ' . cs_email . "\r\n"
		. 'Reply-To: ' . cs_email . "\r\n"
		. 'X-Mailer: PHP/' . phpversion();
	$message = 		"Salut " . $ret['pacient_get']['email'] . " \n<br>"
		. "Ai primit acest email deoarece ai cerut o noua programare <a href='" . cs_url . "'>" . cs_url . "</a> . \n<br>"
		. "Mesaj generat automat, te rugam nu da reply.\n<br>"
		. "\n<br>"
		. "Cu stima,\n<br>"
		. "Echipa, <a href='" . cs_url . "'>" . cs_url . "</a>\n<br>";		 
	try {
		mail($ret['pacient_get']['email'] , 'O noua programare pentru tine', $message,$headers);
	}catch (\Throwable | \Warrning | \Error | \Exception $e) {
		$ret['log'][] = array(
			'email'=>$ret['pacient_get']['email'] ,
			'error'=>$e->getMessage(),
		); 
	}
	restore_error_handler();
	
	$ret['doctor_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['doctor']),
	))));
	if ($ret['doctor_get'] == null){return $ret;}
	
	set_error_handler(function ($severity, $message, $file, $line) {
		throw new \ErrorException($message, $severity, $severity, $file, $line);
	});
	$headers = 'MIME-Version: 1.0' . "\r\n"
		. 'Content-type: text/html; charset=iso-8859-1' . "\r\n"
		. 'From: ' . cs_email . "\r\n"
		. 'Reply-To: ' . cs_email . "\r\n"
		. 'X-Mailer: PHP/' . phpversion();
	$message = 		"Salut " . $ret['doctor_get']['email'] . " \n<br>"
		. "Ai primit acest email deoarece ai cerut o noua programare <a href='" . cs_url . "'>" . cs_url . "</a> . \n<br>"
		. "Mesaj generat automat, te rugam nu da reply.\n<br>"
		. "\n<br>"
		. "Cu stima,\n<br>"
		. "Echipa, <a href='" . cs_url . "'>" . cs_url . "</a>\n<br>";		 
	try {
		mail($ret['doctor_get']['email'] , 'O noua programare pentru tine', $message,$headers);
	}catch (\Throwable | \Warrning | \Error | \Exception $e) {
		$ret['log'][] = array(
			'email'=>$ret['doctor_get']['email'] ,
			'error'=>$e->getMessage(),
		); 
	}
	restore_error_handler();
	
	$ret['manager_sql'] = "SELECT ";
	$ret['manager_sql'] .= "    users.id as id";
	$ret['manager_sql'] .= "    ,users.email as email";
	$ret['manager_sql'] .= "    ,users.nume as nume";
	$ret['manager_sql'] .= "    ,users.uri as uri";
	$ret['manager_sql'] .= "    ,users.image as image";
	$ret['manager_sql'] .= " FROM spitale_users";
	$ret['manager_sql'] .=  " LEFT JOIN users ON spitale_users.user = users.id";
	$ret['manager_sql'] .=  " WHERE spitale_users.level >= " . $GLOBALS['cs_db_conn']->real_escape_string(user_level_manager);
	$ret['manager_sql'] .=  " AND spitale_users.spital = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
	$ret['manager_sql'] .=  " AND spitale_users.user <> " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['doctor']);
	$ret['manager_sql'] .=  " AND users.email IS NOT NULL ";

	$ret['manager'] = cs("_cs_grid/get",array('db_sql'=>$ret['manager_sql']));
	if (!isset($ret['manager']['success']) || ($ret['manager']['success'] != true)) {$ret['error'] = 'manager??';return $ret;}
	if ($ret['manager']['resp']['records'] > 0) {
		set_error_handler(function ($severity, $message, $file, $line) {
			throw new \ErrorException($message, $severity, $severity, $file, $line);
		});
		foreach($ret['manager']['resp']['rows'] as $manager){
			$headers = 'MIME-Version: 1.0' . "\r\n"
				. 'Content-type: text/html; charset=iso-8859-1' . "\r\n"
				. 'From: ' . cs_email . "\r\n"
				. 'Reply-To: ' . cs_email . "\r\n"
				. 'X-Mailer: PHP/' . phpversion();
			$message = 		"Salut " . $manager->email . " \n<br>"
				. "Ai primit acest email deoarece a fost adaugata o programare noua <a href='" . cs_url . "'>" . cs_url . "</a> . \n<br>"
				. "Mesaj generat automat, te rugam nu da reply.\n<br>"
				. "\n<br>"
				. "Cu stima,\n<br>"
				. "Echipa, <a href='" . cs_url . "'>" . cs_url . "</a>\n<br>";		 
			try {
				mail($manager->email , 'Programare noua', $message,$headers);
			}catch (\Throwable | \Warrning | \Error | \Exception $e) {
				$ret['log'][] = array(
					'email'=>$manager->email,
					'error'=>$e->getMessage(),
				); 
			}
		}
		restore_error_handler();
	}
	
	$ret['success'] = true;
	return $ret;
}
function programari_monthshow($p_arr){
	$ret = array('success'=>false,'resp'=>array('html'=>''));
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	if (!isset($p_arr['an']) || $p_arr['an'] == ''){ $ret['error'] = 'check param - an'; return $ret;}
	if (!isset($p_arr['luna']) || $p_arr['luna'] == ''){ $ret['error'] = 'check param - luna'; return $ret;}
	if (!isset($p_arr['spital']) || $p_arr['spital'] == ''){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($p_arr['page'])){$p_arr['page'] = 1;}
	if (!isset($p_arr['rows'])){$p_arr['rows'] = 20;}
	$ret['bydoctor'] = '';
	if (isset($p_arr['did'])){
		$ret['bydoctor'] = ' AND programari.doctor = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['did']);
	}
	$ret['p_arr'] = $p_arr;
	
	
	$lastday = date("t",strtotime($p_arr['an'].'-'.$p_arr['luna'].'-01'));
	$ret['datastart'] = $p_arr['an'].'-'.$p_arr['luna'].'-01 00:00:00';
	$ret['dataend'] = $p_arr['an'].'-'.$p_arr['luna'].'-' . $lastday . ' 23:59:59';
	
	
	$ret['cauta_sql'] = "SELECT SQL_CALC_FOUND_ROWS ";
	$ret['cauta_sql'] .= "    programari.id";
	$ret['cauta_sql'] .= "    ,programari.start";
	$ret['cauta_sql'] .= "    ,programari.stop";
	$ret['cauta_sql'] .= "    ,programari.doctor";
	$ret['cauta_sql'] .= "    ,programari.specializare";
	$ret['cauta_sql'] .= "    ,programari.spital";
	$ret['cauta_sql'] .= "    ,programari.user";
	$ret['cauta_sql'] .= "    ,programari.observatii";
	$ret['cauta_sql'] .= "    ,programari.isread";
	$ret['cauta_sql'] .= "    , userspacient.id AS pacient_id";
	$ret['cauta_sql'] .= "    , userspacient.email AS pacient_email";
	$ret['cauta_sql'] .= "    , userspacient.nume AS pacient_nume";
	$ret['cauta_sql'] .= "    , userspacient.telefon AS pacient_telefon";
	$ret['cauta_sql'] .= "    , usersdoctor.id AS doctor_id";
	$ret['cauta_sql'] .= "    , usersdoctor.nume AS doctor_nume";
	$ret['cauta_sql'] .= " FROM programari";
	$ret['cauta_sql'] .=  " LEFT JOIN users userspacient ON userspacient.id = programari.user";
	$ret['cauta_sql'] .=  " LEFT JOIN users usersdoctor ON usersdoctor.id = programari.doctor";
	$ret['cauta_sql'] .=  " WHERE programari.spital = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
	$ret['cauta_sql'] .=  " AND programari.start >= '" . $GLOBALS['cs_db_conn']->real_escape_string($ret['datastart']) . "'";
	$ret['cauta_sql'] .=  " AND programari.stop <= '" . $GLOBALS['cs_db_conn']->real_escape_string($ret['dataend']) . "'";
	$ret['cauta_sql'] .=  $ret['bydoctor'];
	$ret['cauta_sql'] .=  " __order__ __limit__";
	$ret['p_arr'] = $p_arr;
	$ret['cauta'] = cs("_cs_grid/get",array(
		'db_sql'=>$ret['cauta_sql'],
		'page'=>$p_arr['page'],
		'rows'=>$p_arr['rows'],
		'sord'=>'asc',
		'sidx'=>'start',
	));
	if (!isset($ret['cauta']['success'])||($ret['cauta']['success'] != true)){return $ret;}
	$ret['resp']['data'] = $ret['cauta'];
	if ($ret['cauta']['resp']['records'] == 0){$ret['resp']['html'] = 'Nu sunt pacienti programati.'; $ret['success'] = true; return $ret;}
	ob_start();?>
	
	<?php //////////////////////////////////////
	$ret['spitale_users_gett'] = cs('spitale_users/getlevel', array("filters"=>array("rules"=>array(
		array("field"=>"user","op"=>"eq","data"=>$_SESSION['cs_users_id']),
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
	))));
//	if ($ret['spitale_users_gett'] != null) { $ret['error'] = 'errr'; return $ret;}
//	echo $_SESSION['cs_users_id'];
	
	if($ret['spitale_users_gett']['resp']==2){
		
	

	////////////////////////////////////////////
?>
				<table class="table table-hover">
					<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col">data/ora</th>
							<th scope="col">nume pacient</th>	
							<th scope="col">observatii</th>
						</tr>
					</thead>
					<tbody>					
						<?php
						$nr=1;
						foreach ($ret['cauta']['resp']['rows'] as $programare){
							
							$ret['specializari_breadcrumb'] = cs('specializari/breadcrumb',array(
								'catid'=>$programare->specializare,
							));
							if (!isset($ret['specializari_breadcrumb']['success'])||($ret['specializari_breadcrumb']['success'] != true)){$ret['resp']['html'] = ob_get_contents();ob_end_clean();return $ret;}
							$ret['specializare_denumire'] = array();
							foreach($ret['specializari_breadcrumb']['resp']['nodearr'] as $item_sd){
								array_unshift($ret['specializare_denumire'],$item_sd['denumire']);
							}
							
						if(strtotime($programare->start)>=time()) {
							$css="style='background-color:white;'";
						}else{
							$css="style='background-color:red; color: white;'";
						}
						?>
						<tr <?php echo $css;?> >
							<td><?php echo $nr;?></td>
							<td><?php echo date('d.m.Y H:i',strtotime($programare->start));?></td>
							<td><?php echo $programare->pacient_nume . ' (' . $programare->pacient_email . ', ' . $programare->pacient_telefon . ')'?></td>	
							<td><?php echo htmlspecialchars($programare->observatii)?></td>
						</tr>
					<?php  $nr+=1;}?>
					</tbody>
				</table>
	<?php			
	}else{?>
		<table class="table table-hover">
					<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col">data/ora</th>
							<th scope="col">nume pacient</th>							
							<th scope="col">serviciu</th>							
							<th scope="col">doctor</th>
							<th scope="col">observatii</th>
							<th scope="col" style="text-align:center;">sterge</th>
						</tr>
					</thead>
					<tbody>					
						<?php
						$nr=1;
						foreach ($ret['cauta']['resp']['rows'] as $programare){
							
							$ret['specializari_breadcrumb'] = cs('specializari/breadcrumb',array(
								'catid'=>$programare->specializare,
							));
							if (!isset($ret['specializari_breadcrumb']['success'])||($ret['specializari_breadcrumb']['success'] != true)){$ret['resp']['html'] = ob_get_contents();ob_end_clean();return $ret;}
							$ret['specializare_denumire'] = array();
							foreach($ret['specializari_breadcrumb']['resp']['nodearr'] as $item_sd){
								array_unshift($ret['specializare_denumire'],$item_sd['denumire']);
							}						

						if(strtotime($programare->start)>=time()) {
							$css="style='background-color:white;'";
						}else{
							$css="style='background-color:red; color: white;'";
						}
						?>
						<tr <?php echo $css;?> >
							<td><?php  echo $nr; ?></td>
							<td><?php echo date('d.m.Y H:i',strtotime($programare->start))?></td>
							<td><?php echo $programare->pacient_nume . ' (' . $programare->pacient_email . ', ' . $programare->pacient_telefon . ')'?></td>							
							<td><?php echo implode('->',$ret['specializare_denumire']);?></td>							
							<td><?php echo $programare->doctor_nume?></td>
							<td><?php echo htmlspecialchars($programare->observatii)?></td>
							<td style="text-align:center;">
								<button class="btn btn-danger" onclick="if (confirm('Sigur doriti sa stergeti?')) cs('programari/sterge',{id:<?php echo $programare->id;?>}).then(function(d){window.location.reload()})"><i class="fa fa-trash" aria-hidden="true"></i></button></td>
						</tr>
						<?php $nr+=1;}?>
					</tbody>
				</table>
				<?php
	}
	
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function programari_sterge($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$p_arr['oper'] = 'del';
	$ret['programari_update'] = programari_update($p_arr);
	if (!isset($ret['programari_update']['success'])||($ret['programari_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
?>