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

$monthshow_param = array(
	"luna"=>date('m'),
	"an"=>date('Y'), 
	'spital'=>$spital_activ['id'],
);


if (isset($_GET['luna']) && (intval($_GET['luna']) > 0)){
	$monthshow_param['luna'] = $_GET['luna'];
}
if (isset($_GET['an']) && (intval($_GET['an']) > 0)){
	$monthshow_param['an'] = $_GET['an'];
}
$time = strtotime($monthshow_param['an']. '-' . $monthshow_param['luna']. '-01' );
$prev = date("Y-m-d", strtotime("-1 month", $time));
$final = date("Y-m-d", strtotime("+1 month", $time));

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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-newspaper-o" aria-hidden="true"></i> Pacienti programati</h3></div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<button type="button" class="btn btn-primary" onclick="window.location = setURLParameter({name:'an',value:<?php echo date("Y", strtotime($prev))?>,url:setURLParameter({name:'luna',value:<?php echo date("m", strtotime($prev))?>})})"><i class='fa fa-arrow-left' aria-hidden='true'></i> Luna anterioara  </button>
							<button type="button" class="btn btn-primary" onclick="window.location = setURLParameter({name:'an',value:<?php echo date("Y", strtotime($final))?>,url:setURLParameter({name:'luna',value:<?php echo date("m", strtotime($final))?>})})"> Luna urmatoare <i class='fa fa-arrow-right' aria-hidden='true'></i></button>
						</div>
					</div>

					<?php 
					$pag_prog_maxrows = 20;
					$p_page = 1;
					if (isset($_GET['p_page']) && (intval($_GET['p_page'])>0)){$p_page = intval($_GET['p_page']);}
					$monthshow_param['page'] = $p_page;
					$monthshow_param['rows'] = $pag_prog_maxrows;
					if ($spitale_users_getlevel['resp'] >= user_level_manager){
					}else{
						$monthshow_param['did'] = $users_get['id'];
					}
					$monthshow = cs('programari/monthshow', $monthshow_param); 
					cscheck($monthshow); 
					echo $monthshow['resp']['html'];
					?>
				</div>
				<?php 
				//$pag_prog_maxrows = 1;
				if ($monthshow['resp']['data']['resp']['records'] > $pag_prog_maxrows){?>
				<div class="panel-footer">
					<div class="row">
						<div class="col-xs-12 text-center">
							<nav aria-label="...">
								<ul class="pager">
									<li><a href="javascript:void(0)" onclick="pagination_button_click(-1)">Previous</a></li>
									<li><a href="javascript:void(0)" onclick="pagination_button_click(1)">Next</a></li>
								</ul>
							</nav>
							pagina <?php echo $monthshow['resp']['data']['resp']['page']?> din <?php echo $monthshow['resp']['data']['resp']['total']?>
						</div>
					</div>
				</div>
				<script>
					pagination_button_click = function(next){
						var p_page = <?php echo $p_page;?>;
						var totalpages = <?php echo $monthshow['resp']['data']['resp']['total'];?>;
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