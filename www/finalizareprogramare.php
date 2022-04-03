<?php
require_once("_cs_config.php");

$reqiredvalues = true;
if (!isset($_REQUEST['specializare'])) $reqiredvalues = false;
if (!isset($_REQUEST['spital'])) $reqiredvalues = false;
if (!isset($_REQUEST['doctor'])) $reqiredvalues = false;
if (!isset($_REQUEST['y'])) $reqiredvalues = false;
if (!isset($_REQUEST['m'])) $reqiredvalues = false;
if (!isset($_REQUEST['d'])) $reqiredvalues = false;
if (!isset($_REQUEST['h'])) $reqiredvalues = false;
if (!isset($_REQUEST['i'])) $reqiredvalues = false;

cscheck(array('success'=>$reqiredvalues));

$spitale_get = cs('spitale/get', array("filters"=>array("rules"=>array(
	array("field"=>"id","op"=>"eq","data"=>$_REQUEST['spital'])
))));
cscheck(array('success'=>$spitale_get!=null));

$doctor_get = cs('users/get', array("filters"=>array("rules"=>array(
	array("field"=>"id","op"=>"eq","data"=>$_REQUEST['doctor'])
))));
cscheck(array('success'=>$doctor_get!=null));

require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
$doc_sql = "SELECT ";
$doc_sql .= "    users.id as id";
$doc_sql .= "    ,users.email as email";
$doc_sql .= "    ,users.nume as nume";
$doc_sql .= "    ,users.uri as uri";
$doc_sql .= "    ,users.image as image";
$doc_sql .= "    ,spitale.nume as spitale_nume";
$doc_sql .= "    ,spitale.adresa as spitale_adresa";
$doc_sql .= "    ,spitale.uri as spitale_uri";
$doc_sql .= "    ,spitale_users.descriere as spitale_users_descriere";
$doc_sql .= " FROM spitale_users";
$doc_sql .=  " LEFT JOIN users ON spitale_users.user = users.id";
$doc_sql .=  " LEFT JOIN spitale ON spitale_users.spital = spitale.id";
$doc_sql .=  " WHERE spitale_users.user = " . $GLOBALS['cs_db_conn']->real_escape_string($_REQUEST['doctor']);
$doc_sql .=  " LIMIT 0,1";

$doc = cs("_cs_grid/get",array('db_sql'=>$doc_sql));
cscheck($doc);
cscheck(array('success'=>$doc['resp']['records'] == 1));

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
$detaliiservicii_valoare = cs('detaliiservicii/get', array("filters"=>array("rules"=>array(
	array("field"=>"doctor","op"=>"eq","data"=>$_REQUEST['doctor']),
	array("field"=>"spital","op"=>"eq","data"=>$_REQUEST['spital']),
	array("field"=>"specializare","op"=>"eq","data"=>$_REQUEST['specializare']),
	array("field"=>"numedetaliu","op"=>"eq","data"=>'valoare'),
))));

