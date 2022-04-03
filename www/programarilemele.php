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

if ($spitale_users_getlevel['resp'] < user_level_pacient) { echo 'you are not autorized to this level..'; exit; }

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
		.listitem{
			border-bottom: 1px solid gray;
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
			
			$programari_grid = cs('programari/grid',array(
									"filters"=>array("rules"=>array(
										array("field"=>"user","op"=>"eq","data"=>$users_get['id']),
										array("field"=>"start","op"=>"gt","data"=>date('Y-m-d H:m:s'))
									)),
									'sidx'=>'start',
									'sord'=>'asc',
								));
			$programari_grid1 = cs('programari/grid',array("filters"=>array("rules"=>array(
										array("field"=>"user","op"=>"eq","data"=>$users_get['id']),
										array("field"=>"start","op"=>"lt","data"=>date('Y-m-d H:m:s'))
									)),
									'sidx'=>'start',
									'sord'=>'asc',
									));
			?>
			<script>
			function myFunction1() {
				var y = document.getElementById("activ");
				var x = document.getElementById("inactiv");
				if (y.style.display == "none") {
					y.style.display = "block";
					x.style.display = "none";
				}
			}
			function myFunction2() {
				var x = document.getElementById("inactiv");
				var y = document.getElementById("activ");				
				if (x.style.display == "none") {
					x.style.display = "block";
					y.style.display = "none";					
				} 
			}
			</script>
			</div>
			<div class="panel panel-default detail-right" style="padding:0; margin-top:5px">
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-clock-o" aria-hidden="true"></i> Programarile mele</h3></div>
				<div class="panel-body">
			<?php
				if($programari_grid['resp']['records']==0 && $programari_grid1['resp']['records']==0){?>
					<div class="col-xs-12" style="text-align: center;font-weight: bold;">Nu aveti programari!</div></div>
			<?php	}else{ ?>
			
			
				<p>					 
					 <button class="btn btn-primary" id="btn1" value="on" onclick="myFunction1()"><i class="fa fa-hourglass-start
" aria-hidden="true"></i> Programari viitoare</button>
					 <button class="btn btn-primary" id="btn2"  value="on" onclick="myFunction2()" ><i class="fa fa-hourglass-end
" aria-hidden="true"></i> Programari expirate</button>				
					 <button class="btn btn-success" id="btn3"  value="on" onclick="window.location.href = 'doctori';" ><i class="fa fa-plus" aria-hidden="true"></i> Programare noua</button>				
				</p>
				<div id="activ">
				<?php
					if($programari_grid['resp']['records']==0){ ?>
						<div class=""><div class="col-xs-12" style="text-align: center;font-weight: bold; border:1px solid #ccc;padding:15px;">Nu aveti programari in viitor!</div></div>
				<?php }else{ ?>
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>Data</th>
							<th>Denumirea</th>
							<th>Doctor</th>
							<th>Specializarea</th>
							<th style="text-align:center;">Sterge</th>
						</tr>
					</thead>
					<tbody>
								<?php
								
												
								foreach($programari_grid['resp']['rows'] as $programare){ 
								
								//	echo $programare->start; echo" // ";
									$doctor = cs('users/get',array("filters"=>array("rules"=>array(
													array("field"=>"id","op"=>"eq","data"=>$programare->doctor)
											))));
								//	echo $doctor['nume'];echo" // ";
											
									$spital = cs('spitale/get',array("filters"=>array("rules"=>array(
													array("field"=>"id","op"=>"eq","data"=>$programare->spital)
									))));		
								//	echo $spital['nume']; echo" // ";
									$specializare = cs('specializari/get',array("filters"=>array("rules"=>array(
													array("field"=>"id","op"=>"eq","data"=>$programare->specializare)
									))));
									
									echo"<tr>";
									
									if($specializare['parent'] !=0){
										$subspecializare = cs('specializari/get',array("filters"=>array("rules"=>array(
													array("field"=>"id","op"=>"eq","data"=>$specializare['parent'])
											))));
										$s = $subspecializare['denumire']."->".$specializare['denumire'];
											
									}else{
											$s = $specializare['denumire']; 
											
									}
									$print ="<td>"
										. date('d.m.Y H:i', strtotime($programare->start)) 
										."</td><td>". $spital['nume'] 
										."</td><td>". $doctor['nume'] 
										."</td><td>". $s."</td>"								
										."</td>";									
									echo $print;?>
									<td style='text-align:center;'><button class='btn btn-danger' onclick="if (confirm('Sigur doriti sa stergeti?')) cs('programari/sterge',{id:<?php echo $programare->id;?>}).then(function(d){window.location.reload()})"><i class='fa fa-trash' aria-hidden='true'></i></button></td>	
									</tr>
									<?php
								//	echo"</tr>";
								}	
								?>	
								
							<tbody>
						</table>	
				<?php } ?>
					</div>
					
					<div id="inactiv" style="display:none;">
					<?php
					if($programari_grid1['resp']['records']==0){ ?>
						<div class=""><div class="col-xs-12" style="text-align: center;font-weight: bold; border:1px solid #ccc;padding:15px;">Nu aveti programari expirate!</div></div>
					<?php }else{ ?>
					<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>Data</th>
							<th>Denumirea</th>
							<th>Doctor</th>
							<th>Specializarea</th>
						</tr>
					</thead>
					<tbody>
							<?php
								
												
								foreach($programari_grid1['resp']['rows'] as $programare){ 
									// scoatem dr
								//	echo $programare->start; echo" // ";
									$doctor = cs('users/get',array("filters"=>array("rules"=>array(
													array("field"=>"id","op"=>"eq","data"=>$programare->doctor)
											))));
								//	echo $doctor['nume'];
											
									$spital = cs('spitale/get',array("filters"=>array("rules"=>array(
													array("field"=>"id","op"=>"eq","data"=>$programare->spital)
									))));		
								//	echo $spital['nume']; echo" // ";
									$specializare = cs('specializari/get',array("filters"=>array("rules"=>array(
													array("field"=>"id","op"=>"eq","data"=>$programare->specializare)
									))));
									echo"<tr>";
									
									if($specializare['parent'] !=0){
										$subspecializare = cs('specializari/get',array("filters"=>array("rules"=>array(
													array("field"=>"id","op"=>"eq","data"=>$specializare['parent'])
											))));
										$s = $subspecializare['denumire']."->".$specializare['denumire'];
											
									}else{
											$s = $specializare['denumire']; 
											
									}
									$print ="<td>".date('d.m.Y H:i', strtotime($programare->start)) ."</td><td>". $spital['nume'] ."</td><td>". $doctor['nume'] ."</td><td>". $s."</td>";									
									echo $print;
									echo"</tr>";
								}	
							//	cscheck($programari_grid);
							
							
				?>		
							
							<tbody>
						</table>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
				
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