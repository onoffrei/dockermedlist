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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-building" aria-hidden="true"></i> Spitale</h3></div>
				<div class="panel-body">
					<?php 
					$pag_maxrows = 20;
					$p_page = 1;
					if (isset($_GET['p_page']) && (intval($_GET['p_page'])>0)){$p_page = intval($_GET['p_page']);}
					$spitale_grid = cs('spitale/grid',array(
						'page'=>$p_page,
						'rows'=>$pag_maxrows,
						'sidx'=>'id',
						'sord'=>'desc',
					));
					cscheck($spitale_grid);					
					?>
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>nume</th>
								<th>uri</th>
								<th>aprobat</th>
								<th>...</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							if (count($spitale_grid['resp']['rows']) >0){
								foreach ($spitale_grid['resp']['rows'] as $spital){
								$localitati_spitale_sql = 'SELECT localitati.id';
								$localitati_spitale_sql .= '	, localitati.denumire';
								$localitati_spitale_sql .= '	, localitati.uri';
								$localitati_spitale_sql .= ' FROM localitati_spitale';
								$localitati_spitale_sql .= ' LEFT JOIN localitati ON localitati_spitale.localitate = localitati.id';
								$localitati_spitale_sql .= ' WHERE localitati_spitale.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($spital->id);
								$localitati_spitale_sql .= ' ORDER BY localitati.id ASC';

								$localitati_spitale = cs("_cs_grid/get",array('db_sql'=>$localitati_spitale_sql));
								cscheck($localitati_spitale);

								$localitate_uri = array();
								foreach($localitati_spitale['resp']['rows'] as $item_sd){
									$localitate_uri[] = $item_sd->uri;
								}
								$localitate_uri  = implode('/',$localitate_uri);

							?>
							<tr>
								<td><?php echo $spital->id; ?></td>
								<td><?php echo $spital->nume; ?></td>
								<td><a href="<?php
										echo cs_url . '/doctori/' 
											. $localitate_uri
											. '/' . $spital->uri 
									?>"><?php echo $spital->uri; ?></a></td>
								<td><?php if (intval($spital->aprobat) == 1){echo 'DA';}else{echo 'NU';}; ?></td>
								<td style="min-width:100px">
									<button class="btn btn-default btn-xs" onclick="cs('spitale/modifica_rs',{id:<?php echo $spital->id;?>})"><i class="fa fa-pencil" aria-hidden="true"></i></button>
									<a class="btn btn-default btn-xs" href="<?php echo cs_url . '/detaliispital?p_spital=' . $spital->id?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
									<button class="btn btn-danger btn-xs" onclick="if (confirm('sunteti sigur ca doriti sa stergeti spitalul?')) cs('spitale/sterge',{id:<?php echo $spital->id;?>}).then(function(){window.location.reload()})"><i class="fa fa-close" aria-hidden="true"></i></button> 
								</td>
							</tr>
							<?php 	}?>
							<?php }?>
						</tbody>
					</table>
				</div>
				<?php 
				//$pag_maxrows = 1;
				if ($spitale_grid['resp']['records'] > $pag_maxrows){?>
				<div class="panel-footer">
					<div class="row">
						<div class="col-xs-12 text-center">
							<nav aria-label="...">
								<ul class="pager">
									<li><a href="javascript:void(0)" onclick="pagination_button_click(-1)">Previous</a></li>
									<li><a href="javascript:void(0)" onclick="pagination_button_click(1)">Next</a></li>
								</ul>
							</nav>
							pagina <?php echo $spitale_grid['resp']['page']?> din <?php echo $spitale_grid['resp']['total']?>
						</div>
					</div>
				</div>
				<script>
					pagination_button_click = function(next){
						var p_page = <?php echo $p_page;?>;
						var totalpages = <?php echo $spitale_grid['resp']['total'];?>;
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
	//	console.log('test')
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