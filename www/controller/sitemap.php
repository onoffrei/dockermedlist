<?php
$GLOBALS['sitemaps']= array(
	'unitati_pe_localitati' => array(),
	'specializari' => array(),
	'userprofile' => array(),
	'doctori' => array(),
);
function sitemap_generate($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('xml'=>array()));
	foreach($GLOBALS['sitemaps'] as $sk=>$sv){
		$ret['sitemap_' . $sk] = cs('sitemap/' . $sk);
		if (isset($ret['sitemap_' . $sk]['success'])&&($ret['sitemap_' . $sk]['success'] == true)){
			$ret['resp']['xml'][$sk] = $ret['sitemap_' . $sk]['resp']['xml'];
		}
	}
	$ret['success'] = true;
	return $ret;
}
function sitemap_update($p_arr = array()){
	$ret = array('success'=>false);
	
	$ret['sitemap_index'] = cs("sitemap/index");
	if (!isset($ret['sitemap_index']['success'])||($ret['sitemap_index']['success'] != true)){return $ret;}
	$filename = 'sitemapindex.xml';
	$myfile = fopen(cs_path . DIRECTORY_SEPARATOR . 'sitemaps' . DIRECTORY_SEPARATOR . $filename, "w") or die("Unable to open file!");
	fwrite($myfile, $ret['sitemap_index']['resp']['xml']);
	fclose($myfile);
	
	$ret['sitemap_generate'] = cs("sitemap/generate");
	if (!isset($ret['sitemap_generate']['success'])||($ret['sitemap_generate']['success'] != true)){return $ret;}
	
	foreach($ret['sitemap_generate']['resp']['xml'] as $xk=>$xv){
		if ($xv != ''){
			$filename = 'sitemap-' . str_replace('_','-',$xk) . '.xml';
			$myfile = fopen(cs_path . DIRECTORY_SEPARATOR . 'sitemaps' . DIRECTORY_SEPARATOR . $filename, "w") or die("Unable to open file!");
			fwrite($myfile, $xv);
			fclose($myfile);
		}
	}
	$ret['success'] = true;
	return $ret;
}
function sitemap_index($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('xml'=>''));
	ob_start(); 
?><?xml version="1.0" encoding="utf-8"?>
<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<sitemap>
		<loc><?php echo cs_url . '/sitemaps/' . 'sitemap.xml';?></loc>
		<lastmod><?php echo date('Y-m-d')?></lastmod>
	</sitemap><?php
	foreach($GLOBALS['sitemaps'] as $sk=>$sv){
	?> 
	<sitemap>
		<loc><?php echo cs_url . '/sitemaps/' . 'sitemap-' . str_replace('_','-',$sk) . '.xml';?></loc>
		<lastmod><?php echo date('Y-m-d')?></lastmod>
	</sitemap><?php	
	}?> 
</sitemapindex><?php
	$ret['success'] = true;
	$ret['resp']['xml'] = ob_get_contents();ob_end_clean();
	return $ret;	
}
function sitemap_unitati_pe_localitati($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('xml'=>''));
	
	$ret['cauta_sql'] = " SELECT DISTINCT spitale.id as spitale_id";
	$ret['cauta_sql'] .= " 	, spitale.uri as spitale_uri";
	$ret['cauta_sql'] .= " FROM spitale ";
	$ret['cauta_sql'] .= " WHERE spitale.activman = 1 ";
	$ret['cauta_sql'] .= " AND spitale.aprobat = 1 ";
	$ret['cauta_sql'] .= " ORDER BY spitale.id";
	$ret['p_arr'] = $p_arr;
	$ret['cauta'] = cs("_cs_grid/get",array('db_sql'=>$ret['cauta_sql']));
	if (!isset($ret['cauta']['success'])||($ret['cauta']['success'] != true)){return $ret;}
	if ($ret['cauta']['resp']['records'] == 0) return $ret;
	ob_start(); 
