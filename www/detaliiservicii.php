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
	<style>
		.cells{
			width: calc(100%);
			width: -moz-calc(100%);
			width: -webkit-calc(100%);			
			margin-top: 10px;
			border: 1px solid #60b0f4;
			padding: 20px 20px 20px 20px;
			border-radius: 5px;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px
			}
		.space {
			padding: 3px;			
		}
		.line{
			border-bottom: 1px dotted #cccccc;
		}
		.den{
			font-weight: bold;
			padding-left:0;
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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-sliders" aria-hidden="true"></i> Detalii servicii</h3></div>
				<div class="panel-body">
				
					<?php if(intval($spital_activ['id'])>0){
						$pag_doc_maxrows = 20;
						$p_page = 1;
						if (isset($_GET['p_page']) && (intval($_GET['p_page'])>0)){$p_page = intval($_GET['p_page']);}
						
						
						$doctori_sql = 'SELECT SQL_CALC_FOUND_ROWS spitale_users.id AS id';
						$doctori_sql .= '	, spitale_users.spital AS spitale_users_spital ';
						$doctori_sql .= '	, users.nume AS users_nume ';
						$doctori_sql .= '	, users.id AS  users_id';
						$doctori_sql .= ' FROM spitale_users';
						$doctori_sql .= ' LEFT JOIN users ON spitale_users.user = users.id';
						$doctori_sql .= ' WHERE spitale_users.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($spital_activ['id']);
						$doctori_sql .= ' AND (SELECT id FROM specializari_user_spitale WHERE (spitale_users.user = specializari_user_spitale.user) AND (spitale_users.spital = specializari_user_spitale.spital) LIMIT 0,1) IS NOT NULL';
						$doctori_sql .= '  __order__ __limit__ ';
						
						$doctori = cs("_cs_grid/get",array(
							'db_sql'=>$doctori_sql,
							'rows'=>$pag_doc_maxrows,
							'page'=>$p_page,
						));
						cscheck($doctori);
						if ($doctori['resp']['records'] == 0){
							$mesage = '<a href="' . cs_url . '/' . 'personal';
							if ($_SERVER['QUERY_STRING'] != '') {
								$mesage .= '?' . $_SERVER['QUERY_STRING'];
							}
							$mesage .=  '">Alege mai intai personalul din unitatea ta si serviciile pe care acesta le ofera</a>';
							echo $mesage;
						}else{						
							foreach($doctori['resp']['rows'] as $doctor){
								$timp = cs('detaliiservicii/get',array("filters"=>array("rules"=>array(
									array("field"=>"doctor","op"=>"eq","data"=>$doctor->users_id),
									array("field"=>"spital","op"=>"eq","data"=>$doctor->spitale_users_spital),
									array("field"=>"numedetaliu","op"=>"eq","data"=>'timp'),
								))));
								$specializari_sql = 'SELECT specializari_main.id AS specializari_id';
								$specializari_sql .= '	, specializari_main.denumire AS specializari_denumire';
								$specializari_sql .= ' FROM specializari_user_spitale';
								$specializari_sql .= ' LEFT JOIN specializari AS specializari_main ON specializari_user_spitale.specializare = specializari_main.id';
								$specializari_sql .= ' WHERE specializari_user_spitale.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($spital_activ['id']);
								$specializari_sql .= ' AND specializari_user_spitale.user = '. $doctor->users_id;
								$specializari_sql .= ' AND NOT EXISTS (';
								$specializari_sql .= ' 	SELECT * ';
								$specializari_sql .= ' 	FROM specializari specializari_inner ';
								$specializari_sql .= ' 	WHERE specializari_main.id = specializari_inner.parent';
								$specializari_sql .= ' )';
								
								$specializari = cs("_cs_grid/get",array('db_sql'=>$specializari_sql));
								cscheck($specializari);
								?>
								<div class="cells">
									<div class="row">							
										<div class="col-xs-12 btn btn-primary">
											<b><?php echo $doctor->users_nume?></b>
										</div>
										<div class="col-xs-12 space line">
											<button class="btn btn-default" onclick="ds_changetime({doctor:<?php echo $doctor->users_id
																								?>,spital:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil" aria-hidden="true"></i></button>
											Timp alocat: <?php if($timp != null){ echo $timp['valoaredetaliu'].' min'; }else{ echo '-';}?>
										</div>
										<div class="col-xs-12 line den">
											Tarife
										</div>
										<?php 
										foreach($specializari['resp']['rows'] as $specializare){
											$valoare = cs('detaliiservicii/get',array("filters"=>array("rules"=>array(
												array("field"=>"doctor","op"=>"eq","data"=>$doctor->users_id),
												array("field"=>"spital","op"=>"eq","data"=>$doctor->spitale_users_spital),
												array("field"=>"specializare","op"=>"eq","data"=>$specializare->specializari_id),
												array("field"=>"numedetaliu","op"=>"eq","data"=>'valoare')
											))));
											$item_specializari_breadcrumb = cs('specializari/breadcrumb',array(
												'catid'=>$specializare->specializari_id
											));
											cscheck($item_specializari_breadcrumb);
											$item_specializare_denumire = array();
											foreach($item_specializari_breadcrumb['resp']['nodearr'] as $item_sd){
												array_unshift($item_specializare_denumire,$item_sd['denumire']);
											}
											
											?>
											<div class="col-xs-12 space">
												<button class="btn btn-default" onclick="ds_changepret({doctor:<?php echo $doctor->users_id;
																								?>,spital:<?php echo $spital_activ['id']; ?>, specializare:<?php echo $specializare->specializari_id; ?>,})"><i class="fa fa-pencil" aria-hidden="true"></i></button>
												<?php echo implode('/',$item_specializare_denumire);?>: <?php if($valoare != null){ echo $valoare['valoaredetaliu'] .' lei'; }else{ echo '-';}?>
											</div>
											<?php
										}
										?>
										
									</div>
								</div>
								<?php
							}
						}
					}
					?>
					<?php if ($doctori['resp']['records'] > $pag_doc_maxrows){?>
					<div class="row">
						<div class="col-xs-12 text-center">
							<nav aria-label="...">
								<ul class="pager">
									<li><a href="javascript:void(0)" onclick="pagination_button_click(-1)">Previous</a></li>
									<li><a href="javascript:void(0)" onclick="pagination_button_click(1)">Next</a></li>
								</ul>
							</nav>
							pagina <?php echo $doctori['resp']['page']?> din <?php echo $doctori['resp']['total']?>
						</div>
					</div>
					<script>
						pagination_button_click = function(next){
							var p_page = <?php echo $p_page;?>;
							var totalpages = <?php echo $doctori['resp']['total'];?>;
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
</div>

<?php 
function footer_ob(){
	global $postid;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<script>
	ds_changetime = function(p_arr){
		console.log(p_arr)
		cs('detaliiservicii/modificatimp_rs',p_arr).then(function(d){console.log(d)})
	}
	ds_changepret = function(p_arr){
		console.log(p_arr)
		cs('detaliiservicii/modificapret_rs',p_arr).then(function(d){console.log(d)})
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