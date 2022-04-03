<?php
require_once("_cs_config.php");
$cauta_uridecode = cs('cauta/uridecode',array('REQUEST_URI'=>$_SERVER['REQUEST_URI'],'rootstr'=>'doctori'));
cscheck($cauta_uridecode);
$GLOBALS['cauta_uridecode'] = $cauta_uridecode;

if (count($cauta_uridecode['rest']) == 0){
	require_once(cs_path . DIRECTORY_SEPARATOR . 'doctori_cauta.php' );
}else if (count($cauta_uridecode['rest']) == 1){
	require_once(cs_path . DIRECTORY_SEPARATOR . 'doctori_spital.php' );
}else if (count($cauta_uridecode['rest']) == 2){
	require_once(cs_path . DIRECTORY_SEPARATOR . 'doctori_doctor.php' );
}

?>