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

if ($spitale_users_getlevel['resp'] < user_level_manager) { echo 'you are not autorized to this level..'; exit; }

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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-users" aria-hidden="true"></i> Personal unitate medicala</h3></div>
				<div class="panel-body">
					<?php 
					$pag_personal_maxrows = 35;
					$p_page = 1;
					if (isset($_GET['p_page']) && (intval($_GET['p_page'])>0)){$p_page = intval($_GET['p_page']);}
					
					$users_getspitalusers_sql = "SELECT SQL_CALC_FOUND_ROWS ";
					$users_getspitalusers_sql .= "    users.id";
					$users_getspitalusers_sql .= "    ,users.email";
					$users_getspitalusers_sql .= "    ,users.password";
					$users_getspitalusers_sql .= "    , users.date";
					$users_getspitalusers_sql .= "    , users.nume";
					$users_getspitalusers_sql .= "    , users.uri";
					$users_getspitalusers_sql .= "    , users.image";
					$users_getspitalusers_sql .= "    , users.status";
					$users_getspitalusers_sql .= "    , spitale_users.level as spitale_users_level";
					$users_getspitalusers_sql .= '	, (SELECT specializari.denumire FROM specializari_user_spitale ';
					$users_getspitalusers_sql .= '	 	LEFT JOIN specializari ON specializari.id = specializari_user_spitale.specializare';
					$users_getspitalusers_sql .= '	 	WHERE (user = spitale_users.user) AND (spital = spitale_users.spital) ORDER BY specializare ASC LIMIT 0,1) AS specializari_denumire';
					$users_getspitalusers_sql .= " FROM spitale_users";
					$users_getspitalusers_sql .=  " LEFT JOIN users ON users.id = spitale_users.user";
					$users_getspitalusers_sql .=  " WHERE spitale_users.spital = " . $spital_activ['id'];
					$users_getspitalusers_sql .=  " __order__ __limit__";

					$users_getspitalusers = cs("_cs_grid/get",array(
						'db_sql'=>$users_getspitalusers_sql,
						'page'=>$p_page,
						'rows'=>$pag_personal_maxrows,
					));
					cscheck($users_getspitalusers);
					?>
					<input id="personal_upload_file" type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" onchange="personal_upload_onchange(this)" style="display:none">
					<button class="btn btn-success" onclick="cs('users/adaugadoctor_rs',{spital:<?php echo $spital_activ['id']?>})"><i class="fa fa-plus" aria-hidden="true"></i> Adauga</button> 
					<!-- <a class="btn btn-primary" target="_blank" href="<?php echo cs_url_po . '/csapi/users/xls_export?spital='. $spital_activ['id'];?>" ><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Excel</a>  -->
					<!-- <button class="btn btn-danger" onclick="$('#personal_upload_file').click()"><i class="fa fa-upload" aria-hidden="true"></i> Import Excel</button>  -->
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>nume</th>
								<th>specializare</th>
								<th>...</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$nr = 0;
							if (count($users_getspitalusers['resp']['rows']) >0){
								foreach ($users_getspitalusers['resp']['rows'] as $spitaluser){
								$nr ++;
							?>
							<tr>
								<td><?php echo $nr; ?></td>
								<td>
									<?php echo $spitaluser->nume; ?>
									<?php
										if (intval($spitaluser->spitale_users_level) == user_level_manager){
											echo "<b style='color:red'>(MANAGER)</b>";
										}
									?>
								</td>
								<td>
									<?php 
									$btntext = 'Adauga specializare';
									if ($spitaluser->specializari_denumire != null){
										$btntext = $spitaluser->specializari_denumire;
									}
									?>
									<button class="btn btn-default" onclick="cs('specializari/inputedit_rs',{id:<?php echo $spitaluser->id;?>,spital:<?php echo $spital_activ['id']?>})"><i class="fa fa-tags" aria-hidden="true"></i> <?php echo $btntext;?></button>
								</td>
								<td>
									<button class="btn btn-default" onclick="cs('users/modifica_rs',{id:<?php echo $spitaluser->id;?>,spital:<?php echo $spital_activ['id']?>})"><i class="fa fa-user-o" aria-hidden="true"></i></button>
									<?php if (intval($spitaluser->spitale_users_level) == user_level_doctor){?>
									<button class="btn btn-danger" onclick="if (confirm('Sigur doriti sa stergeti?')) cs('users/doctordelete',{did:<?php echo $spitaluser->id;?>,sid:<?php echo $spital_activ['id']?>}).then(function(d){window.location.reload()})"><i class="fa fa-times" aria-hidden="true"></i></button>
									<?php }?>
								</td>
							</tr>
							<?php }?>
							<?php }?>
						</tbody>
					</table>
					<button class="btn btn-success" onclick="cs('users/adaugadoctor_rs',{spital:<?php echo $spital_activ['id']?>})"><i class="fa fa-plus" aria-hidden="true"></i> Adauga</button> 
					<!-- <a class="btn btn-primary" target="_blank" href="<?php echo cs_url_po . '/csapi/users/xls_export?spital='. $spital_activ['id'];?>" ><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Excel</a>  -->
					<!-- <button class="btn btn-danger" onclick="$('#personal_upload_file').click()"><i class="fa fa-upload" aria-hidden="true"></i> Import Excel</button>  -->
				</div>
				<?php 
				//$pag_personal_maxrows = 1;
				if ($users_getspitalusers['resp']['records'] > $pag_personal_maxrows){?>
				<div class="panel-footer">
					<div class="row">
						<div class="col-xs-12 text-center">
							<nav aria-label="...">
								<ul class="pager">
									<li><a href="javascript:void(0)" onclick="pagination_button_click(-1)">Previous</a></li>
									<li><a href="javascript:void(0)" onclick="pagination_button_click(1)">Next</a></li>
								</ul>
							</nav>
							pagina <?php echo $users_getspitalusers['resp']['page']?> din <?php echo $users_getspitalusers['resp']['total']?>
						</div>
					</div>
				</div>
				<script>
					pagination_button_click = function(next){
						var p_page = <?php echo $p_page;?>;
						var totalpages = <?php echo $users_getspitalusers['resp']['total'];?>;
						var nextpage = p_page + next;
						if ((nextpage > 0) && (nextpage <=totalpages) && (nextpage != p_page)){
							window.location.href = setURLParameter({name:'p_page',value:nextpage})
						}
						
					}
				</script>
				<?php }?>
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
		function personal_upload_onchange(finput){
			if (('files' in finput) && (finput.files.length > 0)) {
				cs('users/xls_import',finput.files[0]).then(function(xls_import){
					console.log(xls_import)
					if ((typeof(xls_import.success) != 'undefined') && (xls_import.success == true)){
						alert('Success ' + xls_import.resp.users_parse.addcount + ' rows added')
					}else{
						if ((typeof(xls_import.error) != 'undefined') && (xls_import.error == '')){
							alert('error ' + xls_import.error)
						}else{
							alert('undefined error')
						}
					}
					window.location.reload()
				})
				finput.value = ''
			}
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