<?php
require_once("_cs_config.php");
// if ($_SERVER["SERVER_NAME"] != cs_url_host){header("HTTP/1.0 404 Not Found");echo "not found.\n";die();} //uncomenent  to do
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

if ($spitale_users_getlevel['resp'] < user_level_admin) { echo 'you are not autorized to this level..'; exit; }

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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-map-marker" aria-hidden="true"></i> Lista localitati</h3></div>
				<div class="panel-body">
				<?php 
				$localitati_grid = cs('localitati/grid');
				cscheck($localitati_grid);
				?>
				<button class="btn btn-success" onclick="cs('localitati/adauga_rs')"><i class="fa fa-plus" aria-hidden="true"></i> Adauga</button> 
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>localitatea</th>
							<th>uri</th>
							<th>lat</th>
							<th>long</th>
							<th>...</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						if (count($localitati_grid['resp']['rows']) >0){
							foreach ($localitati_grid['resp']['rows'] as $localitate){
						?>
						<tr>
							<td><?php echo $localitate->id; ?></td>
							<td><?php echo $localitate->denumire; ?></td>
							<td><?php echo $localitate->uri; ?></td>
							<td><?php echo $localitate->lat; ?></td>
							<td><?php echo $localitate->long; ?></td>
							<td>
								<button class="btn btn-default" onclick="cs('localitati/modifica_rs',{id:<?php echo $localitate->id;?>})"><i class="fa fa-pencil" aria-hidden="true"></i></button>
								<button class="btn btn-danger" onclick="if (confirm ('Sunteti sigur ca doriti stergerea?'))cs('localitati/sterge',{id:<?php echo $localitate->id;?>}).then(function(){window.location.reload()})"><i class="fa fa-close" aria-hidden="true"></i></button> 
								
							</td>
						</tr>
						<?php 	}?>
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
	<script>
		console.log('test')
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