$item_specializari_breadcrumb = cs('specializari/breadcrumb',array(
	'catid'=>$_REQUEST['specializare'],
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
		.mycentered{
			max-width: 500px;
			padding: 15px;
			margin: 0 auto;
		}
		.finalizare_imagespital{
			height:35px;
		}
		.finalizare_imageaccount {
			height: 100px;
			width: 75px;
			border-radius: 5px;
		}
		.finalizare_item_pic{
			width: 100px;
			min-height: 100px;
			float: left;
			text-align:center;
		}
		.finalizare_item_info{
			width: calc(100% - 100px);
			width: -moz-calc(100% - 100px);
			width: -webkit-calc(100% - 100px);
			float: right;
			margin-top: 0;
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
<div class="mycentered">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Finalizare programare</h3>
		</div>
		<div class="panel-body">
			<form class="" action="javascript:void(0)" id="finalizare_form" onsubmit="finalizare_click(this)" role="form">
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
					<div class="col-xs-12">
						<div class="finalizare_item_pic">
							<a href="<?php 
								echo cs_url . '/doctori/' 
								. $localitate_uri
								. '/' . $doc['resp']['rows'][0]->spitale_uri 
								. '/' . $doc['resp']['rows'][0]->uri 
							?>">
								<img src="<?php 
									if (intval($doctor_get['image']) > 0){
										echo cs_url."/csapi/images/view/?thumb=0&id=" . $doctor_get['image'];
									}else{
										echo cs_url."/images/default_avatar.jpg";
									}
								?>" class="finalizare_imageaccount" style="" alt="User Image">
							</a>
						</div>
						<div class="finalizare_item_info">
							<?php if ($spitale_get['logo'] > 0){?>
								<a href="<?php 
									echo cs_url . '/doctori/' 
									. $localitate_uri 
									. '/' . $doc['resp']['rows'][0]->spitale_uri 
								?>">
									<img src="<?php echo cs_url."/csapi/images/view/?thumb=0&id=" . $spitale_get['logo'];?>" class="finalizare_imagespital" alt="spital Image">
								</a>
							<?php }?>
							<div>
								<a href="<?php 
									echo cs_url . '/doctori/' 
									. $localitate_uri 
									. '/' . $doc['resp']['rows'][0]->spitale_uri 
								?>">
									<?php echo $spitale_get['nume']?>
								</a>
							</div>
							<div>
								<a href="<?php 
									echo cs_url . '/doctori/' 
									. $localitate_uri
									. '/' . $doc['resp']['rows'][0]->spitale_uri 
									. '/' . $doc['resp']['rows'][0]->uri 
								?>">
									<?php echo $doctor_get['nume']?>
								</a>
							</div>
							<div><?php echo implode('->',$item_specializare_denumire);?></div>
						</div>
					</div>
				</div>
				<?php if ($doc['resp']['rows'][0]->spitale_users_descriere != ''){?>
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
					<div class="col-xs-12 text-center">
						<?php echo htmlspecialchars($doc['resp']['rows'][0]->spitale_users_descriere)?>
					</div>
				</div>
				<?php }?>
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
					<div class="col-xs-12 text-center">
						Data si ora: <?php echo sprintf("%02d", $_REQUEST['d'])
						. '.' . sprintf("%02d", $_REQUEST['m'])
						. '.' . $_REQUEST['y']
						. ' ' . sprintf("%02d", $_REQUEST['h'])
						. ':' . sprintf("%02d", $_REQUEST['i'])
						;
						?>
					</div>
					<?php if ($doc['resp']['rows'][0]->spitale_adresa  != ''){?>
					<div class="col-xs-12 text-center">
						Adresa: <?php echo $doc['resp']['rows'][0]->spitale_adresa ;?>
					</div>
					<?php }?>
					<?php if ($detaliiservicii_valoare != null){?>
					<div class="col-xs-12 text-center">
						Pret: <?php echo $detaliiservicii_valoare['valoaredetaliu'];?>
					</div>
					<?php }?>
				</div>
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
					<div class="col-xs-12">
						<textarea class="form-control" name="observatii" placeholder="OBSERVATII: sugestie, lasati numarul de telefon pentru a putea fi gasit de doctor in eventualiatea confirmarii sau modificarii programarii" rows="2"></textarea>
					</div>
				</div>
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
					<div class="col-xs-12">
						<button type="submit" class="btn btn-primary btn-lg btn-block" >Finalizare</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php 
function footer_ob(){
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<script>
		<?php if (isset($_SESSION['cs_users_id'])){
			echo 'cs_users_id = ' . $_SESSION['cs_users_id'] . ';';
		}?>;
		pageparams = <?php echo json_encode(array(
			'doctor'=>$_REQUEST['doctor'],
			'spital'=>$_REQUEST['spital'],
			'specializare'=>$_REQUEST['specializare'],
			'y'=>$_REQUEST['y'],
			'm'=>$_REQUEST['m'],
			'd'=>$_REQUEST['d'],
			'h'=>$_REQUEST['h'],
			'i'=>$_REQUEST['i'],
		))?>;
		users_login_callback = function(login_callback){
			$('#users_login_htmlmodal').modal('hide');
			if ((typeof(login_callback.success)!='undefined')&&login_callback.success==true){
				cs_users_id = login_callback.resp.id
				finalizare_click()
			}else{
				if ((typeof(login_callback.error)!='undefined')&&(login_callback.error != '')){
					alert(login_callback.error)
				}else{
					alert('ceva nu a mers')
				}
			}
		}
		finalizare_click = function(){
			console.log(pageparams)
			if (typeof(cs_users_id) == 'undefined'){
				cs('users/login_rs',{'callback':'users_login_callback'})
				return
			}
			cs('programari/setappoint',{
				spital:pageparams.spital,
				doctor:pageparams.doctor,
				specializare:pageparams.specializare,
				observatii:$('#finalizare_form textarea[name=observatii]').val(),
				start:pageparams.y
					+ '-' + pageparams.m
					+ '-' + pageparams.d
					+ ' ' + pageparams.h
					+ ':' + pageparams.i
					+ ':00'
			}).then(function(setappoint){
				console.log(setappoint)
				if ((typeof(setappoint.success) != 'undefined') && (setappoint.success == true)){
					alert('Felicitari programare stabilita')
				}else{
					alert('ceva nu a mers...')
					
				}
				window.location = '/'
			})
		}
	</script>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>