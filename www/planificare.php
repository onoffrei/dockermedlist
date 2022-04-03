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

$legenda_grid =  cs('legenda/grid',array("filters"=>array("rules"=>array(
	array("field"=>"spital","op"=>"eq","data"=>$spital_activ['id']),
))));
cscheck($legenda_grid);

function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari medicale online</title>
	<meta name="robots" content="noindex, nofollow" />	
	<style>
		.select-edit{
			-webkit-appearance: none;
			width: 100%;
			height:30px;
			text-align-last: center;
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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-calendar" aria-hidden="true"></i> Planificare personal medical</h3></div>
				<div class="panel-body">
					<?php 
					$planificare_tabel = cs('planificare/tabel',array(
						'spital'=>$spital_activ['id'],
						'y'=>date('Y'),
						'm'=>date('m'),
					));
					cscheck($planificare_tabel);
					if ($planificare_tabel['doctori']['resp']['records'] == 0){
						$link = cs_url . '/' . 'personal';
						if ($_SERVER['QUERY_STRING'] != '') {
							$link .= '?' . $_SERVER['QUERY_STRING'];
						}
						$mesage =  ' <a href="' . $link . '" class="btn btn-info"><i class="fa fa-user" aria-hidden="true"></i></a>';
						$mesage .=  ' <a href="' . $link . '">Alege mai intai personalul din unitatea ta si serviciile pe care acesta le ofera</a>';
						echo $mesage;
					} else if ($legenda_grid['resp']['records'] == 0){
						$link = cs_url . '/' . 'legenda';
						if ($_SERVER['QUERY_STRING'] != '') {
							$link .= '?' . $_SERVER['QUERY_STRING'];
						}
						$mesage =  ' <a href="' . $link . '" class="btn btn-info"><i class="fa fa-grav" aria-hidden="true"></i></a>';
						$mesage .=  ' <a href="' . $link . '">Stabileste intervalele orare mai intai in meniul: Legenda</a>';
						echo $mesage;
					}else{
					?>
					<button class="btn btn-success" onclick="cs('planificare/emptyget_rs',{spital:<?php echo $spital_activ['id']?>})"><i class="fa fa-download" aria-hidden="true"></i> Descarca planificare goala</button> 
					<input id="admin_upload_file" type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" onchange="admin_upload_onchange(this)" style="display:none">
					<button class="btn btn-danger" onclick="$('#admin_upload_file').click()"><i class="fa fa-upload" aria-hidden="true"></i> Uploadeaza planificare</button> 
					
					<button class="btn btn-success" onclick="window.location.href = 'planifauto?p_spital=<?php echo $spital_activ['id']?>'"><i class="fa fa-refresh" aria-hidden="true"></i> Auto Planificare</button>
					<?php 
					$planificare_ymchoose = cs('planificare/ymchoose',array('callback'=>'update_planificare_tabel'));
					cscheck($planificare_ymchoose);
					echo $planificare_ymchoose['resp']['html'];
					?>
					<script>
						update_planificare_tabel = function(m,y){
							console.log(m,y)
							cs('planificare/tabel',{m:m,y:y,spital:<?php echo $spital_activ['id']; ?>}).then(function(d){
								if ((typeof(d.success) != 'undefined') && d.success == true){
									document.querySelector('#planificare_tabel_place').innerHTML = d.resp.html
								}
							})
						}
					</script>
					<div id="planificare_tabel_place">
					<?php
					echo $planificare_tabel['resp']['html'];
					?>
					</div>
					<div>
						<button class="btn btn-default" onclick="admin_planificare_edit_onclick()" id="admin_planificare_edit_btn"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</button> 
						<button class="btn btn-default" onclick="admin_planificare_cancel_onclick()" id="admin_planificare_cancel_btn" style="display:none"><i class="fa fa-times" aria-hidden="true"></i> Anuleaza</button> 
						<button class="btn btn-default" onclick="admin_planificare_save_onclick()" id="admin_planificare_save_btn" style="display:none"><i class="fa fa-floppy-o" aria-hidden="true"></i> Salveaza</button> 
					</div>
					<div style="margin:15px 0 ">
						Legenda:
						<?php foreach ($legenda_grid['resp']['rows'] as $legenda){?>
							<div>
								<?php echo $legenda->nume . ' = ' . $legenda->start . ' - ' . $legenda->stop; ?>
							</div>
						<?php }?>
					</div>
					<?php }?>
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
	<script src="<?php echo cs_url_po;?>/js/admin.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script>
		legenda_grid = <?php echo json_encode($legenda_grid['resp']['rows']);?>;
		spital_activ = <?php echo json_encode($spital_activ);?>;
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