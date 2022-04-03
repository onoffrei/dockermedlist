<?php
require_once("_cs_config.php");

if (!isset($_SESSION['cs_users_id'])){
	header("Location: " . cs_url_scheme . '://' . cs_url_host 
		. '/csapi/users/login_html' 
		. '?urlnext=' . urldecode(cs_url_scheme . '://' . cs_url_host . $_SERVER['REQUEST_URI'])
	);
	exit;
}
require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');

$spitale_users_spitalactivinput = cs('spitale_users/spitalactivinput');
cscheck($spitale_users_spitalactivinput);
$spital_activ = $spitale_users_spitalactivinput['spitale_users_spitalactivget']['resp'];

$spitale_users_getlevel = cs('spitale_users/getlevel',array('spital'=>$spital_activ['id']));
cscheck($spitale_users_getlevel);

if ($spitale_users_getlevel['resp'] < user_level_manager) { echo 'you are not autorized to this level..'; var_dump($spitale_users_getlevel); exit; }

$menu_get = cs('menu/get',array('level'=>$spitale_users_getlevel['resp']));
cscheck($menu_get);

$users_get = cs('users/get', array("filters"=>array("rules"=>array(
	array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
))));
cscheck(array('success'=>$users_get!=null));

$spitale_get = cs('spitale/get', array("filters"=>array("rules"=>array(
	array("field"=>"id","op"=>"eq","data"=>$spital_activ['id'])
))));
cscheck(array('success'=>$spitale_get!=null));


$localitati_spitale_sql = 'SELECT localitati.id';
$localitati_spitale_sql .= '	, localitati.denumire';
$localitati_spitale_sql .= '	, localitati.uri';
$localitati_spitale_sql .= ' FROM localitati_spitale';
$localitati_spitale_sql .= ' LEFT JOIN localitati ON localitati_spitale.localitate = localitati.id';
$localitati_spitale_sql .= ' WHERE localitati_spitale.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($spital_activ['id']);
$localitati_spitale_sql .= ' ORDER BY localitati.id ASC';

$localitati_spitale = cs("_cs_grid/get",array('db_sql'=>$localitati_spitale_sql));
cscheck($localitati_spitale);