?><?xml version="1.0" encoding="utf-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><?php
	foreach($ret['cauta']['resp']['rows'] as $unitate){
		$ret['localitati_spitale_sql'] = 'SELECT localitati.id';
		$ret['localitati_spitale_sql'] .= '	, localitati.denumire';
		$ret['localitati_spitale_sql'] .= '	, localitati.uri';
		$ret['localitati_spitale_sql'] .= ' FROM localitati_spitale';
		$ret['localitati_spitale_sql'] .= ' LEFT JOIN localitati ON localitati_spitale.localitate = localitati.id';
		$ret['localitati_spitale_sql'] .= ' WHERE localitati_spitale.spital = '. $unitate->spitale_id;
		$ret['localitati_spitale_sql'] .= ' ORDER BY localitati.id ASC';
		$ret['localitati_spitale'] = cs("_cs_grid/get",array('db_sql'=>$ret['localitati_spitale_sql']));
		if (!isset($ret['localitati_spitale']['success'])||($ret['localitati_spitale']['success'] != true)){$ret['success'] = false;$ret['resp']['xml'] = ob_get_contents();ob_end_clean();return $ret;}
		
		$localitate_uri = array();
		foreach($ret['localitati_spitale']['resp']['rows'] as $item_sd){
			$localitate_uri[] = $item_sd->uri;
		}
	$localitate_uri  = implode('/',$localitate_uri);
	?>
	<url>
		<loc><?php echo cs_url . '/doctori/' . $localitate_uri . '/' . $unitate->spitale_uri?></loc>
		<changefreq>weekly</changefreq>
		<lastmod><?php echo date('Y-m-d')?></lastmod>
		<priority>1</priority>
	</url>
<?php	
	}?></urlset><?php
	$ret['success'] = true;
	$ret['resp']['xml'] = ob_get_contents();ob_end_clean();
	return $ret;	
}
function sitemap_specializari_imbricate($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array());
	$ret['p_arr'] = $p_arr;
	if (!isset($p_arr['parent'])){$ret['error'] = 'param parent??';return $ret;}
	if (!isset($p_arr['path'])){$ret['error'] = 'param path??';return $ret;}
	$ret['specializari'] = cs("cauta/specializari_list_a",array('catid'=>$p_arr['parent']['id']));
	if (!isset($ret['specializari']['success'])||($ret['specializari']['success'] != true)){$ret['error'] = 'specializari??';return $ret;}
	if ($ret['specializari']['resp']['specializari_grid']['resp']['records'] == 0) {$ret['success'] = true;return $ret;}
	$ret['resp']['children'] = array();
	$ret['resp']['xml'] = '';
	foreach($ret['specializari']['resp']['specializari_grid']['resp']['rows'] as $specializare){
		$specializare = json_decode(json_encode($specializare),true);
		$nextpath = json_decode(json_encode($p_arr['path']),true);
		$nextpath[] = $specializare['uri'];
		$ret['sitemap_specializari_imbricate'] = cs('sitemap/specializari_imbricate',array('parent'=>$specializare,'path'=>$nextpath));
		if (!isset($ret['sitemap_specializari_imbricate']['success'])||($ret['sitemap_specializari_imbricate']['success'] != true)){return $ret;}
		$ret['resp']['xml'] .= "	<url>\r\n"
			. "		<loc>" . cs_url . '/doctori/' . implode('/',$nextpath) . '/' . "</loc>\r\n"
			. "		<changefreq>weekly</changefreq>\r\n"
			. "		<lastmod>" . date('Y-m-d') . "</lastmod>\r\n"
			. "		<priority>1</priority>\r\n"
			. "	</url>\r\n";
		if (isset($ret['sitemap_specializari_imbricate']['resp']['children'])){
			$specializare['children'] = $ret['sitemap_specializari_imbricate']['resp']['children'];		
			$ret['resp']['xml'] .= $ret['sitemap_specializari_imbricate']['resp']['xml'];
		}
		$ret['resp']['children'][] = $specializare;
	}
	$ret['success'] = true;
	return $ret;
}
function sitemap_specializari($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('xml'=>'','root'=>array()));
	$ret['specializari_root'] = cs("cauta/specializari_list_a");
	if (!isset($ret['specializari_root']['success'])||($ret['specializari_root']['success'] != true)){return $ret;}
	if ($ret['specializari_root']['resp']['specializari_grid']['resp']['records'] == 0) return $ret;
	foreach ($ret['specializari_root']['resp']['specializari_grid']['resp']['rows'] as $root){
		$root = json_decode(json_encode($root),true);
		$ret['sitemap_specializari_imbricate'] = cs('sitemap/specializari_imbricate',array('parent'=>$root,'path'=>array($root['uri'])));
		if (!isset($ret['sitemap_specializari_imbricate']['success'])||($ret['sitemap_specializari_imbricate']['success'] != true)){return $ret;}
		$ret['resp']['xml'] .= "	<url>\r\n"
			. "		<loc>" . cs_url . '/doctori/' . $root['uri'] . '/' . "</loc>\r\n"
			. "		<changefreq>weekly</changefreq>\r\n"
			. "		<lastmod>" . date('Y-m-d') . "</lastmod>\r\n"
			. "		<priority>1</priority>\r\n"
			. "	</url>\r\n";
		if (isset($ret['sitemap_specializari_imbricate']['resp']['children'])){
			$root['children'] = $ret['sitemap_specializari_imbricate']['resp']['children'];		
			$ret['resp']['xml'] .= $ret['sitemap_specializari_imbricate']['resp']['xml'];
		}
		$ret['resp']['root'][] = $root;
	}
	$ret['resp']['xml'] = '<?xml version="1.0" encoding="utf-8"?>' . "\r\n"
				. '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n"
				. $ret['resp']['xml']
				. '</urlset>';
	$ret['success'] = true;
	return $ret;	
}
function sitemap_userprofile($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('xml'=>''));
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	$ret['cauta_sql'] = " SELECT users.id";
	$ret['cauta_sql'] .= " 	, users.uri";
	$ret['cauta_sql'] .= " FROM users ";
	$ret['cauta_sql'] .= " ORDER BY users.id desc";
	$ret['p_arr'] = $p_arr;
	$ret['cauta'] = cs("_cs_grid/get",array('db_sql'=>$ret['cauta_sql']));
	if (!isset($ret['cauta']['success'])||($ret['cauta']['success'] != true)){return $ret;}
	if ($ret['cauta']['resp']['records'] == 0) return $ret;
	ob_start(); 
