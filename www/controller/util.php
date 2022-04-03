<?php 
function util_removeAccents($p_arr = array()){
	$a = array('Ț','ț','À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
	$b = array('T','t','A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
	return str_replace($a, $b, $p_arr['text']);
}
function util_uritospital($p_arr){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	if (!isset($p_arr['uri'])){ $ret['error'] = 'check param - uri'; return $ret;}
	$ret['parse_url'] = explode('.',parse_url($p_arr['uri'])['path']);
	if (($ret['parse_url'][0] == 'www') && (count($ret['parse_url']) != 4)) { $ret['error'] = 'wrong1'; return $ret;}
	if (($ret['parse_url'][0] != 'www') && (count($ret['parse_url']) != 3)) { $ret['error'] = 'wrong2'; return $ret;}
	if ($ret['parse_url'][0] == 'www') {
		$ret['parse_url'] = $ret['parse_url'][1];
	}else{
		$ret['parse_url'] = $ret['parse_url'][0];
	}
	$ret['resp'] = cs('spitale/get',array("filters"=>array("rules"=>array(
		array("field"=>"nume","op"=>"eq","data"=>$ret['parse_url']),
	))));
	if ($ret['resp'] == null) { $ret['error'] = 'no such name'; return $ret;}
	$ret['success'] = true;
	return $ret;
}
function util_columntoletter($p_arr){
	$ret = array('success'=>false, 'resp'=>'' ,'error'=>'');
	if (!isset($p_arr['column'])){ $ret['error'] = 'check param - column'; return $ret;}
	$p_arr['column'] = intval($p_arr['column']);
	if ($p_arr['column'] <= 0) return $ret;
	$ret['resp'] = '';
	while($p_arr['column'] != 0){
	   $p = ($p_arr['column'] - 1) % 26;
	   $p_arr['column'] = intval(($p_arr['column'] - $p) / 26);
	   $ret['resp'] = chr(65 + $p) . $ret['resp'];
	}
	$ret['success'] = true;
	return $ret;
}
function util_dateshort($p_arr){
	$ret = array('success'=>false, 'resp'=>'');
	if (!isset($p_arr['date'])){ $p_arr['date'] = date('Y-m-d H:i:s');}
	$ret['txt'] = date('N',strtotime($p_arr['date']));
	$numeluni = array('ian', 'feb', 'mart', 'apr', 'mai', 'iun', 'iul', 'aug', 'sep', 'oct', 'nov', 'dec');
	$numezile = array('LU', 'MA', 'MI', 'JO', 'VI', 'SA', 'DU');
	if (date('Y') == date('Y',strtotime($p_arr['date']))){
		if (date('m') == date('m',strtotime($p_arr['date']))){
			if (date('d') == date('d',strtotime($p_arr['date']))){
				$ret['resp'] = date('H:i',strtotime($p_arr['date']));
			}else{
				if (strtotime(date('Y-m-d',strtotime($p_arr['date']))) >= strtotime(date('Y-m-d',strtotime('last monday')))){
					$ret['resp'] = $numezile[intval(date('N',strtotime($p_arr['date'])) - 1)];
				}else{
					$ret['resp'] = date('d',strtotime($p_arr['date'])) . ' ' . $numeluni[intval(date('m'))];
				}
			}
		}else{
			$ret['resp'] = date('d',strtotime($p_arr['date'])) . ' ' . $numeluni[intval(date('m',strtotime($p_arr['date'])))];
		}
	}else{
		$ret['resp'] = date('d',strtotime($p_arr['date'])) . '.' . date('m',strtotime($p_arr['date'])) . '.' . date('y',strtotime($p_arr['date']));
	}
	$ret['success'] = true;
	return $ret;
}
?>