$spitale_images_grid = cs('spitale_images/grid',array("filters"=>array("rules"=>array(
	array("field"=>"spital","op"=>"eq","data"=>$spital_activ['id']),
))));
cscheck($spitale_images_grid);
?><?php
function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari medicale online</title>
	<meta name="robots" content="noindex, nofollow" />	
	<link href="<?php echo cs_url;?>/css/jquery.Jcrop.min.css" type="text/css" rel="stylesheet"/>
	<style>
		.spital_logo {
			width: 250px;
			height: 100px;
		}
		.spitale_images_container{
			width:100%;
			float:left;
			margin-bottom: 10px;
		}
		.spitale_images_container .wrapper.error{
			border: 1px solid red;
			background-color: yellow;
		}
		.spitale_images_container .wrapper.loading{
			border: 1px solid #fff;
		}

		.spitale_images_container .wrapper{
			cursor: move;
			line-height: 80px!important;
			width: 114px;
			height: 84px;
			border: 1px solid green;
			background-position: center;
			text-align: center;
			background-repeat: no-repeat;
			background-color: #fff;
			vertical-align: middle;
			float: left;
			overflow: hidden;
			margin: 10px 10px 0 0;
			position: relative;
		}
		.spitale_images_container .image{
			max-width: 112px;
			max-height: 82px;
			background-size: 100%;
			display: inline-block;
			vertical-align: middle;
			
		}
		.spitale_images_container .options{
			bottom: 0;
			background: rgba(0,0,0,.25);
			line-height: 0;
			height: 35px;
			position: absolute;
			width: 100%;
			transition: ease all .2s;
			z-index: 2;
		}
		.spitale_images_container .option:hover{
			 background: #5da423!important;
			 cursor: auto!important;
		}
		.spitale_images_container .wrapper.loading .option
		, .spitale_images_container .wrapper.loading .error
		, .spitale_images_container .wrapper.error .option
		, .spitale_images_container .wrapper.error .loading
		, .spitale_images_container .wrapper .loading
		, .spitale_images_container .wrapper .error
		{
			display:none !important;
		}
		.spitale_images_container .wrapper .option, .spitale_images_container .wrapper.loading .loading, .spitale_images_container .wrapper.error .error{
			padding: 8px 9px!important;
			background: transparent;
			border: 0;
			border-radius: 0;
			-webkit-border-radius: 0;
			-moz-border-radius: 0;
			width: auto!important;
			display: inline-block!important;
			margin: 0!important;
			color:white;
		}
		.spitale_images_container .option>span{
			font-size: 20px !important;
		}
		div.autocomplete {
		  /*the container must be positioned relative:*/
		  position: relative;
		  display: inline-block;
		}
		div.autocomplete input {
		  border: 1px solid transparent;
		  background-color: #f1f1f1;
		  padding: 10px;
		  font-size: 16px;
		}
		div.autocomplete input[type=text] {
		  background-color: #f1f1f1;
		  width: 100%;
		}
		div.autocomplete input[type=submit] {
		  background-color: DodgerBlue;
		  color: #fff;
		  cursor: pointer;
		}
		.autocomplete-items {
		  position: absolute;
		  border: 1px solid #d4d4d4;
		  border-bottom: none;
		  border-top: none;
		  z-index: 99;
		  /*position the autocomplete items to be the same width as the container:*/
		  top: 100%;
		  left: 0;
		  right: 0;
		  overflow-y: scroll;
		  max-height:300px
		}
		.autocomplete-items div {
		  padding: 10px;
		  cursor: pointer;
		  background-color: #fff; 
		  border-bottom: 1px solid #d4d4d4; 
		}
		.autocomplete-items div:hover {
		  /*when hovering an item:*/
		  background-color: #e9e9e9; 
		}
		.autocomplete-active {
		  /*when navigating through the items using the arrow keys:*/
		  background-color: DodgerBlue !important; 
		  color: #ffffff; 
		}

	</style>
	<link href="<?php echo cs_url;?>/css/bootstrapValidator.min.css" type="text/css" rel="stylesheet"/>
	<link href="<?php echo cs_url;?>/js/quill/quill.snow.css" type="text/css" rel="stylesheet"/>
	<link href="<?php echo cs_url;?>/js/quill/quill.core.css" type="text/css" rel="stylesheet"/>
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
			$userlevel = $menu_side_ob['menu_get']['spitale_users_getlevel']['spitale_users_get']['level'];
			?>
			</div>
			<div class="panel panel-default detail-right" style="padding:0; margin-top:5px">
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Detalii spital</h3></div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<form class="form-horizontal" action="javascript:void(0)" id="spital_form">
									<div class="form-group">									
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Aprobat</div>
											<input type="text" class="form-control" id="nume" value="<?php if ($spitale_get['aprobat'] == 1){echo 'DA';}else{echo 'NU';}?>" readonly="readonly" style="background-color: #fff;">
											<div class="input-group-addon">
												<?php 
													if($userlevel==4){
												?>
													<button type="button" class="btn btn-info btn-xs" onclick="cs('spitale/modifica_aprobat_rs',{id:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
												<?php }else{ ?>
													<button type="button" class="btn btn-info btn-xs" ><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
												<?php } ?>
											</div>
										</div>									
									</div>			

									
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Denumire</div>
											<input type="text" class="form-control" id="denumire" value="<?php echo $spitale_get['nume']?>" readonly="readonly" style="background-color: #fff;">
											<div class="input-group-addon">
												<button type="button" class="btn btn-info btn-xs" onclick="cs('spitale/modifica_nume_rs',{id:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
											</div>
										</div>									
									</div>	

									
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Activ?</div>
											<input type="text" class="form-control" id="activ" value="<?php if ($spitale_get['activman'] == 1){echo 'DA';}else{echo 'NU';}?>" readonly="readonly" style="background-color: #fff;">
											<div class="input-group-addon">
												<?php 
													if($userlevel==4){
												?>
													<button type="button" class="btn btn-info btn-xs" onclick="cs('spitale/modifica_activman_rs',{id:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
												<?php }else{ ?>
													<button type="button" class="btn btn-info btn-xs" ><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
												<?php } ?>
											</div>
										</div>									
									</div>																
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Uri</div>
											<input type="text" class="form-control" id="uri" value='<?php 
												$localitati_uri = '';
												foreach($localitati_spitale['resp']['rows'] as $localitate){
													$localitati_uri .= '/' . $localitate->uri;
												}
												echo ''. cs_url . '/doctori' . $localitati_uri . '/' . $spitale_get['uri'] .'';?>' readonly="readonly" style="background-color: #fff;">
											<div class="input-group-addon">
												<?php 
												if($userlevel==4){
												?>
													<button type="button" class="btn btn-info btn-xs" onclick="cs('spitale/modifica_uri_rs',{id:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
												<?php }else{ ?>
													<button type="button" class="btn btn-info btn-xs" ><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
												<?php } ?>
											</div>
										</div>									
									</div>					
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Localitate</div>
											<input type="text" class="form-control" id="localitate" value="<?php 
												$localitati_denumire = array();
												foreach($localitati_spitale['resp']['rows'] as $localitate){
													$localitati_denumire[] = $localitate->denumire;
												}
												$localitati_denumire = implode(', ',$localitati_denumire);
												echo $localitati_denumire;
											?>" readonly="readonly" style="background-color: #fff;">
											<div class="input-group-addon">
												<button type="button" class="btn btn-info btn-xs" onclick="cs('spitale/modifica_localitate_rs',{id:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
											</div>
										</div>									
									</div>						
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Adresa</div>
											<input type="text" class="form-control" id="adresa" value="<?php echo $spitale_get['adresa']?>" readonly="readonly" style="background-color: #fff;">
											<div class="input-group-addon">
												<button type="button" class="btn btn-info btn-xs" onclick="cs('spitale/modifica_adresa_rs',{id:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
											</div>
										</div>									
									</div>
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Telefon</div>
											<input type="text" class="form-control" id="telefon" value="<?php echo $spitale_get['telefon']?>" readonly="readonly" style="background-color: #fff;">
											<div class="input-group-addon">
												<button type="button" class="btn btn-info btn-xs" onclick="cs('spitale/modifica_telefon_rs',{id:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
											</div>
										</div>									
									</div>				
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Contract CAS</div>
											<input type="text" class="form-control" id="contract" value="<?php if ($spitale_get['contractcas'] == 1){echo 'DA';}else{echo 'NU';}?>" readonly="readonly" style="background-color: #fff;">
											<div class="input-group-addon">
												<button type="button" class="btn btn-info btn-xs" onclick="cs('spitale/modifica_contract_rs',{id:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
											</div>
										</div>									
									</div>								
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Tip contract</div>
												<input type="text" class="form-control" id="tip_contract" value="<?php if ($spitale_get['tipcontract'] != 0){echo $spitale_get['tipcontract'];}else{echo 'Fara contract';}?>" readonly="readonly" style="background-color: #fff;">
												<div class="input-group-addon">
												<?php 
													if($userlevel==4){
												?>
													<button type="button" class="btn btn-info btn-xs" onclick="cs('spitale/modifica_tipcontract_rs',{id:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
													</button>
													<?php } ?>
												</div>
										</div>
									</div>	
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Descriere</div>
											<textarea type="text" class="form-control" id="descriere" value="" readonly="readonly" style="background-color: #fff; text-align: justify;"  rows="10">
												<?php echo ($spitale_get['descriere'])?>
											</textarea>
											<div class="input-group-addon">
												<button type="button" class="btn btn-info btn-xs" onclick="cs('spitale/modifica_descriere_rs',{id:<?php echo $spital_activ['id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
												</button>
											</div>
										</div>
									</div>	
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Logo</div>
												<div style=" border: 1px solid #ccc;">
													<img id="spital_logo" src="<?php 
														if ($spitale_get['logo'] > 0){
															echo cs_url."/csapi/images/view/?thumb=0&id=" . $spitale_get['logo'];
														}else{
															echo cs_url."/images/photodefault.png";
														}
													?>" class="spital_logo img-thumbnail" alt="Spital Logo">
												</div>
												<div class="input-group-addon">
													<?php if ($spitale_get['logo'] == 0){?>
														<button type="button" class="btn btn-success btn-xs" onclick="detaliispital_logo_add_click()"><i class="fa fa-plus" aria-hidden="true"></i></button>
														<?php }else{?>
														<button type="button" class="btn btn-danger btn-xs" onclick="detaliispital_logo_del_click()"><i class="fa fa-trash" aria-hidden="true"></i></button>
														<?php }?>
												</div>
										</div>
									</div>					
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px; border: 1px solid #ccc;">Fotografii</div>			
											<div class="spitale_images_container" style="">
												<div class="sortable">
													<?php foreach($spitale_images_grid['resp']['rows'] as $image_row){ ?> 
													<div class="wrapper" imgid="<?php echo $image_row->image; ?>">
														<img class="image img-thumbnail" src="<?php echo cs_url . '/csapi/images/view/?thumb=0&amp;id=' . $image_row->image; ?>">
														<div class="options">
															<a class="loading"><span class="fa fa-upload"></span> Loading...</a>
															<a class="error" onclick="detaliispital_retry(this,event)"><span class="fa fa-refresh"></span> Error</a>
															<a class="option" onclick="detaliispital_delete(this,event)"><span class="fa fa-trash-o"></span></a>
														</div>
													</div> 
													<?php }	?>
												</div>
											</div>									
											<div class="input-group-addon"  style=" border: 1px solid #ccc;">
												<input id="spitale_images_file" type="file" accept="image/*" multiple onchange="detaliispital_image_onchange(this)" style="display:none">
												<button type="button" class="btn btn-success btn-xs" onclick="$('#spitale_images_file').click()"><i class="fa fa-plus" aria-hidden="true"></i> </button>
											</div>			
										</div>
									</div>	
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon" style="width: 100px;">Sterge unitate</div>
											<input type="text" class="form-control" id="" readonly="readonly" value="Acest buton va sterge unitatea dv medicala din aplicatia MedList!" style="background-color: #fff; color: #d9534f; font-weight:bold; text-align: center;">
												<div class="input-group-addon">
													<button type="button" class="btn btn-danger btn-xs" onclick="detaliispital_sterge(<?php echo $spitale_get['id']?>)"><i class="fa fa-trash" aria-hidden="true"></i></button>
												</div>
													
										</div>									
									</div>	
								
							</form>
						</div>
					</div>	
				</div>
			</div>
		</div>
	</div>
