<?php
$GLOBALS['browser_cols'] = array(
	'id'				=>array('type'=>'int',),
	'browser'			=>array('type'=>'text',),
	'date'				=>array('type'=>'datetime',),
	'firebase'			=>array('type'=>'text',),
); 
function browser_get($p_arr = array()){
	$ret = null;
	$list = browser_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["browser"] = $list['resp']['rows'][0]->browser;
		$ret["date"] = $list['resp']['rows'][0]->date;
		$ret["firebase"] = $list['resp']['rows'][0]->firebase;
	}
	return $ret;
}
function browser_grid($p_arr = array()){
	global $browser_cols;
	$p_arr['db_cols'] = $browser_cols;
	$p_arr['db_table'] = 'browser';
	return cs("_cs_grid/get",$p_arr);
}
function browser_update($p_arr = array()){
	global $browser_cols;
	$p_arr['db_cols'] = $browser_cols;
	$p_arr['db_table'] = 'browser';
	return cs("_cs_grid/update",$p_arr);
}
?>