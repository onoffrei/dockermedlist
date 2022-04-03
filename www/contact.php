<?php
require_once("_cs_config.php");
function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari medicale online - Contact</title>
	<meta name="robots" content="index, follow" />	
	<style>
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
			<div class="panel panel-default" style="padding:0; margin-top:5px">
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-envelope-open" aria-hidden="true"></i> Contact</h3></div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 form-horizontal" id="contact_form">
							<?php if (!isset($_SESSION['cs_users_id'])){ ?>
							<div class="form-group">
								<label class="col-sm-4 control-label">Email</label>
								<div class="col-sm-8">
									<input type="email" class="form-control" id="contact_email" name="email" placeholder="Emailul tau" required>
								</div>
							</div>
							<?php }?>
							<div class="form-group">
								<label class="col-sm-4 control-label">Mesaj</label>
								<div class="col-sm-8">
									<textarea class="form-control" id="contact_mesaj" name="mesaj" placeholder="Mesajul tau" required rows="5"></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label"></label>
								<div class="col-sm-8">
									<button type="button" class="btn btn-success" onclick="contact_trimite_click()"><i class="fa fa-paper-plane" aria-hidden="true"></i> Trimite mesaj</button>
								</div>
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
	<script>
		contact_trimite_click = function(){
			if ((typeof(cs_users_id) == 'undefined') && ($('#contact_email').val() == '')){
				alert('Te rugam sa completezi campul "email"')
				return
			}
			if ($('#contact_mesaj').val() == ''){
				alert('Te rugam sa completezi campul "mesaj"')
				return
			}
			cs('mesaje/send',{
				from:1,
				text:'contact form - ' + $('#contact_form input[name=email]').val() + " - " + $('#contact_form textarea[name=mesaj]').val()
			}).then(function(mesaje_send){
				if ((typeof(mesaje_send.success) != 'undefined') && (mesaje_send.success == true)){
					alert('mesaj trimis cu succes')
				}else{
					if ((typeof(mesaje_send.error) != 'undefined') && (mesaje_send.error != '')){
						alert(error)
					}else{
						alert('ceva nu a functionat')
					}
				}
				window.location.href = '/'
			})
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
