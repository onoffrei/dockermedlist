<?php
$GLOBALS['specializari_user_spitale_cols'] = array(
	'id'				=>array('type'=>'int',),
	'spital'			=>array('type'=>'int',),
	'user'				=>array('type'=>'int',),
	'specializare'				=>array('type'=>'int',),
); 
function specializari_user_spitale_get($p_arr = array()){
	$ret = null;
	$list = specializari_user_spitale_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["user"] = intval($list['resp']['rows'][0]->user);
		$ret["spital"] = intval($list['resp']['rows'][0]->spital);
		$ret["specializare"] = intval($list['resp']['rows'][0]->specializare);
	}
	return $ret;
}
function specializari_user_spitale_grid($p_arr = array()){
	global $specializari_user_spitale_cols;
	$p_arr['db_cols'] = $specializari_user_spitale_cols;
	$p_arr['db_table'] = 'specializari_user_spitale';
	return cs("_cs_grid/get",$p_arr);
}
function specializari_user_spitale_update($p_arr = array()){
	global $specializari_user_spitale_cols;
	$p_arr['db_cols'] = $specializari_user_spitale_cols;
	$p_arr['db_table'] = 'specializari_user_spitale';
	return cs("_cs_grid/update",$p_arr);
}
function specializari_user_spitale_userset($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['user']) || $p_arr['user'] == ''){ $ret['error'] = 'check param - user'; return $ret;}
	if (!isset($p_arr['spital']) || $p_arr['spital'] == ''){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($p_arr['specializari']) || $p_arr['specializari'] == ''){ $ret['error'] = 'check param - specializari'; return $ret;}
	
	//-----------parent check
	foreach($p_arr['specializari'] as $specializare){
		$ret['specializari_breadcrumb'] = cs('specializari/breadcrumb', array('catid'=>$specializare));
		if (!isset($ret['specializari_breadcrumb']['success']) ||  ($ret['specializari_breadcrumb']['success'] != true)) return $ret;
		if (count($ret['specializari_breadcrumb']['resp']['nodearr']) > 0) for ($ni = 1; $ni < count($ret['specializari_breadcrumb']['resp']['nodearr']); $ni++){
			if (!in_array(intval($ret['specializari_breadcrumb']['resp']['nodearr'][$ni]['id']),$p_arr['specializari'])) $p_arr['specializari'][] = intval($ret['specializari_breadcrumb']['resp']['nodearr'][$ni]['id']);
		}
		
	}
	$ret['p_arr'] = $p_arr;
	//------------delsdid
	$ret['delsdid'] = array();
	$ret['specializari_user_spitale_grid'] = cs('specializari_user_spitale/grid', array(
		"filters"=>array("rules"=>array(
			array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
			array("field"=>"user","op"=>"eq","data"=>$p_arr['user']),
		))
	));
	if (!isset($ret['specializari_user_spitale_grid']['success']) || ($ret['specializari_user_spitale_grid']['success'] != true)) return $ret;
	foreach($ret['specializari_user_spitale_grid']['resp']['rows'] as $specializare){
		if (!in_array(intval($specializare->specializare), $p_arr['specializari'])) {
			$ret['delsdid'][] = intval($specializare->specializare);
		}
	}
	//-----addsdid
	$ret['addsdid'] = array();
	foreach($p_arr['specializari'] as $specializarenew){
		$found = false;
		foreach($ret['specializari_user_spitale_grid']['resp']['rows'] as $specializareold){
			if (intval($specializarenew) == intval($specializareold->specializare)){
				$found = true;
				break;
			}
		}
		if (!$found) $ret['addsdid'][] = intval($specializarenew);
	}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	if (count($ret['delsdid']) > 0){
		$ret['delsql'] = 'DELETE FROM specializari_user_spitale WHERE '
			. ' user=' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['user'])
			. ' AND spital=' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital'])
			. ' AND specializare IN (' . implode(',',$ret['delsdid']) . ')'
		;
		$ret['delret'] = $GLOBALS['cs_db_conn']->query($ret['delsql']);
		if (!$ret['delret']){return $ret;}
	}
	if (count($ret['addsdid']) > 0){
		foreach($ret['addsdid'] as $item){
			$ret['specializari_user_spitale_update'] = specializari_user_spitale_update(array(
				'oper'=>'add',
				'id'=>'null',
				'spital'=>$p_arr['spital'],
				'user'=>$p_arr['user'],
				'specializare'=>$item,
			));
			if (!isset($ret['specializari_user_spitale_update']['success'])||($ret['specializari_user_spitale_update']['success'] != true)){return $ret;}
		}
	}
	$ret['success'] = true;
	return $ret;
}
function specializari_user_spitale_tags_a($p_arr){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'error'=>'');
	if (!isset($p_arr['user']) || $p_arr['user'] == ''){ $ret['error'] = 'check param - user'; return $ret;}
	if (!isset($p_arr['spital']) || $p_arr['spital'] == ''){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($p_arr['callback']) || ($p_arr['callback'] == '')){$p_arr['callback'] = "javascript:void";}
	$ret['resp']['tagarr'] = array();
	
	$ret['specializari_user_spitale_grid'] = cs('specializari_user_spitale/grid', array(
		"filters"=>array("rules"=>array(
			array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
			array("field"=>"user","op"=>"eq","data"=>$p_arr['user']),
		))
	));
	if (!isset($ret['specializari_user_spitale_grid']['success']) || ($ret['specializari_user_spitale_grid']['success'] != true)) return $ret;
	
	foreach ($ret['specializari_user_spitale_grid']['resp']['rows'] as $sd){
		$item = array();
		$ret['specializari_breadcrumb'] = cs('specializari/breadcrumb', array('catid'=>$sd->specializare));
		if (!isset($ret['specializari_breadcrumb']['success']) ||  ($ret['specializari_breadcrumb']['success'] != true)) return $ret;
		$ret['nodearr'] = $ret['specializari_breadcrumb']['resp']['nodearr'];
		if (count($ret['nodearr']) == 0) continue;
		$ret['lastid'] = $ret['nodearr'][0]['id'];
		/*
		*/
		$ret['specializari_get'] = cs('specializari/get', array(
			"filters"=>array("rules"=>array(
				array("field"=>"parent","op"=>"eq","data"=>$ret['lastid']),
			)),
		));
		if ($ret['specializari_get'] != null) continue;
		//$item['specializari_get'] = $ret['specializari_get'];
		$ret['name'] = array();
		foreach(array_reverse($ret['nodearr']) as $node){
			$ret['name'][] = $node['denumire'];
		}
		$ret['name'] = implode('/',$ret['name']);
		$item['name'] = $ret['name'];
		$item['sid'] = $ret['lastid'];
		$item['sdid'] = intval($sd->id);
		$ret['resp']['tagarr'][] = $item;
	}
	ob_start(); 
	?>
	<div class="breadcrumb" id="specializari_user_spitale_tags_a">
	<style>
		.specializari_tag{
			display:inline-block;
			background-color: coral;
			padding: 7px;
			border-radius: 14px;
		}
	</style>
	<?php foreach($ret['resp']['tagarr'] as $tag){?>
		<div class="specializari_tag" <?php echo 'data-sid="' . $tag['sid'] . '" data-sdid="' . $tag['sdid'] . '"'?>><?php echo $tag['name']?> <i class="fa fa-times-circle-o" aria-hidden="true" onclick="specializari_user_spitale_tags_delete(<?php echo $tag['sid']?>)"></i></div>
	<?php } ?>
	<script>
		specializari_user_spitale_tags_delete = function(sid){
			console.log(sid)
			<?php echo $p_arr['callback']; ?>(sid);
		}
	</script>
	</div>
	<?php
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function specializari_user_spitale_tags_deletebysd($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'error'=>'');
	if (!isset($p_arr['sdid']) || $p_arr['sdid'] == ''){ $ret['error'] = 'check param - sdid'; return $ret;}
	$ret['specializari_user_spitale_update'] = specializari_user_spitale_update(array("oper"=>"del","id"=>$p_arr['sdid']));
	if (!isset($ret['specializari_user_spitale_update']['success']) || ($ret['specializari_user_spitale_update']['success'] != true)) return $ret;
	$ret['success'] = true;
	return $ret;
}
?>