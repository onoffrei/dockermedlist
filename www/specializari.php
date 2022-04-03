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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-tags" aria-hidden="true"></i> Lista specializari</h3></div>
				<div class="panel-body">
				<div class="row">
					<div class="col-xs-12">
						<button class="btn btn-success" onclick="cs('specializari/adauga_rs',{parent:specializari_breadcrumb_parentid,callback:'specializari_update_callback'})"><i class="fa fa-plus" aria-hidden="true"></i> Adauga</button> 
						<button class="btn btn-primary" onclick="cs('specializari/modifica_rs',{id:specializari_breadcrumb_parentid,callback:'specializari_update_callback'})"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</button>
						<button class="btn btn-danger" onclick="if (confirm('sunteti sigur ca doriti sa stergeti?')) cs('specializari/sterge',{id:specializari_breadcrumb_parentid}).then(function(){admin_specializari_onclick(specializari_breadcrumb_parentparent)})"><i class="fa fa-close" aria-hidden="true"></i> Sterge</button> 
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<?php 
							$specializari_breadcrumb = cs('specializari/breadcrumb', array('catid'=>0,'callback'=>'admin_specializari_onclick'));
							cscheck($specializari_breadcrumb);
						?>
						<div class="specializari_breadcrumb">
							<?php echo $specializari_breadcrumb['resp']['html']?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<?php 
							$specializari_list_a = cs('specializari/list_a',array('catid'=>0,'callback'=>'admin_specializari_onclick','element'=>'div'));
							cscheck($specializari_list_a);
						?>
						<div class="list-group">
						<?php echo $specializari_list_a['resp']['html']?>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>
</div>
<?php 
function footer_ob(){
	global $specializari_breadcrumb;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<script src="<?php echo cs_url_po;?>/js/admin.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script>
		specializari_breadcrumb_nodearr = <?php echo json_encode($specializari_breadcrumb['resp']['nodearr'])?>;
		specializari_breadcrumb_parentid = 0;
		specializari_breadcrumb_parentparent = 0;
		specializari_update_callback = function(data){
			//console.log(data)
			admin_specializari_onclick(specializari_breadcrumb_parentid)
		}
		admin_specializari_onclick(0)
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