</div>
<?php 
function footer_ob(){
	global $spital_activ;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<script>
		spital_activ = <?php echo json_encode($spital_activ)?>;
		
		detaliispital_sterge = function(sid){
			if (confirm('Esti sigur ca vrei sa stergi?')){
				cs('spitale/sterge',{id:sid}).then(function(d){
					console.log(d)
					window.location.href= '/'
				})
			}
		}
		detaliispital_logo_del_click = function(){
			if (confirm('Esti sigur ca vrei sa stergi?'))
			cs('spitale/logo_delete',{spital_id:spital_activ.id}).then(function(d){
				console.log(d)
				window.location.reload()
			})
		}
		detaliispital_logo_add_click = function(){
			$('#detaliispital_logo_adauga_file').remove()
			var fileinput = $('<input/>',{
				type:'file',
				style:'display:none',
				id:'detaliispital_logo_adauga_file',
				accept:'image/*',
				change:function(finput_event){					
					if (('files' in finput_event.target) 
						&& (finput_event.target.files.length > 0)
						&& (finput_event.target.value != "")
					) {
						var filename = splitPath(finput_event.target.value).filename + splitPath(finput_event.target.value).extension
						var reader = new FileReader();
						reader.onload = function(e){
							//console.log(e.target.result);
							var strDataURI = e.target.result
							imagecrop_parr.aspectRatio = 2.5
							imagecrop_start({
								strDataURI:strDataURI,
								then:function(blob){
									var form = document.createElement("form");
									form.style.display = "none";
									var formData = new FormData(form);
									formData.append('spital_id', spital_activ.id);
									formData.append("blob", blob,filename);
									$.ajax({
										url: '/csapi/spitale/logo_change',
										type: "POST",
										data: formData,
										contentType: false,
										cache: false,
										processData: false,
										success: function (data) {
											console.log(data)
											window.location.reload()
										},
										error: function (data) {
											console.error(data)
										},
										complete: function (data) { }
									});
								}
							})
						}
						reader.readAsDataURL(finput_event.target.files[0]);
						finput_event.target.value = ""
					}
				}
			})
			$(document.body).append(fileinput);
			$('#detaliispital_logo_adauga_file').click()
		}
		splitPath = function(path) {
			var result = path.replace(/\\/g, "/").match(/(.*\/)?(\..*?|.*?)(\.[^.]*?)?(#.*$|\?.*$|$)/);
			return {
				dirname: result[1] || "",
				filename: result[2] || "",
				extension: result[3] || "",
				params: result[4] || ""
			};
		};

	</script>
	<script src="<?php echo cs_url;?>/js/jquery.Jcrop.min.js"></script>
	<script src="<?php echo cs_url;?>/js/imagecrop.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script src="<?php echo cs_url;?>/js/detaliispital.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script src="<?php echo cs_url;?>/js/autocomplete.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script src="<?php echo cs_url;?>/js/bootstrapValidator.min.js"></script>
	<script src="<?php echo cs_url;?>/js/quill/quill.min.js"></script>
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>
