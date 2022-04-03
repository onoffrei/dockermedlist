<?php
function mobile_ismobile($p_arr = array()){
	if (!isset($p_arr['str'])) $p_arr['str'] = $_SERVER['HTTP_USER_AGENT'];
	$re = '/Mobile|iP(hone|od|ad)|Android|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/';
	preg_match($re, $p_arr['str'], $matches, PREG_OFFSET_CAPTURE, 0);
	if (count($matches)>0) {return true;}else{return false;}
}
?>