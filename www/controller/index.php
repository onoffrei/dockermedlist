<?php
$GLOBALS['localitati_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'denumire'		=>array('type'=>'text',),
	'uri'				=>array('type'=>'text',),
); 
function localitati_get($p_arr = array()){
	$ret = null;
	$list = localitati_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["denumire"] = $list['resp']['rows'][0]->denumire;
		$ret["uri"] = $list['resp']['rows'][0]->uri;
	}
	return $ret;
}

?>