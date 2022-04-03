<?php 
$parsed = parse_url($_SERVER['REQUEST_URI']);
preg_match_all('/\/mesaje(\/?.*)$/mi', $parsed['path'], $matches, PREG_SET_ORDER, 0);
$partener = array(
	'id'=>0,
	'nume'=>'anonim',
	'image'=>0,
	'uri'=>'',
);
$mesaje_inboxlist_param = array('callback'=>'mesaje_chatuser_onclick');
if ((count($matches) > 0) && count($matches[0]) > 1){
	$mesaje_inboxlist_param['activeuri'] = str_replace('/','',$matches[0][1]);
}
$mesaje_inboxlist = cs('mesaje/inboxlist',$mesaje_inboxlist_param);
cscheck($mesaje_inboxlist);

if ((count($matches) > 0) && count($matches[0]) > 1){
	if (substr( $matches[0][1], 0, 8 ) === "/anonim_"){
		$pk = 'browser';
		$pv = intval(substr( $matches[0][1], 8));
		$mesaje_conversationlist = cs('mesaje/conversationlist',array($pk=>$pv));
		cscheck($mesaje_conversationlist);
	}else{
		$partener_users_get = cs('users/get', array("filters"=>array("rules"=>array(
			array("field"=>"uri","op"=>"eq","data"=>str_replace('/','',$matches[0][1])),
		))));
		if ($partener_users_get != null) {
			$partener = $partener_users_get;
			$pk = 'from';
			$pv = $partener_users_get['id'];	
			$mesaje_conversationlist = cs('mesaje/conversationlist',array($pk=>$pv));
			cscheck($mesaje_conversationlist);			
		}else{
			if ($mesaje_inboxlist['resp']['data']['resp']['records'] > 0){
				$fe = $mesaje_inboxlist['resp']['data']['resp']['rows'][0];
				header("Location: " . cs_url_scheme . '://' . cs_url_host 
					. '/mesaje' 
					. '/' . ($fe->users_uri==null?'anonim_'.$fe->mesaje_browser:$fe->users_uri)
				);
				exit;
			}
		}
	}
}



?>
<?php
function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari medicale online</title>
	<meta name="robots" content="noindex, nofollow" />	
	<style>
		.mesaje_avatar{
			height:30px;
			width:25px;
			border-radius: 50%;
		}
		.mesaje_listitem{
			border-bottom: 1px solid #acd4f6;
			border-radius: 12px;
			margin-top: 5px;
			margin-bottom: 5px;
		}
		.mesaje_listitem.active{
			background-color: #acd4f6 !important;
		}
		.mesaje_listitem.unread{
			background-color:yellow;
		}
		.mesaje_listitem.activeuri{
			background-color:#acd4f6;
		}
		.chat-mesagelist{
			border: 1px solid #60b0f4;
			padding: 10px;
			border-radius: 5px;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			width:100%;
			overflow-y:auto;
			overflow-x:hidden;
		}
		.chat-left {
			width: 190px;
			float: left;
			font-size: smaller;
			overflow-y:auto;
			overflow-x:hidden;
		}
		.chat-right {
			width: calc(100% - 205px);
			width: -moz-calc(100% - 205px);
			width: -webkit-calc(100% - 205px);
			float: right;
			margin-top: 0;
		}
		@media all and (max-width: 768px) {
			.chat-left {
				display: none;
			}
			.chat-right {
				width: 100%;
				float: unset;
				margin-top: 0;
				border: unset;
				border-radius: unset;
			}
		}
		.chat-list-text{
			
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
			<div class="panel panel-default detail-right" style="padding:0">
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-envelope" aria-hidden="true"></i> Mesaje</h3></div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<?php if ($mesaje_inboxlist['resp']['data']['resp']['records'] == 0){?>
								Nu aveti mesaje
							<?php }else{?>
							<div class="chat-left">
								<?php echo $mesaje_inboxlist['resp']['html']?>
							</div>
							<div class="chat-right">
								<div class="row" style="margin:3px">
									<div class="col-xs-3 text-center">
										<img src="<?php 
											if (intval($partener['image']) > 0){
												echo cs_url."/csapi/images/view/?thumb=0&id=" . $partener['image'];
											}else{
												echo cs_url."/images/default_avatar.jpg";
											}
										?>" class="mesaje_avatar" alt="User Image">
									</div>
									<div class="col-xs-9">
										<span><?php echo $partener['nume']?></span>
									</div>
								</div>
								<div class="chat-mesagelist">
									<?php echo $mesaje_conversationlist['resp']['html']?>
								</div>
								<div class="row" style="margin:3px -15px">
									<form action="javascript:void(0)" onsubmit="chat_send_click(this)" id="mesaje_chat_form">
										<input type="hidden" name="<?php echo $pk?>" value="<?php echo $pv?>" >
										<input type="hidden" name="pk" value="<?php echo $pk?>" >
										<input type="hidden" name="pv" value="<?php echo $pv?>" >
										<div class="col-xs-9 text-center">
											<input type="text" name="text" class="form-control" placeholder="..." autocomplete="off">
										</div>
										<div class="col-xs-3">
											<button type="submit" class="btn btn-success btn-block" ><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
										</div>
									</form>
								</div>
							</div>
							<?php }?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php 
