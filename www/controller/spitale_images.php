<?php
$GLOBALS['spitale_images_cols'] = array(
	'id'				=>array('type'=>'int',),
	'spital'			=>array('type'=>'int',),
	'image'				=>array('type'=>'int',),
); 
function spitale_images_get($p_arr = array()){
	$ret = null;
	$list = spitale_images_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["spital"] = intval($list['resp']['rows'][0]->spital);
		$ret["image"] = intval($list['resp']['rows'][0]->image);
	}
	return $ret;
}
function spitale_images_grid($p_arr = array()){
	global $spitale_images_cols;
	$p_arr['db_cols'] = $spitale_images_cols;
	$p_arr['db_table'] = 'spitale_images';
	return cs("_cs_grid/get",$p_arr);
}
function spitale_images_update($p_arr = array()){
	global $spitale_images_cols;
	$p_arr['db_cols'] = $spitale_images_cols;
	$p_arr['db_table'] = 'spitale_images';
	return cs("_cs_grid/update",$p_arr);
}
function spitale_images_add($p_arr = array()){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	if ((!isset($p_arr['spital']))||(!(intval($p_arr['spital']) > 0))){$ret['error'] = 'missing field - spital'; return $ret;}

	$input = file_get_contents("php://input");
	if ((gettype($input) != 'string') || (strlen($input) == 0)) {$ret['error'] = 'image'; return $ret;}
	$ret['images_add'] = cs('images/add',array('image'=>$input));
	if (!isset($ret['images_add']['success']) || ($ret['images_add']['success'] != true)) return $ret;
	$ret['spitale_images_update'] = cs('spitale_images/update',array(
		'oper'=>'add'
		, 'spital'=>$p_arr['spital']
		, 'image'=>$ret['images_add']['resp']['id']
	));
	if (!isset($ret['spitale_images_update']['success']) || ($ret['spitale_images_update']['success'] != true)) return $ret;
	$ret['resp']['id'] = $ret['images_add']['resp']['id'];
	$ret['success'] = true;
	return $ret;
}
function spitale_images_delete_js($p_arr){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['image']) ||  (!(intval($p_arr['image']) > 0))){$ret['error'] = 'missing field image'; return $ret;}
	$ret['spitale_images_get'] = spitale_images_get(array("filters"=>array("rules"=>array(
		array("field"=>"image","op"=>"eq","data"=>$p_arr['image']),
	)))); 
	if ($ret['spitale_images_get'] == null) {$ret['error'] = 'invalid image'; return $ret;}
	$ret['images_delete'] = cs('images/delete', (array("filters"=>array("groupOp"=>"AND","rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['image'])
	)))));
	if (!isset($ret['images_delete']['success']) || ($ret['images_delete']['success'] != true)) return $ret;
	$ret['spitale_images_update'] = spitale_images_update(array("oper"=>"del","id"=>$ret['spitale_images_get']['id']));
	if (!isset($ret['spitale_images_update']['success']) || ($ret['spitale_images_update']['success'] != true)) return $ret;
	$ret['success'] = true;
	return $ret;
}
