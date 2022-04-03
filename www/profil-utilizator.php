<?php
require_once("_cs_config.php");
$is_success = true;
require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');

if ($is_success){
	//$is_success = false;
	$cauta_initparams = cs('cauta/initparams');
	if (!isset($cauta_initparams['success']) || ($cauta_initparams['success'] == false)) {$is_success = false;}
	extract($cauta_initparams['resp']);
}
if ($is_success){
	//$is_success = false;
	$cauta_form = cs('cauta/form',array('cauta_initparams'=>$cauta_initparams));
	if (!isset($cauta_form['success']) || ($cauta_form['success'] == false)) {$is_success = false;}
}

if ($is_success){
	$is_success = false;
	$parsed = parse_url($_SERVER['REQUEST_URI']);
	$parsed = explode('/',$parsed['path']);
	$parsed1 = array();
	foreach($parsed as $item){
		if (($item != '')) $parsed1[] = $item;
	}
	if (count($parsed1) > 0) {$is_success = true;}
}

if ($is_success){
	$is_success = false;
	preg_match_all('/^([^\/]+)\.html$/m', $parsed1[count($parsed1) - 1], $matches, PREG_SET_ORDER, 0);
	if (
		(count($matches) > 0) 
		&& (count($matches[0]) > 1) 
	){$is_success = true;}

	$utilizatoruri = $matches[0][1];
}
if ($is_success){
	$utilizator_get = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"uri","op"=>"eq","data"=>$utilizatoruri)
	))));
	if ($utilizator_get == null) {$is_success = false;}
}
if ($is_success){
	$is_admin = false;
	$ret['admin_check'] = cs('spitale_users/get',array("filters"=>array("rules"=>array(
		array("field"=>"user","op"=>"eq","data"=>$utilizator_get['id']),
		array("field"=>"level","op"=>"eq","data"=>user_level_admin),
	))));
	if ($ret['admin_check'] != null) $is_admin = true;

	$is_manager = false;
	$ret['manager_check'] = cs('spitale_users/get',array("filters"=>array("rules"=>array(
		array("field"=>"user","op"=>"eq","data"=>$utilizator_get['id']),
		array("field"=>"level","op"=>"eq","data"=>user_level_manager),
	))));
	if ($ret['manager_check'] != null) $is_manager = true;

	$is_doctor = false;
	$ret['doctor_check'] = cs('spitale_users/get',array("filters"=>array("rules"=>array(
		array("field"=>"user","op"=>"eq","data"=>$utilizator_get['id']),
		array("field"=>"level","op"=>"eq","data"=>user_level_doctor),
	))));
	if ($ret['doctor_check'] != null) $is_doctor = true;
	
	if (($is_doctor == false) && ($is_manager == true)){
		$ret['doctor_check1'] = cs('specializari_user_spitale/get',array("filters"=>array("rules"=>array(
			array("field"=>"user","op"=>"eq","data"=>$utilizator_get['id']),
		))));
		if ($ret['doctor_check1'] != null) $is_doctor = true;
	}
	
	$unitati_sql = 'SELECT spitale.id';
	$unitati_sql .= '	, spitale.nume';
	$unitati_sql .= '	, spitale.uri';
	$unitati_sql .= '	, spitale.logo';
	$unitati_sql .= '	, spitale.telefon';
	$unitati_sql .= '	, spitale.adresa';
	$unitati_sql .= '	, (SELECT specializari.denumire FROM specializari_user_spitale ';
	$unitati_sql .= '	 	LEFT JOIN specializari ON specializari.id = specializari_user_spitale.specializare';
	$unitati_sql .= '	 	WHERE (user = spitale_users.user) AND (spital = spitale_users.spital) ORDER BY specializare ASC LIMIT 0,1) AS specializare';
	$unitati_sql .= ' FROM spitale_users';
	$unitati_sql .= ' LEFT JOIN spitale ON spitale_users.spital = spitale.id';
	$unitati_sql .= ' WHERE spitale_users.user = '. $GLOBALS['cs_db_conn']->real_escape_string($utilizator_get['id']);
	$unitati_sql .= ' AND spitale.id IS NOT NULL ';

	$unitati = cs("_cs_grid/get",array('db_sql'=>$unitati_sql));
	if (!isset($unitati['success']) || ($unitati['success'] == false)) {$is_success = false;}
	
}