function footer_ob(){
	global $partener, $pk, $pv;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<script>
		$('.mesaje_listitem').hover(
			function(){$(this).toggleClass('active');}
		);
		$('.chat-left').css({
			'max-height':($(window).height() - 150) + 'px',
			'height':($(window).height() - 150) + 'px',
		})
		$('.chat-mesagelist').css({
			'max-height':($(window).height() - 220) + 'px',
			'height':($(window).height() - 220) + 'px',
		}).scrollTop($('.chat-mesagelist').prop('scrollHeight') - $('.chat-mesagelist').innerHeight())
		mesaje_chatuser_onclick = function(p_arr){
			//cs('mesaje/conversation_rs',{to:p_arr})
			if (p_arr.uri != '')
			window.location.href = '/mesaje/' + p_arr.uri
			console.log(p_arr)
		}
		chat_send_click = function(form){
			var formdata = new FormData(form)
			var text = $('#mesaje_chat_form input[name=text]').val()
			var pk = $('#mesaje_chat_form input[name=pk]').val()
			var pv = $('#mesaje_chat_form input[name=pv]').val()
			var payload = {}
			var payload1 = {}
			var p_arr = {}
			payload[pk] = pv
			payload1[pk] = pv
			p_arr[pk] = pv
			p_arr.text = text
			payload.text = text
			payload1.then = ['mesaje/inboxlist',{callback:'mesaje_chatuser_onclick'}]
			p_arr.then = ['mesaje/conversationlist',payload]
			
			if (text != ''){
				$('.chat-mesagelist').append(
					$('<div/>',{
						class:'row mesaje_sent',
					}).append(
						$('<div/>',{
							class:'col-xs-12',
						}).append(
							$('<span/>',{
								class:'mesaje_text',
								html:text ,
							})
						)
					)
				).scrollTop($('.chat-mesagelist').prop('scrollHeight') - $('.chat-mesagelist').innerHeight())
				cs('mesaje/send',p_arr).then(function(mesaje_send){
					console.log(mesaje_send)
					if (typeof(mesaje_send.then)!='undefined'){
						var mesaje_conversationlist = mesaje_send.then
						if ((typeof(mesaje_conversationlist.success)!='undefined') && (mesaje_conversationlist.success == true)){
							$('.chat-mesagelist')
								.empty()
								.append(mesaje_conversationlist.resp.html)
								.scrollTop($('.chat-mesagelist').prop('scrollHeight') - $('.chat-mesagelist').innerHeight())
						}
						if (typeof(mesaje_conversationlist.then)!='undefined'){
							var mesaje_inboxlist = mesaje_conversationlist.then
							if ((typeof(mesaje_inboxlist.success)!='undefined') && (mesaje_inboxlist.success == true)){
								$('.chat-left').empty().append(mesaje_inboxlist.resp.html)
							}
						}
					}
				})
				$('#mesaje_chat_form input[name=text]').val('')
			}
		}
		mesaje_received = function(p_arr){
			console.log(p_arr)
			if (
				(
					(typeof(p_arr.from) != 'undefined') 
					&& ('<?php echo $pk?>' == 'from')
					&& (parseInt(p_arr.from) == parseInt('<?php echo $pv?>'))
				)
				||
				(
					(typeof(p_arr.browser) != 'undefined') 
					&& ('<?php echo $pk?>' == 'browser')
					&& (parseInt(p_arr.browser) == parseInt('<?php echo $pv?>'))
				)
			){
				cs('mesaje/conversationlist',{'<?php echo $pk?>':'<?php echo $pv?>',
				then:['mesaje/inboxlist',{callback:'mesaje_chatuser_onclick'}]}).then(function(mesaje_conversationlist){
					if ((typeof(mesaje_conversationlist.success)!='undefined') && (mesaje_conversationlist.success == true)){
						$('.chat-mesagelist')
							.empty()
							.append(mesaje_conversationlist.resp.html)
							.scrollTop($('.chat-mesagelist').prop('scrollHeight') - $('.chat-mesagelist').innerHeight())
					}
					if (typeof(mesaje_conversationlist.then)!='undefined'){
						var mesaje_inboxlist = mesaje_conversationlist.then
						if ((typeof(mesaje_inboxlist.success)!='undefined') && (mesaje_inboxlist.success == true)){
							$('.chat-left').empty().append(mesaje_inboxlist.resp.html)
						}
					}
				})
			}else{
				cs('mesaje/inboxlist',{callback:'mesaje_chatuser_onclick'}).then(function(mesaje_inboxlist){
						if ((typeof(mesaje_inboxlist.success)!='undefined') && (mesaje_inboxlist.success == true)){
							$('.chat-left').empty().append(mesaje_inboxlist.resp.html)
						}
				})
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