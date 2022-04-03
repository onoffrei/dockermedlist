<?php
require_once("_cs_config.php");

if (!isset($_SESSION['cs_users_id'])){
	header("Location: " . cs_url_scheme . '://' . cs_url_host 
		. '/csapi/users/login_html' 
		. '?urlnext=' . urldecode(cs_url_scheme . '://' . cs_url_host . $_SERVER['REQUEST_URI'])
	);
	exit;
}

$spitale_users_spitalactivinput = cs('spitale_users/spitalactivinput');
cscheck($spitale_users_spitalactivinput);
$spital_activ = $spitale_users_spitalactivinput['spitale_users_spitalactivget']['resp'];

$spitale_users_getlevel = cs('spitale_users/getlevel',array('spital'=>$spital_activ['id']));
cscheck($spitale_users_getlevel);

if ($spitale_users_getlevel['resp'] < user_level_pacient) { echo 'you are not autorized to this level..'; exit; }

$menu_get = cs('menu/get',array('level'=>$spitale_users_getlevel['resp']));
cscheck($menu_get);

$users_get = cs('users/get', array("filters"=>array("rules"=>array(
	array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
))));
cscheck(array('success'=>$users_get!=null));

$localitati_spitale_sql = 'SELECT localitati.id';
$localitati_spitale_sql .= '	, localitati.denumire';
$localitati_spitale_sql .= '	, localitati.uri';
$localitati_spitale_sql .= ' FROM localitati_spitale';
$localitati_spitale_sql .= ' LEFT JOIN localitati ON localitati_spitale.localitate = localitati.id';
$localitati_spitale_sql .= ' WHERE localitati_spitale.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($spital_activ['id']);
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

$url = cs_url . '/doctori/' . $localitate_uri. '/' . $spital_activ['uri'];

?><?php
function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari medicale online</title>
	<meta name="robots" content="noindex, nofollow" />	
	<link href="<?php echo cs_url;?>/css/jquery.Jcrop.min.css" type="text/css" rel="stylesheet"/>
	<style>
		.userimageaccount {
			height: 150px;
			border-radius: 5px;
		}
		.cont_push_input_activate, .cont_push_input_deactivate, .cont_push_input_error{display:none;}
	</style>
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
} 
$GLOBALS['header_ob'] = header_ob();
require_once(cs_path . DIRECTORY_SEPARATOR . 'header.php' );
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="menu-left" style="">
			<?php 
			echo $spitale_users_spitalactivinput['resp']['html'];
			?>
			<?php 
			$menu_side_ob_param = array();
			if (isset($_GET['p_spital'])) $menu_side_ob_param['lnkquery'] = '?p_spital=' . $_GET['p_spital'];
			$menu_side_ob = cs('menu/side_ob',$menu_side_ob_param);
			cscheck($menu_side_ob);
			echo $menu_side_ob['resp']['html'];			?>
			</div>
			<div class="panel panel-default detail-right" style="padding:0; margin-top:5px">
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-wrench" aria-hidden="true"></i> Utile</h3></div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12">						
							<div class="form-group">								
								<div class="col-sm-4 ">								
									<button class="btn btn-success" onclick="window.location.href = 'embedwidget?p_spital=<?php echo $spital_activ['id']?>'"><i class="fa fa-code" aria-hidden="true"></i> Widget Embedded Code</button>
								</div>								
								<div class="col-sm-8" style="text-align: justify;">
									Embed Widget Code este destinat cabinetelor / spitalelor care au deja o pagină web proprie și doresc să ofere pacienților posibilitatea de programare online prin intermediul paginii web proprii. Pentru a instala wiget-ul în pagina dumneavoastră e suficient să copiați codul HTML direct în pagina dumneavoastră. Pentru detalii contactați administratorul.
								</div>
							</div>
						</div>
					</div><br />
					<div class="row">
						<div class="col-xs-12">						
							<div class="form-group">								
								<div class="col-sm-4 ">								
									<form name='valid' action='generateafis.php' method='post' target="_blank" >
										<input type="hidden" value="<?php echo $spital_activ['id']; ?>" name="id" />
										<input type="hidden" value="<?php echo $spital_activ['nume']; ?>" name="nume" />
										<input type="hidden" value="<?php echo $url; ?>" name="url" />
										<input type="hidden" value="<?php echo $spital_activ['logo']; ?>" name="logo" />
										<input type="submit" src="images/add.png" alt="Submit" title="genereaza" style=' vertical-align:middle;' name="trimite" value="Genereaza afis" class="btn btn-success" />					
									</form>
								</div>
								
								<div class="col-sm-8">
								Acest afis il puteti posta la intrarea in unitatea dumneavoastra pentru a inlesni programarea pacientilor dumneavoastra.
								</div>
							</div>
						</div>
					</div><br />
					<div class="row">
						<div class="col-xs-12">						
							<div class="form-group">								
								<div class="col-sm-4 ">								
									<form name='valid' action='generateflyer.php' method='post' target="_blank" >
										<input type="hidden" value="<?php echo $spital_activ['id']; ?>" name="id" />
										<input type="hidden" value="<?php echo $spital_activ['nume']; ?>" name="nume" />
										<input type="hidden" value="<?php echo $url; ?>" name="url" />
										<input type="hidden" value="<?php echo $spital_activ['logo']; ?>" name="logo" />
										<input type="submit" src="images/add.png" alt="Submit" title="genereaza" style=' vertical-align:middle;' name="trimite" value="Genereaza flyer" class="btn btn-success" />										
									</form>
								</div>
								
								<div class="col-sm-8">
								Aceste flyere le puteti oferi pacientilor pentru ca urmatoarea data cand va apela la serviciile dv sa va gaseasca cat mai usor.
								</div>
							</div>
						</div>
					</div><br />
					
								
				</div>
			</div>
		</div>
	</div>
</div>

<?php 
function footer_ob(){
	global $postid;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<script src="<?php echo cs_url;?>/js/jquery.Jcrop.min.js"></script>
	<script src="<?php echo cs_url;?>/js/imagecrop.js"></script>
	
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>
