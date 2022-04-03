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

if ($spitale_users_getlevel['resp'] < user_level_doctor) { echo 'you are not autorized to this level..'; exit; }

$menu_get = cs('menu/get',array('level'=>$spitale_users_getlevel['resp']));
cscheck($menu_get);

$users_get = cs('users/get', array("filters"=>array("rules"=>array(
	array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
))));
cscheck(array('success'=>$users_get!=null));


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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-grav" aria-hidden="true"></i> Legenda</h3></div>
				<div class="panel-body">
					<?php 
					$legenda_grid = cs('legenda/grid',array("filters"=>array("rules"=>array(
						array("field"=>"spital","op"=>"eq","data"=>$spital_activ['id']),
					))));
					cscheck($legenda_grid);
					?>
					<button class="btn btn-success" onclick="cs('legenda/adauga_rs',{spital:<?php echo $spital_activ['id'];?>})"><i class="fa fa-plus" aria-hidden="true"></i> Adauga palier orar</button> 
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>denumire palier orar</th>
								<th>inceput</th>
								<th>sfarsit</th>
								<th>...</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$nr_legenda = 1;
							if (count($legenda_grid['resp']['rows']) >0){
								foreach ($legenda_grid['resp']['rows'] as $legenda){
							?>
							
							<tr>
								<td><?php echo $nr_legenda; ?></td>
								<td><?php echo $legenda->nume; ?></td>
								<td><?php echo $legenda->start; ?></td>
								<td><?php echo $legenda->stop; ?></td>
								<td>
									<button class="btn btn-default btn-xs" onclick="cs('legenda/modifica_rs',{id:<?php echo $legenda->id;
										?>,spital:<?php echo $spital_activ['id'];?>})"><i class="fa fa-pencil" aria-hidden="true"></i></button>
									<button class="btn btn-danger btn-xs" onclick="if (confirm('Esti sigur ca vrei sa stergi?')) cs('legenda/sterge',{id:<?php echo $legenda->id;?>}).then(function(){window.location.reload()})"><i class="fa fa-close" aria-hidden="true"></i></button> 
								</td>
							</tr>
							<?php $nr_legenda++;	}?>
							<?php }?>
						</tbody>
					</table>		
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
	<script src="<?php echo cs_url_po;?>/js/admin.js?timestamp=<?php echo cs_updatescript;?>"></script>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>