?><?xml version="1.0" encoding="utf-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><?php
	foreach($ret['cauta']['resp']['rows'] as $user){
	?> 
	<url>
		<loc><?php echo cs_url . '/profil-utilizator/' . $user->uri . '.html';?></loc>
		<changefreq>weekly</changefreq>
		<lastmod><?php echo date('Y-m-d')?></lastmod>
		<priority>1</priority>
	</url><?php	
	}?> 
</urlset>
<?php
	$ret['success'] = true;
	$ret['resp']['xml'] = ob_get_contents();ob_end_clean();
	return $ret;	
}
function sitemap_doctori($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('xml'=>''));
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	$ret['cauta_sql'] = " SELECT DISTINCT users.id";
	$ret['cauta_sql'] .= " 	, users.uri";
	$ret['cauta_sql'] .= " 	, specializari_user_spitale.spital as spital_id";
	$ret['cauta_sql'] .= " 	, spitale.uri as spital_uri";
	$ret['cauta_sql'] .= " FROM users ";
	$ret['cauta_sql'] .= " LEFT JOIN specializari_user_spitale ON specializari_user_spitale.user = users.id";
	$ret['cauta_sql'] .= " LEFT JOIN spitale ON specializari_user_spitale.spital = spitale.id";
	$ret['cauta_sql'] .= " WHERE specializari_user_spitale.spital IS NOT NULL";
	$ret['cauta_sql'] .= " AND spitale.aprobat = 1 ";
	$ret['cauta_sql'] .= " AND spitale.activman = 1 ";
	$ret['cauta_sql'] .= " ORDER BY users.id desc";
	$ret['p_arr'] = $p_arr;
	$ret['cauta'] = cs("_cs_grid/get",array('db_sql'=>$ret['cauta_sql']));
	if (!isset($ret['cauta']['success'])||($ret['cauta']['success'] != true)){return $ret;}
	if ($ret['cauta']['resp']['records'] == 0) return $ret;
	ob_start(); 
?><?xml version="1.0" encoding="utf-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><?php
	foreach($ret['cauta']['resp']['rows'] as $user){
		$ret['localitati_spitale_sql'] = 'SELECT localitati.id';
		$ret['localitati_spitale_sql'] .= '	, localitati.denumire';
		$ret['localitati_spitale_sql'] .= '	, localitati.uri';
		$ret['localitati_spitale_sql'] .= ' FROM localitati_spitale';
		$ret['localitati_spitale_sql'] .= ' LEFT JOIN localitati ON localitati_spitale.localitate = localitati.id';
		$ret['localitati_spitale_sql'] .= ' WHERE localitati_spitale.spital = '. $user->spital_id;
		$ret['localitati_spitale_sql'] .= ' ORDER BY localitati.id ASC';
		$ret['localitati_spitale'] = cs("_cs_grid/get",array('db_sql'=>$ret['localitati_spitale_sql']));
		if (!isset($ret['localitati_spitale']['success'])||($ret['localitati_spitale']['success'] != true)){$ret['success'] = false;$ret['resp']['xml'] = ob_get_contents();ob_end_clean();return $ret;}
		
		$localitate_uri = array();
		foreach($ret['localitati_spitale']['resp']['rows'] as $item_sd){
			$localitate_uri[] = $item_sd->uri;
		}
		$localitate_uri  = implode('/',$localitate_uri);

	?> 
	<url>
		<loc><?php echo cs_url . '/doctori/' . $localitate_uri . '/' . $user->spital_uri. '/' . $user->uri ;?></loc>
		<changefreq>weekly</changefreq>
		<lastmod><?php echo date('Y-m-d')?></lastmod>
		<priority>1</priority>
	</url><?php	
	}?> 
</urlset>
<?php
	$ret['success'] = true;
	$ret['resp']['xml'] = ob_get_contents();ob_end_clean();
	return $ret;	
}
?>