<?php
cscheck(array('success'=>isset($GLOBALS['cauta_uridecode'])));
$cauta_uridecode = $GLOBALS['cauta_uridecode'];

$cauta_initparams = cs('cauta/initparams');
cscheck($cauta_initparams);
extract($cauta_initparams['resp']);
$cauta_form = cs('cauta/form',array('cauta_initparams'=>$cauta_initparams));
cscheck($cauta_form);
$is_success = true;

if (count($cauta_uridecode['rest']) < 2){
	$is_success = false;
}
$spitale_get = cs('spitale/get', array("filters"=>array("rules"=>array(
	array("field"=>"uri","op"=>"eq","data"=>$cauta_uridecode['rest'][0])
))));
if ($spitale_get == null) {$is_success = false;}

if ($is_success){
	$doctor_get = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"uri","op"=>"eq","data"=>$cauta_uridecode['rest'][1])
	))));
	if ($doctor_get == null) {$is_success = false;}
}
if ($is_success){
	$logs_count = cs('logs/count', array(
		'alttype'=>2,
		'altid'=>$doctor_get['id'],
	));
	if (!isset($logs_count['success']) || ($logs_count['success'] == false)) {$is_success = false;}
}

if ($is_success){
	$spitale_users_get_param = array("filters"=>array("rules"=>array(
		array("field"=>"user","op"=>"eq","data"=>$doctor_get['id']),
		array("field"=>"spital","op"=>"eq","data"=>$spitale_get['id']),
	)));
	$spitale_users_get = cs('spitale_users/get', $spitale_users_get_param);
	if ($spitale_users_get == null) {$is_success = false;}
}
if ($is_success){
	$localitati_spitale_sql = 'SELECT localitati.id';
	$localitati_spitale_sql .= '	, localitati.denumire';
	$localitati_spitale_sql .= '	, localitati.uri';
	$localitati_spitale_sql .= ' FROM localitati_spitale';
	$localitati_spitale_sql .= ' LEFT JOIN localitati ON localitati_spitale.localitate = localitati.id';
	$localitati_spitale_sql .= ' WHERE localitati_spitale.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($spitale_get['id']);
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

	$localitate_uri1 = array();
	foreach($cauta_uridecode['items']['localitati'] as $item_sd){
		$localitate_uri1[] = $item_sd['uri'];
	}
	$localitate_uri1  = implode('/',$localitate_uri1);
	if ($localitate_uri != $localitate_uri1){
		header("Location: " . cs_url_scheme . '://' . cs_url_host 
			. '/doctori/' 
			. $localitate_uri
			. '/' . $spitale_get['uri']
			. '/' . $doctor_get['uri']
		);
		exit;
	}
}
if ($is_success){
	$specializare_sql = "SELECT ";
	$specializare_sql .= "    specializari.denumire as specializari_denumire";
	$specializare_sql .= "    ,specializari.id as specializari_id";
	$specializare_sql .= " FROM specializari_user_spitale";
	$specializare_sql .=  " LEFT JOIN specializari ON specializari_user_spitale.specializare = specializari.id";
	$specializare_sql .=  " WHERE specializari.parent = 0";
	$specializare_sql .=  " AND specializari_user_spitale.user = " . $doctor_get['id'];
	$specializare_sql .=  " LIMIT 0,1";

	$specializare = cs("_cs_grid/get",array('db_sql'=>$specializare_sql));
	if (!isset($specializare['success']) || ($specializare['success'] == false)) {$is_success = false;}
	if ($specializare['resp']['records'] != 1) {$is_success = false;}
}
if ($is_success){
	$programari_date = date('Y-m-d');
	if (
		isset($_REQUEST['y']) && (intval($_REQUEST['y']) > 0)
		&& isset($_REQUEST['m']) && (intval($_REQUEST['m']) > 0)
		&& isset($_REQUEST['d']) && (intval($_REQUEST['d']) > 0)
		){
		$programari_date = date('Y-m-d', strtotime($_REQUEST['y']
			. '-' . $_REQUEST['m']
			. '-' . $_REQUEST['d']
		));
	}
	$specializari_id = $specializare['resp']['rows'][0]->specializari_id;
	if (isset($_REQUEST['specializare']) && (intval($_REQUEST['specializare']) > 0)){
		$specializari_id = intval($_REQUEST['specializare']);
	}

	$specializari_sql = 'SELECT specializari_main.id AS specializari_id';
	$specializari_sql .= '	, specializari_main.denumire AS specializari_denumire';
	$specializari_sql .= ' FROM specializari_user_spitale';
	$specializari_sql .= ' LEFT JOIN specializari AS specializari_main ON specializari_user_spitale.specializare = specializari_main.id';
	$specializari_sql .= ' WHERE specializari_user_spitale.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($spitale_get['id']);
	$specializari_sql .= ' AND specializari_user_spitale.user = '. $doctor_get['id'];
	$specializari_sql .= ' AND NOT EXISTS (';
	$specializari_sql .= ' 	SELECT * ';
	$specializari_sql .= ' 	FROM specializari specializari_inner ';
	$specializari_sql .= ' 	WHERE specializari_main.id = specializari_inner.parent';
	$specializari_sql .= ' )';

	$specializari = cs("_cs_grid/get",array('db_sql'=>$specializari_sql));
	cscheck($specializari);
}
/*
$GLOBALS['mobile_ismobile'] = cs('mobile/ismobile');
if ($GLOBALS['mobile_ismobile'] == true){
	require_once(cs_path . DIRECTORY_SEPARATOR . 'doctori_doctor_mob.php' );
}else{
	require_once(cs_path . DIRECTORY_SEPARATOR . 'doctori_doctor_desktop.php' );
}
*/
?>
<?php 
function header_ob(){ 
	global $doctor_get, $specializare, $spitale_get, $is_success;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	if ($is_success){
	?>
	<title><?php echo htmlspecialchars($doctor_get['nume']);?> - Programare online doctor <?php echo $specializare['resp']['rows'][0]->specializari_denumire?> - MedList.ro</title>
	<meta name="description" content="<?php echo htmlspecialchars($doctor_get['nume']);?> - Programare online medic <?php echo $specializare['resp']['rows'][0]->specializari_denumire?> - MedList.ro" />
	<meta name="keywords" content="<?php echo htmlspecialchars($doctor_get['nume']);?> - Programare online medic <?php echo $specializare['resp']['rows'][0]->specializari_denumire?> - MedList.ro" />
	<meta name="robots" content="index, follow" />	
	<link href="<?php echo cs_url_po;?>/css/vanillaCalendar.css?timestamp=<?php echo cs_updatescript;?>" type="text/css" rel="stylesheet"/>
	<?php 
		$ogimage = cs_url."/images/logo_640x246.png";
		if ($doctor_get['image'] > 0){
			$ogimage = cs_url."/csapi/images/view/?thumb=0&id=" . $doctor_get['image'];
		}else if ($spitale_get['logo'] > 0){
			$ogimage = cs_url."/csapi/images/view/?thumb=0&id=" . $spitale_get['logo'];
		}
	?>
	<meta property="og:title" content="<?php echo htmlspecialchars($doctor_get['nume']);?> - <?php echo $specializare['resp']['rows'][0]->specializari_denumire?> - MedList.ro" />
	<meta property="og:image" content="<?php echo $ogimage;?>" />
	<?php }?>
	<style>
		.secondcontact{
			top: 50px;
			position: fixed;
			width: 300px;
			z-index: 50;
			display:none;
		}
		.secondcontact.active{
			display:block;
		}
		@media all and (max-width: 768px) {
			.secondcontact {display: none!important;}
		}
		#cauta{
			padding-top: 10px;
			padding-bottom:10px;
			background-color: #60b0f4;
		}
		.spital_logo {
			width: 130px;
			height: 52px;
		}
		.doctor_avatar{
			max-height:150px;
			border-radius: 5px;
		}
		.myleftcol{
			width:250px;
			text-align:center;
			display:inline-block;
			float: left;
		}
		.myleftcol.large{
			width:330px
		}
		.myrightcol{
			display:inline-block;
			float:right;
			width: calc(100% - 250px);
			width: -moz-calc(100% - 250px);
			width: -webkit-calc(100% - 250px);
		}
		.myrightcol.large{
			width: calc(100% - 330px);
			width: -moz-calc(100% - 330px);
			width: -webkit-calc(100% - 330px);
		}
		@media all and (max-width: 768px) {
			.myleftcol{
				width:100% !important;
			}
			.myrightcol{
				width:100% !important;
			}
		}
		#v-cal .vcal-date.disponibil{
			background-color: #e4fdc7;
		}
		#v-cal .vcal-date.epuizat{
			background-color: #fdc0ad;
		}
		.interval_item{
			display: inline-block;
			background-color: #e4fdc7;;
			color: black;
			padding: 7px;
			border-radius: 14px;
			margin-bottom:3px;
			margin-left: 3px;
			min-width: 53px !important;
		}
		.interval_item:hover{
			text-decoration: none;
			background-color: chartreuse;
		}
		.interval_item.status_epuizat{
			background-color: lightgray;
			text-decoration: line-through;
			color: unset;
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
<?php 
function view_contact(){
	global $doctor_get, $spitale_get, $minichat, $localitate_denumire;
	$ret = array('success'=>false, 'resp'=>array());
	ob_start();
	?>
	<?php 
	$phonecall = $doctor_get['telefon'];
	if ($phonecall == '') $phonecall = $spitale_get['telefon'];
	?>
	<?php if ($phonecall != ''){?>
	<div class="row">
		<div class="col-xs-12">
			<a class="btn btn-success btn-block" href="tel:<?php echo $phonecall?>">
				<i class="fa fa-phone" aria-hidden="true"></i> <?php echo $phonecall?>
			</a>
		</div>
	</div>
	<?php }?>
	<div class="row" style="margin-top:7px">
		<div class="col-xs-12">
			<div class="usersstatus <?php 										
				$partstatus = 'offline';
				if ((strtotime(date('Y-m-d H:i:s')) - strtotime($doctor_get['date'])) > usersstatus_maxidle){
					$partstatus = 'offline';
				}else{
					$partstatus = 'online';
				}
				if (intval($doctor_get['status']) == 0){$partstatus = 'offline';}
				echo $partstatus;
				?>" users="<?php echo $doctor_get['id']
				?>" style="display:block">
				<div class="usersstatus_custom_online" >
					<a class="btn btn-default btn-block" onclick='<?php 
						$payload = array();
						$mek = ($minichat['mek']=='browser'?$minichat['mek']:'to');
						$payload[$mek] = intval($minichat['mev']);
						$payload['from'] = intval($doctor_get['id']);
						if ($payload[$mek] != $payload['from']){
							if ($GLOBALS['mobile_ismobile'] == true){
								echo cs_url . '/mesaje/' .  $doctor_get['uri'];	
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
					<a class="btn btn-default btn-block" onclick="<?php 
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
	<div class="row" style="border-top: 1px solid #ddd;margin-top:5px;padding:5px 0;">
		<div class="col-xs-12" >
			Adresa: 
		</div>
		<div class="col-xs-12">
			<?php
			foreach($localitate_denumire as $locitem){?>
			<a href="<?php echo cs_url . '/doctori/' . $locitem['uri']?>">
				<?php echo $locitem['denumire']?>
			</a>
			<?php }?>
		</div>
		<?php if ($spitale_get['adresa'] != ''){?>
		<div class="col-xs-12">
			<p><?php echo htmlentities($spitale_get['adresa'])?></p>
		</div>
		<?php }?>
	</div>
	<?php
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$view_contact = view_contact();
cscheck($view_contact);
?>
<?php echo $cauta_form['resp']['html']?>
<?php if ($is_success){?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="detail-left view_content" style="">
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
					<div class="col-xs-12">
						<?php if ($spitale_get['logo'] > 0){?>
						<div class="myleftcol" style="">
							<a href="<?php 
								echo cs_url . '/doctori/' . $cauta_uridecode['items']['localitati'][0]['uri'] . '/' . $cauta_uridecode['rest'][0]
							?>">
							<img id="spital_logo" src="<?php echo cs_url."/csapi/images/view/?thumb=0&id=" . $spitale_get['logo'];?>" class="spital_logo" alt="Spital Logo">
							</a>
						</div>
						<?php }?>
						<div class="<?php if ($spitale_get['logo'] > 0){ echo 'myrightcol';}?>" style="">
							<h2 style="margin:3px; font-size: 18px;">
								<a href="<?php 
									echo cs_url . '/doctori/' . $cauta_uridecode['items']['localitati'][0]['uri'] . '/' . $cauta_uridecode['rest'][0]
								?>">
								<?php echo htmlentities($spitale_get['nume'])?>
								</a>
							</h2>
							<p style="margin:3px;">
								<?php
								foreach($localitate_denumire as $locitem){?>
								<a href="<?php echo cs_url . '/doctori/' . $locitem['uri']?>">
									<?php echo $locitem['denumire']?>
								</a>
								<?php }?>
							</p>
						</div>
					</div>
				</div>
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
					<div class="col-xs-12">
						<div class="myleftcol" style="">
							<div class="usersstatus <?php 
									$partstatus = 'offline';
									if ((strtotime(date('Y-m-d H:i:s')) - strtotime($doctor_get['date'])) > usersstatus_maxidle){
										$partstatus = 'offline';
									}else{
										$partstatus = 'online';
									}
									if (intval($doctor_get['status']) == 0){$partstatus = 'offline';}
									echo $partstatus;
									?>" users="<?php echo $doctor_get['id']?>">
								<div class="usersstatus_circle_online"></div>
								<div class="usersstatus_circle_offline"></div>
								<img src="<?php 
									if (intval($doctor_get['image']) > 0){
										echo cs_url."/csapi/images/view/?thumb=0&id=" . $doctor_get['image'];
									}else{
										echo cs_url."/images/default_avatar.jpg";
									}
								?>" class="doctor_avatar" style="" alt="User Image">
							</div>
						</div>
						<div class="myrightcol" style="">
							<h4>
								<a href="<?php echo cs_url . '/profil-utilizator/' . $doctor_get['uri'] . '.html'; ?>" >
									<?php echo $doctor_get['nume']?>
								</a> 
							</h4>
							<h1 style="font-size:15px">
								Programare online medic <?php 
								echo $specializare['resp']['rows'][0]->specializari_denumire; 
								?>
							</h1>
						</div>
					</div>
				</div>
				<?php if ($spitale_users_get['descriere'] != ''){?>
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
					<div class="col-xs-12">
						<h3>Descriere</h3>
					</div>
					<div class="col-xs-12">
						<?php echo htmlspecialchars($spitale_users_get['descriere'])?>
					</div>
				</div>
				<?php }?>
				
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
					<?php 
					$planificare_grid = cs('planificare/grid', array(
						"filters"=>array("rules"=>array(
							array("field"=>"spital","op"=>"eq","data"=>$spitale_get['id']),
							array("field"=>"doctor","op"=>"eq","data"=>$doctor_get['id']),
							array("field"=>"start","op"=>"ge","data"=>date("Y-m-d H:i:s")),
						)),
						"rows"=>1
					));
					cscheck($planificare_grid);
					if ($planificare_grid['resp']['records'] == 0){
					?>
					<div class="col-xs-12">
						Programul doctorului nu este incarcat pe site, pentru programari luati legatura cu doctorul telefonic sau prin mesaj.
					</div>
					<?php }else{?>
					<div class="col-xs-12">
						<h3>Program personal</h3>
					</div>
					<div class="col-xs-12">
						<div class="myleftcol large" style="">
							<div id="v-cal" class="caledar-custom"></div>
							<div id="v-cal-hidden" style="display:none">
								<p class="demo-picked" style="display:none">
									Date picked:
									<span data-calendar-label="picked"></span>
								</p>
								<div class="vcal-header">
									<button class="vcal-btn" data-calendar-toggle="previous">
										<svg height="24" version="1.1" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
											<path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"></path>
										</svg>
									</button>

									<div class="vcal-header__label" data-calendar-label="month">
										March 2017
									</div>
									<button class="vcal-btn" data-calendar-toggle="next">
										<svg height="24" version="1.1" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
											<path d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z"></path>
										</svg>
									</button>
								</div>
								<div class="vcal-week">
									<span>LU</span>
									<span>MA</span>
									<span>MI</span>
									<span>JO</span>
									<span>VI</span>
									<span>SA</span>
									<span>DU</span>
								</div>
								<div class="vcal-body" data-calendar-area="month"></div>
							</div>
						</div>
						<div class="myrightcol large" style="">
							<div>
								ore disponibile:
							</div>
							<div id="interval_list">
								<?php
								$programari_daycheck = cs('programari/daycheck',array(
									'doctor'=>$doctor_get['id'],
									'spital'=>$spitale_get['id'],
									'd'=>date('d',strtotime($programari_date)),
									'm'=>date('m',strtotime($programari_date)),
									'y'=>date('Y',strtotime($programari_date)),
								));
								cscheck($programari_daycheck);
								foreach($programari_daycheck['resp'] as $interval){
									if (strtotime($interval['start']) > strtotime(date('Y-m-d H:i:s'))){
								?>
									<a href="javascript:void(0)" class="interval_item status_<?php echo $interval['status']?>" <?php if ($interval['status'] == 'disponibil'){?>onclick="finalizaezaprogramare({doctor:<?php echo $doctor_get['id']
											?>,spital:<?php echo $spitale_get['id']
											?>,y:<?php echo date('Y',strtotime($interval['start']))
											?>,m:<?php echo date('m',strtotime($interval['start']))
											?>,d:<?php echo date('d',strtotime($interval['start']))
											?>,h:<?php echo date('H',strtotime($interval['start']))
											?>,i:<?php echo date('i',strtotime($interval['start']))
											?>,specializare:<?php echo $specializari_id?>})" <?php }?>>
										<?php echo date('H:i',strtotime($interval['start'])) ?>
									</a>
									<?php }
									}
								?>
							</div>
						</div>
					</div>
					<?php }?>
				</div>
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
					<div class="col-xs-12">
						<h3>Servicii oferite</h3>
					</div>
					<div class="col-xs-12">
						<ul class="list-group">
						<?php
						foreach($specializari['resp']['rows'] as $specializare){
							$valoare = cs('detaliiservicii/get',array("filters"=>array("rules"=>array(
								array("field"=>"doctor","op"=>"eq","data"=>$doctor_get['id']),
								array("field"=>"spital","op"=>"eq","data"=>$spitale_get['id']),
								array("field"=>"specializare","op"=>"eq","data"=>$specializare->specializari_id),
								array("field"=>"numedetaliu","op"=>"eq","data"=>'valoare')
							))));
							$item_specializari_breadcrumb = cs('specializari/breadcrumb',array(
								'catid'=>$specializare->specializari_id
							));
							cscheck($item_specializari_breadcrumb);
							$item_specializare_denumire = array();
							foreach($item_specializari_breadcrumb['resp']['nodearr'] as $item_sd){
								array_unshift($item_specializare_denumire,$item_sd['denumire']);
							}
							
							?>
							<li class="list-group-item">
								<?php if($valoare != null){?>
								<span class="badge"><?php echo $valoare['valoaredetaliu'] .' lei';?></span>
								<?php }?>
								<?php echo implode('/',$item_specializare_denumire);?>
							</li>
							<?php
						}
						?>
						</ul>
					</div>
				</div>
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;background-color:#f2f4f5">
					<?php 
						$comments_listhtml = cs('comments/listhtml',array(
							'altid'=>$doctor_get['id'],
							'alttype'=>2,
						));
						cscheck($comments_listhtml);
						$comments_notaget = cs('comments/notaget',array(
							'altid'=>$doctor_get['id'],
							'alttype'=>2,
						));
						cscheck($comments_notaget);
						$comments_count = cs('comments/count',array(
							'altid'=>$doctor_get['id'],
							'alttype'=>2,
						));
						cscheck($comments_count);
					?>
					<div class="col-xs-12">
						<h3 style="font-size:14px">
							<div>Comentarii (<?php echo $comments_count['resp']['count']?>)</div>
							<div>Nota (<?php echo $comments_notaget['resp']['html']?>)</div>
						</h3>
					</div>
					<div class="col-xs-12" id="comments_item_0">
					<?php 
						$comments_addhtml = cs('comments/addhtml',array(
							'altid'=>$doctor_get['id'],
							'alttype'=>2,
							'parent'=>0,
						));
						cscheck($comments_addhtml);
						echo $comments_addhtml['resp']['html'];
					?> 
					</div>
					<div class="col-xs-12">
					<?php 
						echo $comments_listhtml['resp']['html'];
					?>
					</div>
				</div>
			</div>
			<div class="menu-right" style="">
				<div class="row">
					<div class="col-xs-12">
						<div class="panel panel-default secondcontact" style="">
							<div class="panel-body">
								<?php echo $view_contact['resp']['html']; ?>
							</div>
						</div>
						<div class="panel panel-default" style="">
							<div class="panel-body">
								<?php echo $view_contact['resp']['html']; ?>
							</div>
						</div>
						<div class="panel panel-default" style="">
							<div class="panel-body">
								<div class="row" style="">
									<div class="col-xs-12 text-center" style="">
										<a href="<?php echo cs_url . '/statistica-doctor/' . $doctor_get['uri'] . '.html'; ?>" >
											<i class="fa fa-eye" aria-hidden="true"></i> Vizualizări: <?php echo $logs_count['resp']['count']?>
										</a> 
									</div>
								</div>
								<div class="row" style="">
									<div class="col-xs-12 text-center" style="border-top: 1px solid #ddd;margin-bottom:5px;padding:5px 0;">
										<h3 style="margin:0;font-size:15px">Distribuie anuntul pe</h3>
									</div>
									<div class="col-xs-12 text-center" style="">
										<a rel="nofollow" title="Embed Code Widget" href="javascript:void(0)" onclick="embedshow()" class="fa fa-mylogin fa-code" style="padding: 18px;font-size: 18px;width: 18px;margin: 1px 2px;"></a>
										<a rel="nofollow" title="Distribuie pe Facebook" href="javascript:void(0)" onclick="window.open('https://www.facebook.com/sharer.php?u='+encodeURIComponent(location.href)+'&amp;t='+encodeURIComponent(document.title),'sharer','toolbar=0,status=0,width=626,height=436')" class="fa fa-mylogin fa-facebook" style="padding: 18px;font-size: 18px;width: 18px;margin: 1px 2px;"></a>
										<a rel="nofollow" title="Distribuie pe Google+" href="javascript:void(0)" onclick="window.open('https://plus.google.com/share?url='+encodeURIComponent(location.href)+'','', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600')" class="fa fa-mylogin fa-google" style="padding: 18px;font-size: 18px;width: 18px;margin: 1px 2px;"></a>
										<?php if ($GLOBALS['mobile_ismobile'] == true){?>
										<a rel="nofollow" title="Distribuie pe whatsapp" href="javascript:void(0)" onclick="window.open('whatsapp://send?text=' + encodeURIComponent(location.href))" class="fa fa-mylogin fa-whatsapp" style="padding: 18px;font-size: 18px;width: 18px;margin: 1px 2px;"></a>
										<a rel="nofollow" title="Distribuie pe viber" href="javascript:void(0)" onclick="window.open('viber://forward?text=' + encodeURIComponent(location.href))" class="fa fa-mylogin fa-phone" style="padding: 18px;font-size: 18px;width: 18px;margin: 1px 2px; background: #59267c; color:white"></a>
										<?php }?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="embed_modal" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-md" style="z-index:105">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="fa fa-code" aria-hidden="true"></i> Embed Code Widget</h4>
			</div>
			<div class="modal-body">
				<input type="text" class="form-control" value='<iframe id="melistiframe" src="<?php echo cs_url;?>/programari_embediframe.php?doctor=<?php echo $doctor_get['id'];?>&spital=<?php echo $spitale_get['id'];?>" style="border:none;background-color: transparent;min-width:300px;min-height:360px;width:100%" allowtransparency="true"><p>Your browser does not support iframes.</p></iframe>"' id="embed_input">
				<button type="button" class="btn btn-success btn-block" onclick="embed_on_copyclick()">Copiaza text</button>
			</div>
			<div class="modal-footer">
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php
	$userissame = false;
	if (isset($_SESSION['cs_users_id']) && ($_SESSION['cs_users_id'] == $doctor_get['id'])) $userissame = true;
?>
<?php if (!$userissame){?>
<div class="usersstatus minichat-start-button <?php 
		$partstatus = 'offline';
		if ((strtotime(date('Y-m-d H:i:s')) - strtotime($doctor_get['date'])) > usersstatus_maxidle){
			$partstatus = 'offline';
		}else{
			$partstatus = 'online';
		}
		if (intval($doctor_get['status']) == 0){$partstatus = 'offline';}
		echo $partstatus;
	?> " users="<?php echo $doctor_get['id'] ?>">
	<div class="usersstatus_custom_online" >
		<button class="btn btn-success btn-lg" type="button" onclick='<?php 
			$payload = array();
			$mek = ($minichat['mek']=='browser'?$minichat['mek']:'to');
			$payload[$mek] = intval($minichat['mev']);
			$payload['from'] = $doctor_get['id'];
			if ($payload[$mek] != $payload['from']){
				echo 'minichat_show(' . json_encode($payload) . ')';														
			}else{
				echo 'javascript:void(0)';
			}
			?>'><i class="fa fa-comment-o" aria-hidden="true"></i> <?php echo $doctor_get['nume'] ?> <span class="minichat_count"></span></button>
	</div>
</div>
<?php }?>

<?php }else{?>
oupssss.. nu am gasit spitalul cautat...
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
		
		, $planificare_grid
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

		programari_doctor_id = <?php echo $doctor_get['id'];?>;
		programari_spital_id = <?php echo $spitale_get['id'];?>;
		programari_cautadate = '<?php echo $programari_date;?>';
		programari_specializari_id = parseInt('<?php echo $specializari_id;?>');
		
		usersstatus_onload_logs = {alttype:2,altid:<?php echo $doctor_get['id'];?>};
		
		finalizaezaprogramare = function(p_arr){
			console.log(p_arr)
			var form = document.createElement("form");
			form.style.display = "none";
			form.method = "POST";
			form.action = "/finalizareprogramare"; 
			$(form).append(Object.keys(p_arr).map(function(key, index) {
				return $('<input/>',{
					name:key,
					value:p_arr[key],
				})
			}))
			document.body.appendChild(form);
			form.submit();
		}
		document.addEventListener("DOMContentLoaded", function(d) {
			var secondcontactmaxtop = 60;
			var scroll = getCurrentScroll();
			if (scroll >= secondcontactmaxtop) {
				$('.secondcontact').addClass('active');
			} else {
				$('.secondcontact').removeClass('active');
			}
			$(window).scroll(function() {
				var scroll = getCurrentScroll();
				if (scroll >= secondcontactmaxtop) {
					$('.secondcontact').addClass('active');
				} else {
					$('.secondcontact').removeClass('active');
				}
			});
		})
		function getCurrentScroll() {
			return window.pageYOffset || document.documentElement.scrollTop;
		}
		function embedshow(){
			$("#embed_modal").modal('show')
		}
		function embed_on_copyclick(){
			var copyText = document.getElementById("embed_input");
			copyText.select();
			document.execCommand("copy");
			alert("Copied the text");
		}
	</script>
	<script src="<?php echo cs_url;?>/js/auxiliary-rater-0831401/rater.min.js"></script>
	<script src="<?php echo cs_url;?>/js/cauta.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<?php if ($planificare_grid['resp']['records'] > 0){?>
	<script src="<?php echo cs_url;?>/js/vanillaCalendar.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script src="<?php echo cs_url;?>/js/programari.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<?php }?>
	<script src="<?php echo cs_url;?>/js/doctori_doctor.js?timestamp=<?php echo cs_updatescript;?>"></script>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>