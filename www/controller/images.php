<?php
$GLOBALS['images_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'name'				=>array('type'=>'text',	),
	'path'				=>array('type'=>'text',	),
	'owner'				=>array('type'=>'int',	),
	'date'				=>array('type'=>'datetime',	),
	'w'					=>array('type'=>'int',	),
	'h'					=>array('type'=>'int',	),
);
$GLOBALS['images_thumb'] = array(
	array(200,200),
	array(500,500),
);
$GLOBALS['images_scale'] = array(1000,1000);
$GLOBALS['images_quality'] = 35;
$GLOBALS['images_path'] = 'images';
$GLOBALS['images_path_curent'] = '';
$GLOBALS['images_default'] = cs_path . DIRECTORY_SEPARATOR . $GLOBALS['images_path'] . DIRECTORY_SEPARATOR . 'photodefault.png';
$GLOBALS['images_getdefaul'] = array(
	'id'				=>null,
	'name'				=>'',
	'path'				=>$GLOBALS['images_default'],
	'owner'				=>null,
	'date'				=>'',
	'w'					=>259,
	'h'					=>194,
);
function images_foldercheck($p_arr = array()){
	global $images_path;
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $images_path)){ 
		mkdir(cs_path . DIRECTORY_SEPARATOR . $images_path); 
		chmod(cs_path . DIRECTORY_SEPARATOR . $images_path, 0755);
	}
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $images_path . DIRECTORY_SEPARATOR . '.htaccess')){
		$out			= fopen(cs_path . DIRECTORY_SEPARATOR . $images_path . DIRECTORY_SEPARATOR . '.htaccess', 'w+');
		fwrite($out,"deny from all\n<Files ~ '^.+\\.(bmp|gif|jpe?g|png)$'>\norder deny,allow\nallow from all\n</Files>");
		fclose($out);			
	}
	$datetime = new DateTime();
	$timestamp = $datetime->getTimestamp();
	$dt_year = date("Y", $timestamp);
	$dt_month = date("m", $timestamp);
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $images_path . DIRECTORY_SEPARATOR . $dt_year)){ 
		mkdir(cs_path . DIRECTORY_SEPARATOR . $images_path . DIRECTORY_SEPARATOR . $dt_year); 
		chmod(cs_path . DIRECTORY_SEPARATOR . $images_path . DIRECTORY_SEPARATOR . $dt_year, 0755);
	}
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $images_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month)){ 
		mkdir(cs_path . DIRECTORY_SEPARATOR . $images_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month); 
		chmod(cs_path . DIRECTORY_SEPARATOR . $images_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month, 0755);
	}
	$GLOBALS['images_path_curent'] = $images_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month;
}
function images_get($p_arr = array()){
	$ret = null;
	$list = images_grid($p_arr);
	if (isset($list['resp']['rows'])&&(count($list['resp']['rows'])>0)){
		$list = $list['resp'];
		$ret["id"] = intval($list['rows'][0]->id);
		$ret["name"] = $list['rows'][0]->name;
		$ret["path"] = $list['rows'][0]->path;
		$ret["owner"] = intval($list['rows'][0]->owner);
		$ret["date"] = $list['rows'][0]->date;
		$ret["w"] = intval($list['rows'][0]->w);
		$ret["h"] = intval($list['rows'][0]->h);
	}
	return $ret;
}
function images_grid($p_arr = array()){
	global $images_cols;
	$p_arr['db_cols'] = $images_cols;
	$p_arr['db_table'] = 'images';
	return cs("_cs_grid/get",$p_arr);
}
function images_update($p_arr = array()){
	global $images_cols;
	$p_arr['db_cols'] = $images_cols;
	$p_arr['db_table'] = 'images';
	return cs("_cs_grid/update",$p_arr);
}
function images_add($p_arr = array()){
	images_foldercheck();
	global $images_path_curent, $images_thumb, $images_quality, $images_scale;
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	$owner = 0;
	if (isset($p_arr['owner'])) $owner = $p_arr['owner'];
	if(count($_FILES) >0){
		foreach($_FILES as $finput=>$fdata){
			if ($fdata['error'] == 0)
			$p_arr['image'] = file_get_contents($fdata['tmp_name']);
			$ret['files'] = $_FILES;
			if ((!isset($p_arr['name']))) $p_arr['name'] = $fdata['name'];
			break;
		}
	}
	if ((!isset($p_arr['owner'])) && isset($_SESSION['cs_users_id'])) $owner = $_SESSION['cs_users_id'];
	if ((!isset($p_arr['image'])) 
		|| (gettype($p_arr['image']) != 'string')
		|| (strlen($p_arr['image']) <= 0)) {$ret['error'] = 'wrong resource'; return $ret;}
	if (isset($_SERVER['CONTENT_TYPE'])) {
		if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'text/plain') !== false) {
			$exploded = explode(',', $p_arr['image'], 2); // limit to 2 parts, i.e: find the first comma
			$encoded = $exploded[1]; // pick up the 2nd part
			$p_arr['image'] = base64_decode($encoded);
		}
	}
	$img = @imagecreatefromstring($p_arr['image']);
	if ($img === false) {
		$ret['error'] = 'imagecreatefromstring'; 
		return $ret; 
	}
	$imgprop = getimagesizefromstring($p_arr['image']);
	$ret['imgprop'] = $imgprop;
	if ((!isset($p_arr['name']))) {$ret['name'] = 'image' . images_extension($imgprop['mime']);}else{$ret['name'] = $p_arr['name'];}
	while(file_exists(cs_path . DIRECTORY_SEPARATOR . $images_path_curent . DIRECTORY_SEPARATOR . $ret['name'])){
		$ret['name'] = rand(0,9) . $ret['name'];
	}
	
	$images_ratio = $imgprop[0] / $imgprop[1];
	if($images_scale[0] >= $images_scale[1]) { 
		$images_scale[1] = round($images_scale[0] / $images_ratio);
	}else{
		$images_scale[0] = round($images_scale[1] * $images_ratio);
	}
	$temp_image = imagecreatetruecolor($images_scale[0], $images_scale[1]);
	imagecopyresampled($temp_image, $img, 0, 0, 0, 0, $images_scale[0], $images_scale[1], $imgprop[0], $imgprop[1]);
	
	$ret['images_update'] = images_update(array(
		'oper' => 'add',
		'id' => 'null',
		'date' => date('Y-m-d H:i:s'),
		'name' => $ret['name'],
		'owner' => $owner,
		'w' => $images_scale[0],
		'h' => $images_scale[1],
		'path' => $images_path_curent,
	));
	if (!isset($ret['images_update']['success']) || ($ret['images_update']['success'] != true)) return $ret;
	$ret['resp']['id'] = $ret['images_update']['resp']['id'];
	switch($imgprop['mime']){
		case "image/jpeg":
		case 'image/jpg':
			if (imagetypes() & IMG_JPG) {
				imagejpeg($temp_image, cs_path . DIRECTORY_SEPARATOR . $images_path_curent . DIRECTORY_SEPARATOR . $ret['name'], $images_quality);
			}
		break;
		case 'image/gif':
			if (imagetypes() & IMG_GIF) {
				imagegif($temp_image, cs_path . DIRECTORY_SEPARATOR . $images_path_curent . DIRECTORY_SEPARATOR . $ret['name'], $images_quality);
			}
		break;
		case "image/png":
			if (imagetypes() & IMG_PNG) {
				$new_images_quality = intval($images_quality/10);
				imagepng($temp_image, cs_path . DIRECTORY_SEPARATOR . $images_path_curent . DIRECTORY_SEPARATOR . $ret['name'], $new_images_quality);
			}
		break;
	}
	chmod(cs_path . DIRECTORY_SEPARATOR . $images_path_curent . DIRECTORY_SEPARATOR . $ret['name'], 0755);
	$thumb_index = 0;
	foreach($images_thumb as $thumb){
		$thumb_name = 't' . $thumb_index . '_' . $ret['name'];
		$images_ratio = $imgprop[0] / $imgprop[1];
		if($thumb[0] >= $thumb[1]) { 
			$thumb[1] = round($thumb[0] / $images_ratio);
		}else{
			$thumb[0] = round($thumb[1] * $images_ratio);
		}
		$temp_image = imagecreatetruecolor($thumb[0], $thumb[1]);
		imagecopyresampled($temp_image, $img, 0, 0, 0, 0, $thumb[0], $thumb[1], $imgprop[0], $imgprop[1]);
		switch($imgprop['mime']){
			case "image/jpeg":
			case 'image/jpg':
				if (imagetypes() & IMG_JPG) {
					imagejpeg($temp_image, cs_path . DIRECTORY_SEPARATOR . $images_path_curent . DIRECTORY_SEPARATOR . $thumb_name);
				}
			break;
			case 'image/gif':
				if (imagetypes() & IMG_GIF) {
					imagegif($temp_image, cs_path . DIRECTORY_SEPARATOR . $images_path_curent . DIRECTORY_SEPARATOR . $thumb_name);
				}
			break;
			case "image/png":
				if (imagetypes() & IMG_PNG) {
					imagepng($temp_image, cs_path . DIRECTORY_SEPARATOR . $images_path_curent . DIRECTORY_SEPARATOR . $thumb_name);
				}
			break;
		}
		chmod(cs_path . DIRECTORY_SEPARATOR . $images_path_curent . DIRECTORY_SEPARATOR . $thumb_name, 0755);
		$thumb_index++;
	}
	$ret['success'] = true;
	unset($ret['error']);
	return $ret;
}
function images_view($p_arr = array()){
	global $images_default;
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id'])){
		$ret['error'] = 'id'; 
		images_view_output(array('path'=>$images_default));
		return;
	}
	$ret['images_get'] = images_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])))));
	if ($ret['images_get'] == null){
		$ret['error'] = 'not found id'; 
		images_view_output(array('path'=>$images_default));
		return;
	}
	if (isset($p_arr['thumb'])){
		$ret['images_get']['name'] = 't' . $p_arr['thumb'] . '_' . $ret['images_get']['name'];
	}
	$path = str_replace('/',DIRECTORY_SEPARATOR, str_replace('\\',DIRECTORY_SEPARATOR, $ret['images_get']['path']));
	if (!file_exists(cs_path 
		. DIRECTORY_SEPARATOR 
		. $path
		. DIRECTORY_SEPARATOR 
		. $ret['images_get']['name'])
	){
		$ret['error'] = 'not found file'; 
		images_view_output(array('path'=>$images_default));
		return;
	}
	images_view_output(array('path'=>
		cs_path 
		. DIRECTORY_SEPARATOR 
		. $path 
		. DIRECTORY_SEPARATOR 
		. $ret['images_get']['name']
	));
}
function images_view_output($p_arr){
	global $images_default;
	if (!file_exists($p_arr['path'])){
		$p_arr['path'] = $images_default;
	}		
	$imgprop = getimagesizefromstring(file_get_contents($p_arr['path']));
	header("Content-Type: " . $imgprop['mime']);
	readfile($p_arr['path']);
}

