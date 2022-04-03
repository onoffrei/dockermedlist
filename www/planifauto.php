<?php
require_once("_cs_config.php");

if (!isset($_SESSION['cs_users_id'])){
	header("Location: " . cs_url_scheme . '://' . cs_url_host 
		. '/csapi/users/login_html' 
		. '?urlnext=' . urldecode(cs_url_scheme . '://' . cs_url_host . $_SERVER['REQUEST_URI'])
	);
	exit;
}

$is_success = true;
if ($is_success){
	$spitale_users_spitalactivinput = cs('spitale_users/spitalactivinput');
	if (isset($spitale_users_spitalactivinput['success']) && ($spitale_users_spitalactivinput['success'] == true)) {
		$spital_activ = $spitale_users_spitalactivinput['spitale_users_spitalactivget']['resp'];
		$is_success = true;
	}	
}
if ($is_success){
	$spitale_users_getlevel = cs('spitale_users/getlevel',array('spital'=>$spital_activ['id']));
	if (isset($spitale_users_getlevel['success']) && ($spitale_users_getlevel['success'] == true)) {
		$is_success = true;
	}		
}

if ($spitale_users_getlevel['resp'] < user_level_doctor) { 
	$page_error = 'you are not autorized to this level..';; 
	$is_success = false;
}

$menu_get = cs('menu/get',array('level'=>$spitale_users_getlevel['resp']));
cscheck($menu_get);

$users_get = cs('users/get', array("filters"=>array("rules"=>array(
	array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
))));
cscheck(array('success'=>$users_get!=null));

$legenda_grid =  cs('legenda/grid',array("filters"=>array("rules"=>array(
	array("field"=>"spital","op"=>"eq","data"=>$spital_activ['id']),
))));
cscheck($legenda_grid);


?>
<?php
function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari medicale online</title>
	<meta name="robots" content="noindex, nofollow" />	
	<style>
	</style>
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
} 
$GLOBALS['header_ob'] = header_ob();
require_once(cs_path . DIRECTORY_SEPARATOR . 'header.php' );
?>
<?php if ($is_success == false){?>
oups.. <?php if (isset($page_error)) echo $page_error; ?>
<?php }?>
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
			echo $menu_side_ob['resp']['html'];
			?>
			</div>
			<div class="panel panel-default detail-right" style="padding:0; margin-top:5px">
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-refresh" aria-hidden="true"></i> Auto Planificare</h3></div>
				<div class="panel-body">
					<div class="alert alert-warning alert-dismissible" role="alert">
						<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
						<span class="sr-only">Infor:</span>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						Serviciul de autoplanificare se poate folosi doar daca programul doctorului este acelasi in fiecare saptamana generandu-se astfel in mod automat programul pentru luna urmatoare. Pentru a activa aceasta optiune bifați checkbox-ul "activ" din dreptul doctorului la care doriți ca planificarea să se repete automat săptămânal.
					</div>
					<div class="row">
						<div class="col-xs-12" id="planifauto_tabel_place">
							<?php
								$planifauto_autotable = cs('planifauto/autotable',array(
									'spital'=>$spital_activ['id'],
								));
								cscheck($planifauto_autotable);
								if ($planifauto_autotable['doctori']['resp']['records'] == 0){
									$link = cs_url . '/' . 'personal';
									if ($_SERVER['QUERY_STRING'] != '') {
										$link .= '?' . $_SERVER['QUERY_STRING'];
									}
									$mesage =  ' <a href="' . $link . '" class="btn btn-info"><i class="fa fa-user" aria-hidden="true"></i></a>';
									$mesage .=  ' <a href="' . $link . '">Alege mai intai personalul din unitatea ta si serviciile pe care acesta le ofera</a>';
									echo $mesage;
								}else{
									echo $planifauto_autotable['resp']['html'];
								}
							?>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<!--
							<button class="btn btn-default" onclick="planifauto_save_onclick()" ><i class="fa fa-floppy-o" aria-hidden="true"></i> Salveaza & </button> 
							<button class="btn btn-default" onclick="planifauto_generate_onclick()" ><i class="fa fa-floppy-o" aria-hidden="true"></i>  & Genereaza</button> 
							-->
							<button class="btn btn-default" onclick="planifauto_sg_onclick()" ><i class="fa fa-floppy-o" aria-hidden="true"></i>Salveaza & Genereaza</button> 
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php 
function footer_ob(){
	global $legenda_grid,$spital_activ;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<script src="<?php echo cs_url_po;?>/js/planifauto.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script>

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