?>
<?php 
function header_ob(){ 
	global $is_success, $utilizator_get;
	$ret = array('success'=>false, 'resp'=>array());
	ob_start();
	if ($is_success){
	?>
	<title>Profil utilizator <?php echo $utilizator_get['nume']?> - MedList.ro</title>
	<meta name="robots" content="index, follow" />	
	<meta name="description" content="Profil utilizator" />
	<meta name="keywords" content="Profil utilizator" />
	<meta name="robots" content="index, follow" />	
	<?php }?>
	<style>
		#cauta{
			padding-top: 10px;
			padding-bottom:10px;
			background-color: #60b0f4;
		}
		.utilizator_avatar{
			height:90px;
			width:100px;
			border-radius: 50%;
		}
		.spital_logo {
			width: 130px;
			height: 52px;
		}
	</style>
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
} 
$GLOBALS['header_ob'] = header_ob();
require_once(cs_path . DIRECTORY_SEPARATOR . 'header.php' );
?>
<?php if ($is_success){?>
<?php echo $cauta_form['resp']['html']?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<h1 style="font-size:18px">
				Profil utilizator: <?php echo $utilizator_get['nume']?>
			</h1>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-5 text-center">
			<div class="usersstatus <?php 
					$partstatus = 'offline';
					if ((strtotime(date('Y-m-d H:i:s')) - strtotime($utilizator_get['date'])) > usersstatus_maxidle){
						$partstatus = 'offline';
					}else{
						$partstatus = 'online';
					}
					if (intval($utilizator_get['status']) == 0){$partstatus = 'offline';}
					echo $partstatus;
					?>" users="<?php echo $utilizator_get['id']?>">
				<div class="usersstatus_circle_online"></div>
				<div class="usersstatus_circle_offline"></div>
				<img src="<?php 
					if (intval($utilizator_get['image']) > 0){
						echo cs_url."/csapi/images/view/?thumb=0&id=" . $utilizator_get['image'];
					}else{
						echo cs_url."/images/default_avatar.jpg";
					}
				?>" class="utilizator_avatar" style="" alt="User Image">
			</div>
		</div>
		<div class="col-sm-7">
			<div><b>Rol (uri):</b> 
			<?php
				$roluri = array();
				if ($is_admin == true) $roluri[] = 'administrator';
				if ($is_manager == true) $roluri[] = 'manager';
				if ($is_doctor == true) $roluri[] = 'doctor';
				
				if (count($roluri) == 0) $roluri[] = 'utilizator';
				echo implode(', ',$roluri);
			?>
			</div>
			<div class="usersstatus <?php 										
				$partstatus = 'offline';
				if ((strtotime(date('Y-m-d H:i:s')) - strtotime($utilizator_get['date'])) > usersstatus_maxidle){
					$partstatus = 'offline';
				}else{
					$partstatus = 'online';
				}
				if (intval($utilizator_get['status']) == 0){$partstatus = 'offline';}
				echo $partstatus;
				?>" users="<?php echo $utilizator_get['id']
				?>" style="display:block">
				<div class="usersstatus_custom_online" >
					<a class="btn btn-primary " onclick='<?php 
						$payload = array();
						$mek = ($minichat['mek']=='browser'?$minichat['mek']:'to');
						$payload[$mek] = intval($minichat['mev']);
						$payload['from'] = intval($utilizator_get['id']);
						if ($payload[$mek] != $payload['from']){
							if ($GLOBALS['mobile_ismobile'] == true){
								echo cs_url . '/mesaje/' .  $utilizator_get['uri'];	
							}else{
								echo 'minichat_update(' . json_encode($payload) . ')';														
							}
						}else{
							echo 'javascript:void(0)';
						}
						?>' href="javascript:void(0)" style="border: 1px solid lime;">
						<i class="fa fa-commenting-o" aria-hidden="true"></i> ONLINE - start chat
					</a>
				</div>
				<div class="usersstatus_custom_offline">
					<a class="btn btn-primary " onclick="<?php 
						if (isset($_SESSION['cs_users_id'])){
							echo 'cs(\'mesaje/create_rs\',{to:' . $doctor_get['id'] . '})';
						}else{
							echo 'cs(\'users/login_rs\')';
						}
						?>" href="javascript:void(0)" >
						<i class="fa fa-envelope" aria-hidden="true"></i> Mesaj
					</a>
				</div>
			</div>
		</div>
	</div>
	<?php 
	if ($unitati['resp']['records'] > 0){
		foreach($unitati['resp']['rows'] as $unitate){
			$localitati_spitale_sql = 'SELECT localitati.id';
			$localitati_spitale_sql .= '	, localitati.denumire';
			$localitati_spitale_sql .= '	, localitati.uri';
			$localitati_spitale_sql .= ' FROM localitati_spitale';
			$localitati_spitale_sql .= ' LEFT JOIN localitati ON localitati_spitale.localitate = localitati.id';
			$localitati_spitale_sql .= ' WHERE localitati_spitale.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($unitate->id);
			$localitati_spitale_sql .= ' ORDER BY localitati.id ASC';

			$localitati_spitale = cs("_cs_grid/get",array('db_sql'=>$localitati_spitale_sql));
			cscheck($localitati_spitale);

			$localitate_uri = array();
			$localitate_denumire = array();
			foreach($localitati_spitale['resp']['rows'] as $item_sd){
				$localitate_uri[] = $item_sd->uri;
				$localitate_denumire[] = array(
					'denumire'=>$item_sd->denumire,
					'uri'=>implode('/',$localitate_uri),
				);
			}
			$localitate_uri  = implode('/',$localitate_uri);
	?>
	<div class="row" style="border-top: 1px solid #ddd;margin-top:5px;padding:5px 0;">
		<?php ?>
		<div class="col-sm-4 text-center">
			<?php if (intval($unitate->logo) > 0){ ?>
			<a href="<?php 
				echo cs_url . '/doctori/' . $localitate_uri . '/' . $unitate->uri
			?>">
				<img id="spital_logo" src="<?php echo cs_url."/csapi/images/view/?thumb=0&id=" . $unitate->logo;?>" class="spital_logo" alt="Spital Logo">
			</a>
			<?php }?>			
		</div>
		<div class="col-sm-8">
			<div>
				<a href="<?php 
					echo cs_url . '/doctori/' . $localitate_uri . '/' . $unitate->uri
				?>">
				<?php echo htmlentities($unitate->nume)?>
				</a>
			</div>
			<?php if ($unitate->specializare != null){?>
			<div>
				<a class="btn btn-info " href="<?php 
					echo cs_url . '/doctori/' . $localitate_uri . '/' . $unitate->uri . '/' . $utilizator_get['uri']
				?>">
					Programare online <?php echo $unitate->specializare;?>
				</a>
			</div>
			<?php }else{?>
				<div>Manager</div>
			<?php }?>
			<div>
				<?php
				foreach($localitate_denumire as $locitem){?>
				<a href="<?php echo cs_url . '/doctori/' . $locitem['uri']?>">
					<?php echo $locitem['denumire']?>
				</a>
				<?php }?>
			</div>
			<?php if ($unitate->adresa != ''){?>
			<div>
				<?php echo $unitate->adresa;?>
			</div>
			<?php }?>
			<?php if ($unitate->telefon != ''){?>
			<div>
				<a class="btn btn-success " href="tel:<?php echo $unitate->telefon;?>">
					<i class="fa fa-phone" aria-hidden="true"></i> <?php echo $unitate->telefon;?>
				</a>
			</div>
			<?php }?>
		</div>
	</div>
	<?php 	}?>
	<?php }?>
</div>
<?php }else{?>
oupssss.. nu am gasit ce căutați...
<?php }?>
<?php 
function footer_ob(){
	global 
		$cauta_localitati_breadcrumb
		, $cauta_specializari_breadcrumb
		, $cauta_uridecode
		, $cauta_date
		, $cauta_localitate_id
		, $cauta_specializare_id
		, $programari_date
		, $specializari_id
		, $doctor_get
		, $spitale_get
	;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<script>
		cauta_specializari_breadcrumb_parentid = 0;
		cauta_specializari_breadcrumb_parentparent = 0;
		cauta_localitati_breadcrumb_nodearr = <?php echo json_encode($cauta_localitati_breadcrumb['resp']['nodearr'])?>;
		cauta_specializari_breadcrumb_nodearr = <?php echo json_encode($cauta_specializari_breadcrumb['resp']['nodearr'])?>;
		cauta_localitate_id = <?php if ($cauta_localitate_id > 0){ echo $cauta_localitate_id; }else{echo 'null';}?>;
		cauta_specializare_id = <?php if ($cauta_specializare_id > 0){ echo $cauta_specializare_id; }else{echo 'null';}?>;


	</script>
	<script src="<?php echo cs_url;?>/js/cauta.js?timestamp=<?php echo cs_updatescript;?>"></script>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>