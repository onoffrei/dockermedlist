<?php
$GLOBALS['menu_items'] = array(
	array('level'=>user_level_admin,	'href'=> cs_url_scheme . '://' . cs_url_host . '/siteadmin',		'onclick'=>'javascript:void(0)',	'text'=>'Site admin',			'icon'=>'<i class="fa fa-server" aria-hidden="true"></i>'),
	array('level'=>user_level_admin,	'href'=> cs_url_scheme . '://' . cs_url_host . '/localitati',		'onclick'=>'javascript:void(0)',	'text'=>'Localitati',			'icon'=>'<i class="fa fa-map-marker" aria-hidden="true"></i>'),
	array('level'=>user_level_admin,	'href'=> cs_url_scheme . '://' . cs_url_host . '/spitale',			'onclick'=>'javascript:void(0)',	'text'=>'Spitale',				'icon'=>'<i class="fa fa-building" aria-hidden="true"></i>'),	
	array('level'=>user_level_admin,	'href'=> cs_url_scheme . '://' . cs_url_host . '/specializari',		'onclick'=>'javascript:void(0)',	'text'=>'Specializari',			'icon'=>'<i class="fa fa-tags" aria-hidden="true"></i>'),
	array('level'=>user_level_manager,	'href'=> cs_url_scheme . '://' . cs_url_host . '/personal',			'onclick'=>'javascript:void(0)',	'text'=>'Personal Unitate',				'icon'=>'<i class="fa fa-users" aria-hidden="true"></i>'),
	array('level'=>user_level_manager,	'href'=> cs_url_scheme . '://' . cs_url_host . '/detaliiservicii',	'onclick'=>'javascript:void(0)',	'text'=>'Servicii Unitate',		'icon'=>'<i class="fa fa-sliders" aria-hidden="true"></i>'),
	array('level'=>user_level_manager,	'href'=> cs_url_scheme . '://' . cs_url_host . '/legenda',			'onclick'=>'javascript:void(0)',	'text'=>'Legenda Unitate',				'icon'=>'<i class="fa fa-grav" aria-hidden="true"></i>'),
	array('level'=>user_level_doctor,	'href'=> cs_url_scheme . '://' . cs_url_host . '/planificare',		'onclick'=>'javascript:void(0)',	'text'=>'Planificare Unitate',			'icon'=>'<i class="fa fa-calendar" aria-hidden="true"></i>'),
//	array('level'=>user_level_doctor,	'href'=> cs_url_scheme . '://' . cs_url_host . '/planifauto',		'onclick'=>'javascript:void(0)',	'text'=>'Auto Planificare',		'icon'=>'<i class="fa fa-refresh" aria-hidden="true"></i>'),
//	array('level'=>user_level_doctor,	'href'=> cs_url_scheme . '://' . cs_url_host . '/embedwidget',		'onclick'=>'javascript:void(0)',	'text'=>'Embed Widget Code',	'icon'=>'<i class="fa fa-code" aria-hidden="true"></i>'),
	array('level'=>user_level_doctor,	'href'=> cs_url_scheme . '://' . cs_url_host . '/utile',			'onclick'=>'javascript:void(0)',	'text'=>'Utile Unitate',				'icon'=>'<i class="fa fa-wrench" aria-hidden="true"></i>'),
	array('level'=>user_level_manager,	'href'=> cs_url_scheme . '://' . cs_url_host . '/detaliispital',	'onclick'=>'javascript:void(0)',	'text'=>'Detalii Unitate',		'icon'=>'<i class="fa fa-pencil-square-o" aria-hidden="true"></i>'),
	array('level'=>user_level_doctor,	'href'=> cs_url_scheme . '://' . cs_url_host . '/programdoc',		'onclick'=>'javascript:void(0)',	'text'=>'Pacienti programati',	'icon'=>'<i class="fa fa-newspaper-o" aria-hidden="true"></i>'),
	array('level'=>user_level_manager,	'href'=> cs_url_scheme . '://' . cs_url_host . '/costuri',			'onclick'=>'javascript:void(0)',	'text'=>'Costuri Unitate',				'icon'=>'<i class="fa fa-usd" aria-hidden="true"></i>'),
	array('level'=>user_level_pacient,	'href'=> cs_url_scheme . '://' . cs_url_host . '/cont',				'onclick'=>'javascript:void(0)',	'text'=>'Contul meu',					'icon'=>'<i class="fa fa-user-o" aria-hidden="true"></i>'),
	array('level'=>user_level_pacient,	'href'=> cs_url_scheme . '://' . cs_url_host . '/programarilemele',	'onclick'=>'javascript:void(0)',	'text'=>'Programarile mele',	'icon'=>'<i class="fa fa-clock-o" aria-hidden="true"></i>'),
	array('level'=>user_level_pacient,	'href'=> cs_url_scheme . '://' . cs_url_host . '/mesaje',			'onclick'=>'javascript:void(0)',	'text'=>'Mesajele mele',				'icon'=>'<i class="fa fa-envelope" aria-hidden="true"></i>'),	
);
function menu_get($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	$ret['resp']['menu_items'] = array();
	$users_active_level = 0;
	if (isset($p_arr['level'])){
		$users_active_level = intval($p_arr['level']);
	}else{
		$ret['spitale_users_getlevel'] = cs('spitale_users/getlevel');
		if (!isset($ret['spitale_users_getlevel']['success']) ||($ret['spitale_users_getlevel']['success'] != true)){return $ret;}
		$users_active_level = intval($ret['spitale_users_getlevel']['resp']);
	}
	foreach($GLOBALS['menu_items'] as $mi => $menu){
		if($menu['level'] <= $users_active_level){
			if (isset($menu['levelmax']) &&  $menu['levelmax'] <= $users_active_level) continue;
			$ret['resp']['menu_items'][] = $menu;
			if (substr(cs_url_scheme . '://' . cs_url_host . $_SERVER['REQUEST_URI'], 0, strlen($menu['href'])) 
				=== $menu['href']) $ret['resp']['menu_active'] = $menu;
		}
	}
	$ret['success'] = true;
	return $ret;
}
function menu_side_ob($p_arr){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	$ret['lnkquery'] = '';
	if(isset($p_arr['lnkquery']) && ($p_arr['lnkquery'] != '')) $ret['lnkquery'] = $p_arr['lnkquery'];
	$ret['menu_get'] = menu_get();
	if (!isset($ret['menu_get']['success']) ||($ret['menu_get']['success'] != true)){return $ret;}
	ob_start();
	?>
	<div class="list-group">
		<?php foreach ($ret['menu_get']['resp']['menu_items'] as $mi){
			$active = '';
			if (substr(cs_url_scheme . '://' . cs_url_host . $_SERVER['REQUEST_URI'], 0, strlen($mi['href'])) === $mi['href']) $active = 'active';
		?>
			<a href="<?php echo $mi['href'] . $ret['lnkquery'];?>" onclick="<?php echo $mi['onclick']; ?>" class="list-group-item <?php echo $active;?>"> 
				<?php echo $mi['icon']; ?> <?php echo $mi['text']; ?>
			</a>
		<?php } ?>
	</div>
	<?php
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
?>