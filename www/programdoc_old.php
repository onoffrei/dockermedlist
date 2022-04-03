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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-newspaper-o" aria-hidden="true"></i> Programari pacienti</h3></div>
				<div class="panel-body">
			
	<!--/////////////////////////////////////////////////////////////////////////////  -->
		<?php if ($spitale_users_getlevel['resp'] >= user_level_manager){ ?>
		<select id="doctor_users_managerselectHtml" class="form-control" onchange="document.location.href = setURLParameter({name:'did',value:this.value})">
			<option value="" >Alege doctorul</option>
		<?php 
		
					$doctori_sql = 'SELECT spitale_users.id AS id';
					$doctori_sql .= '	, spitale_users.spital AS spitale_users_spital ';
					$doctori_sql .= '	, users.nume AS users_nume ';
					$doctori_sql .= '	, users.id AS  users_id';
					$doctori_sql .= ' FROM spitale_users';
					$doctori_sql .= ' LEFT JOIN users ON spitale_users.user = users.id';
					$doctori_sql .= ' WHERE spitale_users.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($spital_activ['id']);
					$doctori_sql .= ' AND (SELECT id FROM specializari_user_spitale WHERE (spitale_users.user = specializari_user_spitale.user) AND (spitale_users.spital = specializari_user_spitale.spital) LIMIT 0,1) IS NOT NULL';
					
					$doctori = cs("_cs_grid/get",array('db_sql'=>$doctori_sql));
					cscheck($doctori);

				foreach($doctori['resp']['rows'] as $doctor){	
					
				?>
					<option value="<?php echo $doctor->users_id; ?>" ><?php echo $doctor->users_nume; ?></option>
		<?php  }?>
		</select>
		<script>
			document.getElementById('doctor_users_managerselectHtml').value = "<?php if(isset($_GET['did'])) {echo $_GET['did'];} ?>";
		</script>
		<?php }else{
			echo "Programarile mele:"; echo $users_get['nume']; 
		} 
		
		if(isset($_GET['did']) ){
		?>
	<!-- ////////////////////////////////////////////////////////////////////////////// -->	
		<div>&nbsp;</div>
				<button type="button" class="btn btn-primary"  onclick="window.location = setURLParameter({name:'an',value:<?php echo date("Y", strtotime($prev))?>,url:setURLParameter({name:'luna',value:<?php echo date("m", strtotime($prev))?>})})"><< luna anterioara</button>
				<button type="button" class="btn btn-primary" onclick="window.location = setURLParameter({name:'an',value:<?php echo date("Y", strtotime($final))?>,url:setURLParameter({name:'luna',value:<?php echo date("m", strtotime($final))?>})})">luna urmatoare >></button>
				
				
				<?php 
					require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
					if (isset($_GET['did']) && (intval($_GET['did']) > 0)){
						$monthshow_param['did'] = $_GET['did'];
	
						$isread_sql = 'UPDATE programari';
						$isread_sql .= ' SET isread = 1';
						$isread_sql .= ' WHERE (';
						$isread_sql .= '    programari.doctor = ' . $GLOBALS['cs_db_conn']->real_escape_string($_GET['did']);
						$isread_sql .= '    AND programari.spital = ' . $spital_activ['id'];
						$isread_sql .= '    AND programari.isread = 0 ';
						$isread_sql .= ' )';
						$isread_resp = cs("_cs_grid/get",array('db_sql'=>$isread_sql));						
						cscheck($isread_resp);
					}else{
						$monthshow_param['did'] = '';
					}
					$monthshow = cs('programdoc/sortbymonth', $monthshow_param); 
					cscheck($monthshow); 
					echo $monthshow['resp']['html'];

				?>
				<button type="button" class="btn btn-primary" onclick="window.location = setURLParameter({name:'an',value:<?php echo date("Y", strtotime($prev))?>,url:setURLParameter({name:'luna',value:<?php echo date("m", strtotime($prev))?>})})"><< luna anterioara</button>
				<button type="button" class="btn btn-primary" onclick="window.location = setURLParameter({name:'an',value:<?php echo date("Y", strtotime($final))?>,url:setURLParameter({name:'luna',value:<?php echo date("m", strtotime($final))?>})})">luna urmatoare >></button>
		
		<?php }elseif(!isset($_GET['did']) && $spitale_users_getlevel['resp']==user_level_doctor){
					$_GET['did']=$users_get['id']; ?>
					<div>&nbsp;</div>
				<button type="button" class="btn btn-primary"  onclick="window.location = setURLParameter({name:'an',value:<?php echo date("Y", strtotime($prev))?>,url:setURLParameter({name:'luna',value:<?php echo date("m", strtotime($prev))?>})})"><< luna anterioara</button>
				<button type="button" class="btn btn-primary" onclick="window.location = setURLParameter({name:'an',value:<?php echo date("Y", strtotime($final))?>,url:setURLParameter({name:'luna',value:<?php echo date("m", strtotime($final))?>})})">luna urmatoare >></button>
				
				
				<?php 
					if (isset($_GET['did']) && (intval($_GET['did']) > 0)){
						$monthshow_param['did'] = $_GET['did'];
						$isread_sql = 'UPDATE programari';
						$isread_sql .= ' SET isread = 1';
						$isread_sql .= ' WHERE (';
						$isread_sql .= '    programari.doctor = ' . $GLOBALS['cs_db_conn']->real_escape_string($_GET['did']);
						$isread_sql .= '    AND programari.spital = ' . $spital_activ['id'];
						$isread_sql .= '    AND programari.isread = 0 ';
						$isread_sql .= ' )';
						$isread_resp = cs("_cs_grid/get",array('db_sql'=>$isread_sql));						
						cscheck($isread_resp);
					}else{
						$monthshow_param['did'] = '';
					}
					$monthshow = cs('programdoc/sortbymonth', $monthshow_param); 
					cscheck($monthshow); 
					echo $monthshow['resp']['html'];

				?>
				<button type="button" class="btn btn-primary" onclick="window.location = setURLParameter({name:'an',value:<?php echo date("Y", strtotime($prev))?>,url:setURLParameter({name:'luna',value:<?php echo date("m", strtotime($prev))?>})})"><< luna anterioara</button>
				<button type="button" class="btn btn-primary" onclick="window.location = setURLParameter({name:'an',value:<?php echo date("Y", strtotime($final))?>,url:setURLParameter({name:'luna',value:<?php echo date("m", strtotime($final))?>})})">luna urmatoare >></button>
					
					
		<?php } ?>	
					
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