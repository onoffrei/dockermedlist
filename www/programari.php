<?php
require_once("_cs_config.php");

$menu_get = cs('menu/get');
cscheck($menu_get);

$doctor_id = 0;
if (isset($_REQUEST['doctor']) && (intval($_REQUEST['doctor']) > 0)) {
	$doctor_get = cs('users/get', array(
		"filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$_REQUEST['doctor']),
	))));
	if ($doctor_get != null) $doctor_id = intval($doctor_get['id']);
}
$spital_id = 0;
if (isset($_REQUEST['spital']) && (intval($_REQUEST['spital']) > 0)) {
	$spital_get = cs('spitale/get', array(
		"filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$_REQUEST['spital']),
	))));
	if ($spital_get != null) $spital_id = intval($spital_get['id']);
}

$cautadate = date('Y-m-d');
if (
	isset($_REQUEST['y']) && (intval($_REQUEST['y']) > 0)
	&& isset($_REQUEST['m']) && (intval($_REQUEST['m']) > 0)
	&& isset($_REQUEST['d']) && (intval($_REQUEST['d']) > 0)
	){
	$cautadate = date('Y-m-d', strtotime($_REQUEST['y']
		. '-' . $_REQUEST['m']
		. '-' . $_REQUEST['d']
	));
}
$specializari_id = 0;
if (isset($_REQUEST['specializare']) && (intval($_REQUEST['specializare']) > 0)) {
	$specializari_id = intval($_REQUEST['specializare']);
}

$item_specializari_breadcrumb = cs('specializari/breadcrumb',array(
	'catid'=>$specializari_id,
));
cscheck($item_specializari_breadcrumb);
$item_specializare_denumire = array();
foreach($item_specializari_breadcrumb['resp']['nodearr'] as $item_sd){
	array_unshift($item_specializare_denumire,$item_sd['denumire']);
}

function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari medicale online</title>
	<meta name="robots" content="noindex, nofollow" />	
	<style>
		.programari_submit{
			width: 330px;
			padding: 15px;
			margin: 0 auto;
		}
		.programari_alegeora{
			width: 330px;
			margin: 0 auto;
		}
		#v-cal .vcal-date.disponibil{
			background-color: #e4fdc7;
		}
		#v-cal .vcal-date.epuizat{
			background-color: #fdc0ad;
		}
		.interval_item{
			display: inline-block;
			background-color: coral;
			color: black;
			padding: 7px;
			border-radius: 14px;
			margin-bottom:3px;
			margin-left: 3px;
			min-width: 53px !important;
		}
		.interval_item:hover{
			text-decoration: none;
			color: white;
			background-color: brown;
		}
		.interval_item.status_epuizat{
			background-color: lightgray;
			text-decoration: line-through;
			color: unset;
		}
	</style>
	<link href="<?php echo cs_url;?>/css/vanillaCalendar.css" type="text/css" rel="stylesheet"/>
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
} 
$GLOBALS['header_ob'] = header_ob();
require_once(cs_path . DIRECTORY_SEPARATOR . 'header.php' );
?>
<div class="container-fluid">
	<?php if (($doctor_id == 0)||($spital_id == 0)||($specializari_id == 0)){?>
	<div class="row">
		<div class="col-sm-12">
			<span>
				Invalid Doctor/spital Id
			<span>
		</div>
	</div>
	<?php }else{?>
	<div class="row">
		<div class="col-sm-12">
			<span>
				<?php echo $spital_get['nume']?> |
				<?php echo $doctor_get['nume']?> |
				<?php echo implode('->',$item_specializare_denumire);?>
			<span>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 col-md-5">
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
		<div class="col-sm-6 col-md-7" id="interval_list">
			<?php
				$programari_daycheck = cs('programari/daycheck',array(
					'doctor'=>$doctor_id,
					'spital'=>$spital_id,
					'd'=>date('d',strtotime($cautadate)),
					'm'=>date('m',strtotime($cautadate)),
					'y'=>date('Y',strtotime($cautadate)),
				));
				cscheck($programari_daycheck);
				foreach($programari_daycheck['resp'] as $interval){
					?>
					<a href="javascript:void(0)" class="interval_item status_<?php echo $interval['status']?>" <?php if ($interval['status'] == 'disponibil'){?>onclick="finalizaezaprogramare({doctor:<?php echo $doctor_id
																		?>,spital:<?php echo $spital_id
																		?>,y:<?php echo date('Y',strtotime($interval['start']))
																		?>,m:<?php echo date('m',strtotime($interval['start']))
																		?>,d:<?php echo date('d',strtotime($interval['start']))
																		?>,h:<?php echo date('H',strtotime($interval['start']))
																		?>,i:<?php echo date('i',strtotime($interval['start']))
																		?>,specializare:<?php echo $specializari_id?>})" <?php }?>>
						<?php echo date('H:i',strtotime($interval['start'])) ?>
					</a>
					<?php 
				}
			?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="programari_submit">
				<button class="btn btn-success" onclick="programari_aplicaonclick()"><i class="fa fa-clock-o" aria-hidden="true"></i> Apasa pentru a te programa la ora selectata</button> 
			</div>
		</div>
	</div>
	<?php }?>
</div>
<?php 
function footer_ob(){
	global $doctor_id, $spital_id, $cautadate, $specializari_id;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<?php if (($doctor_id != 0)&&($spital_id != 0)){?>
	<script>
		programari_doctor_id = <?php echo $doctor_id;?>;
		programari_spital_id = <?php echo $spital_id;?>;
		programari_cautadate = '<?php echo $cautadate;?>';
		programari_specializari_id = <?php echo $specializari_id;?>;
		finalizaezaprogramare = function(p_arr){
			console.log(p_arr)
			var form = document.createElement("form");
			form.style.display = "none";
			form.method = "POST";
			form.action = "finalizareprogramare"; 
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
	<script src="<?php echo cs_url_po;?>/js/vanillaCalendar.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script src="<?php echo cs_url_po;?>/js/programari.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<?php }?>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>