function images_extension($imagetype){
   if(empty($imagetype)) return false;
   switch($imagetype){
	   case 'image/bmp': return '.bmp';
	   case 'image/cis-cod': return '.cod';
	   case 'image/gif': return '.gif';
	   case 'image/ief': return '.ief';
	   case 'image/jpeg': return '.jpg';
	   case 'image/pipeg': return '.jfif';
	   case 'image/tiff': return '.tif';
	   case 'image/x-cmu-raster': return '.ras';
	   case 'image/x-cmx': return '.cmx';
	   case 'image/x-icon': return '.ico';
	   case 'image/x-portable-anymap': return '.pnm';
	   case 'image/x-portable-bitmap': return '.pbm';
	   case 'image/x-portable-graymap': return '.pgm';
	   case 'image/x-portable-pixmap': return '.ppm';
	   case 'image/x-rgb': return '.rgb';
	   case 'image/x-xbitmap': return '.xbm';
	   case 'image/x-xpixmap': return '.xpm';
	   case 'image/x-xwindowdump': return '.xwd';
	   case 'image/png': return '.png';
	   case 'image/x-jps': return '.jps';
	   case 'image/x-freehand': return '.fh';
	   default: return false;
   }
}
function images_delete_js($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['imageid']) ||  (!(intval($p_arr['imageid']) > 0))){$ret['error'] = 'missing field imageid'; return $ret;}
	$ret['images_delete'] = images_delete(array("filters"=>array("groupOp"=>"AND","rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['imageid'])
	))));
	if (!isset($ret['images_delete']['success']) || ($ret['images_delete']['success'] != true)) return $ret;
	$ret['success'] = true;
	return $ret;
}
function images_delete($p_arr = array()){
	global $images_path_curent, $images_thumb;
	$ret = array('success'=>false,'error'=>'');
	if ((!isset($p_arr['filters']))){$ret['error'] = 'missing field filters'; return $ret;}
	if ((!isset($p_arr['filters']['rules']))){$ret['error'] = 'missing field filters rules'; return $ret;}
	if (isset($_SESSION['cs_users_id'])) {
		$owner = $_SESSION['cs_users_id'];
		$ret['users_get'] = cs("users/get",array("filters"=>array("groupOp"=>"AND","rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
		))));
		if ($ret['users_get'] == null) return $ret;
	}
	$ret['images_grid'] = images_grid($p_arr);
	if (!isset($ret['images_grid']['success']) || ($ret['images_grid']['success'] != true)) return $ret;
	foreach($ret['images_grid']['resp']['rows'] as $row){
		$path = str_replace('/',DIRECTORY_SEPARATOR, str_replace('\\',DIRECTORY_SEPARATOR, $row->path));
		$ret['path'] = cs_path . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $row->name;
		@unlink(cs_path . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $row->name);
		$thumbi = 0;
		foreach($images_thumb as $thumb){
			@unlink(cs_path . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . 't' . $thumbi . '_' . $row->name);
			$thumbi++;
		}
		images_update(array("oper"=>"del","id"=>$row->id));
	}
	$ret['success'] = true;
	unset($ret['error']);
	return $ret;
}
function images_base64_ob($p_arr){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id']) || (!(intval($p_arr['id']) > 0))){$ret['error'] = 'check parm id';return $ret;}
	$ret['images_get'] = images_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])))));
	if ($ret['images_get'] == null){return $ret;}
	if (isset($p_arr['thumb'])){$ret['images_get']['name'] = 't' . $p_arr['thumb'] . '_' . $ret['images_get']['name'];}
	$path = str_replace('/',DIRECTORY_SEPARATOR, str_replace('\\',DIRECTORY_SEPARATOR, $ret['images_get']['path']));
	$ret['path'] = cs_path . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $ret['images_get']['name'];
	if (!file_exists($ret['path'])){$ret['error'] = 'not found file'; return $ret;}
	$ret['type'] = pathinfo($ret['path'], PATHINFO_EXTENSION);
	$data = file_get_contents($ret['path']);
	$ret['resp']= 'data:image/' . $ret['type'] . ';base64,' . base64_encode($data);
	$ret['success'] = true;
	return $ret;
}
?>