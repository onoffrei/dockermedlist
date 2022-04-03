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

if ($spitale_users_getlevel['resp'] < user_level_pacient) { echo 'you are not autorized to this level..'; exit; }

$menu_get = cs('menu/get',array('level'=>$spitale_users_getlevel['resp']));
cscheck($menu_get);

$users_get = cs('users/get', array("filters"=>array("rules"=>array(
	array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
))));
cscheck(array('success'=>$users_get!=null));
?><?php
function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari medicale online</title>
	<meta name="robots" content="noindex, nofollow" />	
	<link href="<?php echo cs_url;?>/css/jquery.Jcrop.min.css" type="text/css" rel="stylesheet"/>
	<style>
		.userimageaccount {
			height: 150px;
			border-radius: 5px;
		}
		.space{
			padding-top: 5px;
			padding-left: 25px;
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
			<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-usd" aria-hidden="true"></i> Situatie costuri</h3>
			</div>
										
					
				<?php
				//	$nr=0; // progr gratuite  
					$tarif1 = 1.30; //  1-100 1.30
					$tarif2 = 1.15; //101-500 1.15
					$tarif3 = 1.00; //501-1000 1.00
					$tarif4 = 0.85; // >1000 0.85

					$luna = date("m");
					$anul = date("Y");
					$ultimazi = date("t");
					
					$prog_grid = cs('programari/grid',array(
									"filters"=>array("rules"=>array(
										array("field"=>"spital","op"=>"eq","data"=>$spital_activ['id']),
										array("field"=>"start","op"=>"gt","data"=>date(''.$anul.'-'.$luna.'-1 H:m:s')),					
										array("field"=>"start","op"=>"lt","data"=>date(''.$anul.'-'.$luna.'-'.$ultimazi.' H:m:s'))
									))
								));
					cscheck($prog_grid);
					
	
			
//////////////////////////////////////////					
			//		$prog_grid['resp']['records']=40; 
					$curent = $prog_grid['resp']['records'];
				
					$spitale_get = cs('spitale/get', array("filters"=>array("rules"=>array(
						array("field"=>"id","op"=>"eq","data"=>$spital_activ['id'])
					))));					
					$platacontract = $spitale_get['platacontract'];
					if($platacontract!="0")
						{
				?>
					<div class="row">
						<div class="col-xs-12 space">
						<!-- tip contract: 363 - 3 luni gratuit, 6 luni 1/2 din tarif, 3luni+ tarif intreg  -->
						<?php							
							$spitale_get['tipcontract']; // tip contract
							$datastart=strtotime($spitale_get['datastartcontract']); // start
							$dataend3luni = $datastart+60*60*24*91; // 1-3 luni
							$data39luni = $dataend3luni+60*60*24*183; // 3-9 luni
							$today = time();
						//	$today = 1601596800;
						//	$today = 1583107200;
						//	$today = 1599004800;
						//	$today = 1609372800;
							
							$nrzileazi1= round(($today-$datastart)/(60*60*24),0); // nr de zile pana astazi de la semnarea contractului
							$nrzileazi2= round(($today-$dataend3luni)/(60*60*24),0); // nr de zile pana astazi de la expirarea celor 3 luni gratis
							$nrzileazi3= round(($today-$data39luni)/(60*60*24),0); // nr de zile pana astazi de la exp celor 6 luni
														
							$procent1='';
							$procent2='';
							$procent3='';
							$nrzile30 = 91;
							$nrzile90 = 183;
							$zileRamase3L = $nrzile30-$nrzileazi1;
							$zileRamase9L = $nrzile90-$nrzileazi2;
							if($today>=$datastart && $today<=$dataend3luni){
								$info1 = "Au ramas $zileRamase3L de zile!";
								$procent1 = round($nrzileazi1*100/91, 2);
							}elseif($today>$dataend3luni && $today<=$data39luni){
								$info2 = "Au ramas $zileRamase9L de zile!";
								$procent2 = round($nrzileazi2*100/183, 2);
							}elseif($today>$data39luni){
								$info3 = "Sunteti in perioada cu tarif intreg!";
								$procent3 = 100;
							}
								
							if($procent2>$procent1)
								$procent1 = 100;
							if($procent3>$procent2){
								$procent1 = 100;
								$procent2 = 100;
							}
						?>

						  <h2>Situatia actuala</h2>
						  <p>Bara va arata perioada in care va aflati:</p> Perioada de gratuitate de 3 luni
						  <div class="progress">
							<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $procent1;?>%">
							  <?php echo $info1;?>
							</div>
						  </div>
						  Perioada de reducere 50% pentru 6 luni
						  <div class="progress">
							<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $procent2;?>%">
							 <?php echo $info2;?>
							</div>
						  </div>
						  Perioada de plata integrala 
						  <div class="progress">
							<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $procent3;?>%">
							 <?php echo $info3;?>
							</div>
						  </div>

						</div>
					</div>
				<?php } ?>
				<div class="row">
					<div class="col-xs-12 space">
						<form class="form-horizontal" >
						<?php					
						
						
						if($platacontract=="0")
						{
							echo'<div class="col-xs-12 space">
								<h2>Situatia actuala</h2>
								<p>Aplicatia v-a fost oferita gratuit!</p>';
								
								$nr_efectuate1='
								<p>Numar total de programari efectuate: 
									<b>'.$prog_grid['resp']['records'].'</b>
								</p>
							</div>';
							echo $nr_efectuate1;	
						}else{							
							$nr_efectuate2='
							<div class="form-group">
								<label class="col-sm-4 control-label">Numar total de programari efectuate: </label>
								<div class="col-sm-2">
									<p class="form-control-static" style="display: inline-block;">'.$prog_grid['resp']['records'].'</p>
								</div>
							</div>';																
							if($prog_grid['resp']['records']<=100){ 
								$pret = $prog_grid['resp']['records'] * $tarif1 .' euro';
								
							}elseif($prog_grid['resp']['records']>100 && $prog_grid['resp']['records']<=500){
								$pret = $prog_grid['resp']['records'] * $tarif2 .' euro';
								
							}elseif($prog_grid['resp']['records']>500 && $prog_grid['resp']['records']<=1000){
								$pret = $prog_grid['resp']['records'] * $tarif3 .' euro';
								
							}elseif($prog_grid['resp']['records']>1000){
								$pret = $prog_grid['resp']['records'] * $tarif4 .' euro';
							}
			
							echo $nr_efectuate2;	
								?>
								
								<div class="form-group">
									<label class="col-sm-4 control-label">Total de plata</label>
									<div class="col-sm-2">
										<p class="form-control-static" style="display: inline-block;">
											<?php
												echo $pret;
											?>
										</p>
									</div>
								</div>						
					<?php }?>
					</div>
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

	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>
