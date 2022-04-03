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
		.cont_push_input_activate, .cont_push_input_deactivate, .cont_push_input_error{display:none;}
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
			$userlevel = $menu_side_ob['menu_get']['spitale_users_getlevel']['spitale_users_get']['level'];
			?>
			</div>
			<div class="panel panel-default detail-right" style="padding:0; margin-top:5px">
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-user-o" aria-hidden="true"></i> Cont</h3></div>
				<div class="panel-body">
	
					
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon" style="width: 10%; border: 1px solid #ccc;">Foto</div>
							<img id="user_image_img" src="<?php 
										if ($users_get['image'] > 0){
											echo cs_url."/csapi/images/view/?thumb=0&id=" . $users_get['image'];
										}else{
											echo cs_url."/images/photodefault.png";
										}
									?>" class="userimageaccount img-thumbnail" alt="User Image">
							<div class="input-group-addon" style="border: 1px solid #ccc;">
								<?php if ($users_get['image'] == 0){?>
										<button type="button" class="btn btn-success btn-block  btn-xs" onclick="contimagine_adauga_click()"><i class="fa fa-plus" aria-hidden="true" alt="Adauga foto"></i>
									<?php }else{?>
										<button type="button" class="btn btn-danger btn-block btn-xs" onclick="contimagine_sterge_click()"><i class="fa fa-trash" aria-hidden="true"></i></button>
									<?php }?>
							</div>
						</div>									
					</div>
					
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon" style="width: 10%">Email</div>
							<input type="text" class="form-control" id="email" value="<?php echo $users_get['email']?>" readonly="readonly" style="background-color: #fff;">
							<div class="input-group-addon">
								<button type="button" class="btn btn-info btn-xs" onclick=""><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
							</div>
						</div>									
					</div>	
					
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon" style="width: 10%">Nume</div>
							<input type="text" class="form-control" id="email" value="<?php echo $users_get['nume']?>" readonly="readonly" style="background-color: #fff;">
							<div class="input-group-addon">
								<button type="button" class="btn btn-info btn-xs" onclick="cs('users/modifica_nume_rs',{id:<?php echo $_SESSION['cs_users_id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
							</div>
						</div>									
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon" style="width: 10%">Password</div>
							<input type="text" class="form-control" id="email" value="******" readonly="readonly" style="background-color: #fff;">
							<div class="input-group-addon">
								<button type="button" class="btn btn-info btn-xs" onclick="cs('users/modifica_password_rs',{id:<?php echo $_SESSION['cs_users_id']?>})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
							</div>
						</div>									
					</div>	
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon" style="width: 10%">Notificari</div>
							<input type="text" class="form-control cont_push_input_error" id="email" value="Nu sunt disponibile notificarile in browserul dumneavoastra, verificati permisiunile site-ului" readonly="readonly" style="background-color: #fff;">
							<div class="input-group-addon">
								<button type="button" class="btn btn-info btn-success cont_push_input_activate" onclick="cont_push_input_change(true)"><i class="fa fa-bell-o" aria-hidden="true"></i> Vreau Notificari</button>
										<button type="button" class="btn btn-info btn-danger cont_push_input_deactivate" onclick="cont_push_input_change(false)"><i class="fa fa-bell-slash-o" aria-hidden="true"></i> Opreste Notificarile</button>
									<!--	<p class="cont_push_input_error">Nu sunt disponibile notificarile in browserul dumneavoastra, verificati permisiunile site-ului</p>-->
							</div>
						</div>									
					</div>	
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon" style="width: 10%">Sterge cont</div>
							<input type="text" class="form-control" id="" readonly="readonly" value="Acest buton va sterge contul dv din aplicatia MedList!" style="background-color: #fff; color: #d9534f; font-weight:bold; text-align: center;">
							<div class="input-group-addon">
								<?php if($userlevel!=4){?>
									<button type="button" class="btn btn-danger btn-xs" onclick="if (confirm('Doriti stergerea contului?')) cs('users/delete').then(function(d){window.location.reload()})"><i class="fa fa-trash" aria-hidden="true"></i></button>
								<?php } ?>
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
	global $postid;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<script src="<?php echo cs_url;?>/js/jquery.Jcrop.min.js"></script>
	<script src="<?php echo cs_url;?>/js/imagecrop.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script>
	//	console.log('test')
		if (typeof(push_init) != 'undefined')
		push_init.then(function(push){
			//console.log('conti push init', push)
			if ((typeof(push) != 'undefined') && (typeof(push.pushSubscription) != 'undefined')){
				$('.cont_push_input_activate').css({display:'none'})
				$('.cont_push_input_deactivate').css({display:'block'})
				$('.cont_push_input_error').css({display:'none'})
			}else if ((typeof(push.serviceWorkerRegistration) != 'undefined') 
				&& (
						(Notification.permission == 'default')
						|| (Notification.permission == 'granted')
					)
				){
				$('.cont_push_input_activate').css({display:'block'})
				$('.cont_push_input_deactivate').css({display:'none'})
				$('.cont_push_input_error').css({display:'none'})
			}else{
				$('.cont_push_input_activate').css({display:'none'})
				$('.cont_push_input_deactivate').css({display:'none'})
				$('.cont_push_input_error').css({display:'block'})
			}
		})
		contimagine_sterge_click = function(){
			if (confirm('Esti sigur ca vrei sa stergi?'))
			cs('users/image_delete').then(function(d){
				console.log(d)
				window.location.reload()
			})
		}
		contimagine_adauga_click = function(){
			$('#contimagine_adauga_file').remove()
			var fileinput = $('<input/>',{
				type:'file',
				style:'display:none',
				id:'contimagine_adauga_file',
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
							imagecrop_parr.aspectRatio = .75
							imagecrop_start({
								strDataURI:strDataURI,
								then:function(blob){
									var form = document.createElement("form");
									form.style.display = "none";
									form.method = "POST";
									form.action = "/csapi/images/add";
									var formData = new FormData(form);
									formData.append("blob", blob,filename);
									$.ajax({
										url: '/csapi/users/image_change',
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
			$('#contimagine_adauga_file').click()
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
		cont_push_input_change = function(newstate){
			if (confirm('Sunteti sigur ca doriti sa ' + (newstate?'porniti':'opriti') + ' notificarile?')){
				if (newstate){
					push_subscribe().then(function(push){
						if (typeof(push.pushSubscription) != 'undefined')
						cs('push/subscribe',push.pushSubscription).then(function(subscribe){
							if((typeof(subscribe.success) != 'undefined') && (subscribe.success == true)){
								console.log('success')
							}else{
								console.error('failed to add subscribtion')
							}
							window.location.reload()
						})
					})
				}else{
					push_unsubscribe().then(function(push){
						cs("push/unsubscribe").then(function(unsubscribe){
							if ((typeof(unsubscribe.success) == 'undefined')||(unsubscribe.success != true)){
								if (typeof(unsubscribe.error) != 'undefined'){
									console.error(unsubscribe.error)
								}else{
									console.error('something went wrong')
								}
							}
							window.location.reload()
						})
					})
				}
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
