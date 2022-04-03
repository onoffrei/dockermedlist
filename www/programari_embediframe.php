<?php
require_once("_cs_config.php");
$GLOBALS['mobile_ismobile'] = cs('mobile/ismobile');
$programari_date = date('Y-m-d');
$is_success = true;
if ($is_success && isset($_REQUEST['doctor']) && (intval($_REQUEST['doctor']) > 0)) {
	$doctor_get = cs('users/get', array(
		"filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$_REQUEST['doctor']),
	))));
	if ($doctor_get != null){
		$doctor_id = intval($doctor_get['id']);
	}else{
		$is_success = false;
	}
}
if ($is_success && isset($_REQUEST['spital']) && (intval($_REQUEST['spital']) > 0)) {
	$spitale_get = cs('spitale/get', array(
		"filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$_REQUEST['spital']),
	))));
	if ($spitale_get != null){
		$spital_id = intval($spitale_get['id']);
	}else{
		$is_success = false;
	}
}
if ($is_success) {
	$is_success = false;
	$planificare_grid = cs('planificare/grid', array(
		"filters"=>array("rules"=>array(
			array("field"=>"spital","op"=>"eq","data"=>$spitale_get['id']),
			array("field"=>"doctor","op"=>"eq","data"=>$doctor_get['id']),
			array("field"=>"start","op"=>"ge","data"=>date("Y-m-d H:i:s")),
		)),
		"rows"=>1
	));
	if (isset($planificare_grid['success']) && ($planificare_grid['success'] == true)) {
		$is_success = true;
	}	
}
if ($is_success) {
	$is_success = false;
	$programari_daycheck = cs('programari/daycheck',array(
		'doctor'=>$doctor_get['id'],
		'spital'=>$spitale_get['id'],
		'd'=>date('d',strtotime($programari_date)),
		'm'=>date('m',strtotime($programari_date)),
		'y'=>date('Y',strtotime($programari_date)),
	));
	if (isset($programari_daycheck['success']) && ($programari_daycheck['success'] == true)) {
		$is_success = true;
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
	$specializari_id = $specializare['resp']['rows'][0]->specializari_id;
}
?><html lang="ro-RO">
    <head>
		<?php if ($GLOBALS['mobile_ismobile']){?>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link href="<?php echo cs_url;?>/css/style.css?timestamp=<?php echo cs_updatescript;?>" type="text/css" rel="stylesheet"/>
        <link href="<?php echo cs_url;?>/css/stylemobile.css?timestamp=<?php echo cs_updatescript;?>" type="text/css" rel="stylesheet"/>
		<?php }else{?>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="<?php echo cs_url;?>/css/style.css?timestamp=<?php echo cs_updatescript;?>" type="text/css" rel="stylesheet"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<?php }?>
		<link rel="shortcut icon" href="<?php echo cs_url; ?>/icon2.ico" />
		<link href="<?php echo cs_url;?>/css/bootstrap.min.css" type="text/css" rel="stylesheet"/>
		<link href="<?php echo cs_url_po;?>/css/vanillaCalendar.css?timestamp=<?php echo cs_updatescript;?>" type="text/css" rel="stylesheet"/>
		<style>
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
					text-align:center;
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
			html, body{
				padding-top: 0;
				background-color: transparent;
				overflow:hidden;
			}
		</style>
	</head>
	<body>
		<?php if ($is_success){?>
		<?php if ($planificare_grid['resp']['records'] == 0){?>
		<div style="width:100%">
			Programul doctorului nu este incarcat pe site, pentru programari luati legatura cu doctorul telefonic sau prin mesaj.
		</div>
		<?php }else{?>
		<div style="position:absolute;right:0;bottom:0;">Powered by <a href="<?php echo cs_url;?>" target="_blank">medlist.ro</a></div>
		<div style="width:100%;height:100%">
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
		<?php }else{?>
			oupssss.. nu am gasit ce căutați...
		<?php }?>
		<script src="<?php echo cs_url;?>/js/_cs.js?timestamp=<?php echo cs_updatescript;?>"></script>
		<script src="<?php echo cs_url;?>/js/jquery.js?timestamp=<?php echo cs_updatescript;?>"></script>
		<script>
			window.cs_url = "<?php echo cs_url;?>/";
			window.cs_url_po = "<?php echo cs_url_po;?>/";
			window.server_time = "<?php echo date('Y-m-d H:i:s');?>";
			window.mobile_ismobile = <?php echo $GLOBALS['mobile_ismobile']?'true':'false';?>;
			<?php 
			if (defined('cs_debug') && cs_debug){ $new_GLOBALS_array = array(); foreach($GLOBALS as $gn => $gv){ if ($gn != 'GLOBALS') $new_GLOBALS_array[$gn] = $gv;}?>
			console.info('globals',<?php echo json_encode($new_GLOBALS_array);?>);
			<?php } ?>
			
			programari_doctor_id = <?php echo $doctor_get['id'];?>;
			programari_spital_id = <?php echo $spitale_get['id'];?>;
			programari_cautadate = '<?php echo $programari_date;?>';
			programari_specializari_id = parseInt('<?php echo $specializari_id;?>');

			setiframeheight = function(){
				var iframe = window.parent.document.getElementById('melistiframe')
				if (window.parent.innerWidth > 768){
					iframe.style.minHeight = '360px'
				}else{
					iframe.style.minHeight = '490px'
				}
			}
			if (self!==top){
				window.parent.addEventListener('resize', function(){
					setiframeheight()
				})
				setiframeheight()
			}
			finalizaezaprogramare = function(p_arr){
				console.log(p_arr)
				var form = document.createElement("form");
				form.style.display = "none";
				form.method = "POST";
				form.target = "_blank";
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
		</script>
		<?php if ($planificare_grid['resp']['records'] > 0){?>
		<script src="<?php echo cs_url;?>/js/vanillaCalendar.js?timestamp=<?php echo cs_updatescript;?>"></script>
		<script src="<?php echo cs_url;?>/js/programari.js?timestamp=<?php echo cs_updatescript;?>"></script>
		<?php }?>
		<?php if (cs_url_host == 'medlist.ro'){?>
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-128878660-1"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', 'UA-128878660-1');
		</script>
		<?php }?>
		<?php if ($is_success){?>
		<?php }?>
	</body>
</html>
