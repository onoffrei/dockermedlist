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
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	$spitale_get = cs('spitale/get',array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$spital_activ),
	))));
	cscheck(array('success'=>$spitale_get!=null));
	
	if ($spitale_users_getlevel['resp'] == user_level_doctor){
		$doctori_sql = 'SELECT users.id AS id';
		$doctori_sql .= '	, users.nume AS nume ';
		$doctori_sql .= '	, spitale_users.autoplanificare AS autoplanificare ';
		$doctori_sql .= ' FROM spitale_users';
		$doctori_sql .= ' LEFT JOIN users ON spitale_users.user = users.id';
		$doctori_sql .= ' WHERE spitale_users.user = '. $spitale_get['id'];
		$doctori_sql .= ' AND (SELECT id FROM specializari_user_spitale WHERE (spitale_users.user = specializari_user_spitale.user) AND (spitale_users.spital = specializari_user_spitale.spital) LIMIT 0,1) IS NOT NULL';
		
		$doctori = cs("_cs_grid/get",array('db_sql'=>$doctori_sql));
		cscheck($doctori);
	}else{
		$doctori_sql = 'SELECT users.id AS id';
		$doctori_sql .= '	, users.nume AS nume ';
		$doctori_sql .= '	, spitale_users.autoplanificare AS autoplanificare ';
		$doctori_sql .= ' FROM spitale_users';
		$doctori_sql .= ' LEFT JOIN users ON spitale_users.user = users.id';
		$doctori_sql .= ' WHERE spitale_users.spital = '. $spitale_get['id'];
		$doctori_sql .= ' AND (SELECT id FROM specializari_user_spitale WHERE (spitale_users.user = specializari_user_spitale.user) AND (spitale_users.spital = specializari_user_spitale.spital) LIMIT 0,1) IS NOT NULL';
		
		$doctori = cs("_cs_grid/get",array('db_sql'=>$doctori_sql));
		cscheck($doctori);	
	}
	/*
	*/

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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-code" aria-hidden="true"></i> Widget Embedded Code</h3></div>
				<div class="panel-body">
					<div class="alert alert-warning alert-dismissible" role="alert">
						<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
						<span class="sr-only">Infor:</span>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						Embed Widget Code este destinat cabinetelor / spitalelor care au deja o pagină web proprie și doresc să ofere pacienților posibilitatea de programare online prin intermediul paginii web proprii. Pentru a instala wiget-ul în pagina dumneavoastră e suficient să copiați codul HTML direct în pagina dumneavoastră. Pentru detalii contactați administratorul.
					</div>
					<?php
						if ($doctori['resp']['records'] == 0){
							$link = cs_url . '/' . 'personal';
							if ($_SERVER['QUERY_STRING'] != '') {
								$link .= '?' . $_SERVER['QUERY_STRING'];
							}
							$mesage =  ' <a href="' . $link . '" class="btn btn-info"><i class="fa fa-user" aria-hidden="true"></i></a>';
							$mesage .=  ' <a href="' . $link . '">Alege mai intai personalul din unitatea ta si serviciile pe care acesta le ofera</a>';
							echo $mesage;
						}else{
					?>
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>nume</th>
								<th>Embed Code</th>
								<th>...</th>
							</tr>
						</thead>
						<tbody>
						<?php 
						$di = 0;
						foreach($doctori['resp']['rows'] as $doctor){
						?>
							<tr>
								<td><?php echo $di + 1;?></td>
								<td><?php echo $doctor->nume;?></td>
								<td><input type="text" class="form-control" value='<iframe id="melistiframe" src="<?php echo cs_url;?>/programari_embediframe.php?doctor=<?php echo $doctor->id;?>&spital=<?php echo $spitale_get['id'];?>" style="border:none;background-color: transparent;min-width:300px;min-height:360px;width:100%" allowtransparency="true"><p>Your browser does not support iframes.</p></iframe>' id="embed_input_<?php echo $doctor->id;?>"></td>
								<td><button type="button" class="btn btn-success btn-block" onclick="embed_on_copyclick(<?php echo $doctor->id;?>)"><i class="fa fa-files-o" aria-hidden="true"></i> Copiaza text</button></td>
							</tr>
						<?php
							$di++;
						}
						?>
						</tbody>
					</table>
					<?php
					}
					?>
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
	<script>
		function embed_on_copyclick(did){
			var copyText = document.getElementById("embed_input_" + did);
			copyText.select();
			document.execCommand("copy");
			alert("Copied the text");
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