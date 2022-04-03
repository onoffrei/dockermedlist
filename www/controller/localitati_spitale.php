<?php
$GLOBALS['localitati_spitale_cols'] = array(
	'id'				=>array('type'=>'int',),
	'spital'			=>array('type'=>'int',),
	'localitate'		=>array('type'=>'int',),
); 
function localitati_spitale_get($p_arr = array()){
	$ret = null;
	$list = localitati_spitale_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["spital"] = intval($list['resp']['rows'][0]->spital);
		$ret["localitate"] = intval($list['resp']['rows'][0]->localitate);
	}
	return $ret;
}
function localitati_spitale_grid($p_arr = array()){
	global $localitati_spitale_cols;
	$p_arr['db_cols'] = $localitati_spitale_cols;
	$p_arr['db_table'] = 'localitati_spitale';
	return cs("_cs_grid/get",$p_arr);
}
function localitati_spitale_update($p_arr = array()){
	global $localitati_spitale_cols;
	$p_arr['db_cols'] = $localitati_spitale_cols;
	$p_arr['db_table'] = 'localitati_spitale';
	return cs("_cs_grid/update",$p_arr);
}