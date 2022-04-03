<?php
$GLOBALS['mesaje_cols'] = array(
	'id'				=>array('type'=>'int',),
	'from'				=>array('type'=>'int',),
	'to'				=>array('type'=>'int',),
	'text'				=>array('type'=>'text',),
	'date'				=>array('type'=>'datetime',),
	'isread'			=>array('type'=>'int',),
	'browser'			=>array('type'=>'int',),
); 
function mesaje_get($p_arr = array()){
	$ret = null;
	$list = mesaje_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["from"] = intval($list['resp']['rows'][0]->from);
		$ret["to"] = intval($list['resp']['rows'][0]->to);
		$ret["text"] = $list['resp']['rows'][0]->text;
		$ret["date"] = $list['resp']['rows'][0]->date;
		$ret["isread"] = intval($list['resp']['rows'][0]->isread);
		$ret["browser"] = intval($list['resp']['rows'][0]->isread);
	}
	return $ret;
}
function mesaje_grid($p_arr = array()){
	global $mesaje_cols;
	$p_arr['db_cols'] = $mesaje_cols;
	$p_arr['db_table'] = 'mesaje';
	return cs("_cs_grid/get",$p_arr);
}
function mesaje_update($p_arr = array()){
	global $mesaje_cols;
	$p_arr['db_cols'] = $mesaje_cols;
	$p_arr['db_table'] = 'mesaje';
	return cs("_cs_grid/update",$p_arr);
}
function mesaje_create_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	if ((!isset($p_arr['to']))||(!(intval($p_arr['to']) > 0))){$ret['error'] = 'check field - to'; return $ret;}
	$ret['to_users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['to'])
	))));
	if ($ret['to_users_get'] == null) {return $ret;}
	$from_nickname = 'anonymous';
	if (isset($_SESSION['cs_users_id'])){
		$ret['from_users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
		))));
		if ($ret['from_users_get'] == null) {return $ret;}
		$from_nickname = $ret['from_users_get']['nume'];
	}

	//if (!isset($ret['to_users_get']['success']) || ($ret['to_users_get']['success'] != true)){return $ret;}
	ob_start(); 
	?> <div id="mesaje_create_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-envelope" aria-hidden="true"></i> Compune mesaj</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="mesaje_create_modal_action(this)" id="mesaje_create_modal_form" >
						<div class="row">
							<div class="col-xs-12 form-inline">
								<div class="form-group">
									<label>Catre</label>
									<input type="text" readonly class="form-control" placeholder="Catre" value='<?php echo $ret['to_users_get']['nume'];?>'>
									<input type="hidden" name="from" value='<?php echo $ret['to_users_get']['id'];?>'>
								</div>
								<div class="form-group">
									<label>De la</label>
									<input type="text" readonly class="form-control" placeholder="de la" value='<?php echo $from_nickname;?>'>
								</div>
							</div>
						</div>
						<div class="row" style="margin-top:20px">
							<div class="col-xs-12 " style="border-top: 1px solid #ddd;">
								<div class="form-group">
									<label>Mesaj</label>
									<textarea name="text" placeholder="mesaj" rows="4" class="form-control" ></textarea>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<button type="submit" class="btn btn-success btn-block">Trimite</button>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal --> <script>
		$("div.modal-backdrop.fade.in").remove()
		$("#mesaje_create_modal").modal('show')
		mesaje_create_modal_action = function(form){
			cs('mesaje/send',new FormData(form)).then(function(d){
				//console.log(d)
				$("#mesaje_create_modal").modal('hide')
				if (typeof(d.success) == 'undefined' || d.success != true) {
					alert('trimitere nereusita')
					return
				}
				//alert('trimitere reusita')
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function mesaje_conversation_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	if(!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'login first'; return $ret;}
	if (!isset($p_arr['to'])){$ret['error'] = 'check field - to'; return $ret;}

	$ret['partener'] = array(
		'id'=>0,
		'nume'=>'anonim',
		'image'=>0,
	);
	$ret['part_users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['to'])
	))));
	if ($ret['part_users_get'] != null) {$ret['partener'] = $ret['part_users_get'];}
	
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$ret['conversation_sql'] = 'SELECT mesaje.id AS mesaje_id';
	$ret['conversation_sql'] .= '  , mesaje.date AS mesaje_date ';
	$ret['conversation_sql'] .= '  , mesaje.text AS mesaje_text ';
	$ret['conversation_sql'] .= '  , mesaje.from AS mesaje_from ';
	$ret['conversation_sql'] .= ' FROM mesaje';
	$ret['conversation_sql'] .= ' LEFT JOIN users ON mesaje.from = users.id';
	$ret['conversation_sql'] .= ' WHERE (';
	$ret['conversation_sql'] .= '    mesaje.to = '. $_SESSION['cs_users_id'];
	$ret['conversation_sql'] .= '    AND mesaje.from = '. $GLOBALS['cs_db_conn']->real_escape_string($p_arr['to']);
	$ret['conversation_sql'] .= ' ) OR (';
	$ret['conversation_sql'] .= '    mesaje.to = '. $GLOBALS['cs_db_conn']->real_escape_string($p_arr['to']);
	$ret['conversation_sql'] .= '    AND mesaje.from = '. $_SESSION['cs_users_id'];
	$ret['conversation_sql'] .= ' )';
	$ret['conversation_sql'] .= ' ORDER BY mesaje.date DESC ';
	$ret['resp'] = cs("_cs_grid/get",array('db_sql'=>$ret['conversation_sql']));
	if (!isset($ret['resp']['success']) || $ret['resp']['success'] == false) {return $ret;}
	
	ob_start(); 
	?> <style>
		.mesaje_text{
			max-width:80%;
			padding: 3px 10px;
			border-radius: 5px;
			margin: 2px;
		}
		.mesaje_date{
			font-size: smaller;
		}
		.mesaje_received .mesaje_text{
			background-color:lightblue;
			float: left;
			text-align: left;
		}
		.mesaje_sent .mesaje_text{
			background-color:cornflowerblue;
			color:white;
			float: right;
			text-align: right;
		}
		.mesaje_received .mesaje_date{
			float: left;
		}
		.mesaje_sent .mesaje_date{
			float: right;
		}
	</style>
	<div id="mesaje_conversation_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">
						<i class="fa fa-envelope" aria-hidden="true"></i> 
						Converatie: 
						<img src="<?php 
							if (intval($ret['partener']['image']) > 0){
								echo cs_url."/csapi/images/view/?thumb=0&id=" . $ret['partener']['image'];
							}else{
								echo cs_url."/images/default_avatar.jpg";
							}
						?>" class="mesaje_avatar" alt="User Image">
						<span><?php echo $ret['partener']['nume']?></span>
					</h4>
				</div>
				<div class="modal-body">
					<?php foreach($ret['resp']['resp']['rows'] as $convitem){?>
						<div class="row <?php if (intval($convitem->mesaje_from) == $_SESSION['cs_users_id']){echo 'mesaje_sent';}else{echo 'mesaje_received';}?>">
							<div class="col-xs-12">
								<span class="mesaje_date">
									<?php 
										echo date('d',strtotime($convitem->mesaje_date))
											. '.' . date('m',strtotime($convitem->mesaje_date))
											. '.' . date('Y',strtotime($convitem->mesaje_date))
											. ' ' . date('H',strtotime($convitem->mesaje_date))
											. ':' . date('i',strtotime($convitem->mesaje_date))
											. ':' . date('s',strtotime($convitem->mesaje_date))
										;
									?>
								</span>
							</div>
							<div class="col-xs-12">
								<span class="mesaje_text">
								<?php echo htmlentities($convitem->mesaje_text)?>
								</span>
							</div>
						</div>
					<?php }?>
				</div>
				<div class="modal-footer">
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal --> <script>
		$("div.modal-backdrop.fade.in").remove()
		$("#mesaje_conversation_modal").modal('show')
		mesaje_conversation_modal_action = function(form){
			cs('mesaje/send',new FormData(form)).then(function(d){
				//console.log(d)
				$("#mesaje_conversation_modal").modal('hide')
				if (typeof(d.success) == 'undefined' || d.success != true) {
					alert('trimitere nereusita')
					return
				}
				//alert('trimitere reusita')
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function mesaje_send($p_arr){
	$ret = array('success'=>false,'error'=>'');
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	if (!isset($p_arr['text'])){ $ret['error'] = 'check param - text'; return $ret;}
	if (!isset($p_arr['date'])){ $p_arr['date'] = date('Y-m-d H:i:s');}
	if ((!isset($p_arr['from']))&&(!isset($p_arr['browser']))){ $ret['error'] = 'check param - from/browser'; return $ret;}
	if (isset($_SESSION['cs_users_id'])){ 
		$ret['mek'] = 'from';
		$ret['mev'] = $_SESSION['cs_users_id'];
	}else{
		$ret['mek'] = 'browser';
		$ret['mev'] = $_SESSION['browser_id'];
	}
	if (isset($p_arr['from'])){ 
		$ret['pk'] = 'to';/////
		$ret['pv'] = $p_arr['from'];
	}else{
		$ret['pk'] = 'browser';
		$ret['pv'] = $p_arr['browser'];
	}
	if ($ret['pk'] == 'to'){
		$ret['push_send_user'] = cs('push/send_user',array(
			'user'=>$ret['pv'],
			'message'=>array(
				'title'=>'Medlist mesage received',
				'message'=>'You have new message'
			),
		));
	}
	
	if (isset($_SESSION['firebase_activeid']) && ($_SESSION['firebase_activeid'] != '')) {
		$ret['action_dispatch_param'] = array(
			'action'=>array(
				'name'=>'mesaje_received',
				'param'=>array(
					$ret['mek']=>$ret['mev'],
					$ret['pk']=>$ret['pv'],
				),
			),
		);
		if ($ret['pk'] == 'to'){
			$ret['action_dispatch_param']['userid'] =  $ret['pv'];
			$ret['action_dispatch_param']['path'] =  'users';
		}else{
			$ret['action_dispatch_param']['browserid'] =  $ret['pv'];
			$ret['action_dispatch_param']['path'] =  'browser';
		}
		$ret['firebase_action_dispatch'] = cs('firebase/action_dispatch',$ret['action_dispatch_param']);
		//fail silently
	}
	
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$ret['mesaje_update_param'] = array(
		'oper'=>'add',
		'id'=>'null',
		'text'=>$p_arr['text'],
		'date'=>date('Y-m-d H:i:s'),
		$ret['mek']=>$ret['mev'],
		$ret['pk']=>$ret['pv'],
	);
	$ret['mesaje_update'] = mesaje_update($ret['mesaje_update_param']);
	if (!isset($ret['mesaje_update']['success'])||($ret['mesaje_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function mesaje_inboxlist($p_arr){
	$ret = array('success'=>false,'resp'=>array('html'=>''));
	if (!isset($p_arr['callback'])) $p_arr['callback'] = 'javascript:void';
	if (!isset($p_arr['activeuri'])) $p_arr['activeuri'] = '';
	if(!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'login first'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$ret['inbox_sql'] = 'SELECT mesaje_tmain.id AS mesaje_id';
	$ret['inbox_sql'] .= '  , mesaje_tmain.from AS mesaje_from ';
	$ret['inbox_sql'] .= '  , mesaje_tmain.browser AS mesaje_browser ';
	$ret['inbox_sql'] .= '  , (SELECT mesaje.isread FROM mesaje WHERE ((mesaje.to = mesaje_tmain.to) AND (mesaje.from = mesaje_tmain.from)AND (mesaje.browser = mesaje_tmain.browser)) ORDER BY mesaje.date DESC LIMIT 0,1) AS mesaje_isread ';
	$ret['inbox_sql'] .= '  , (SELECT mesaje.text FROM mesaje WHERE ((mesaje.to = mesaje_tmain.to) AND (mesaje.from = mesaje_tmain.from)AND (mesaje.browser = mesaje_tmain.browser)) ORDER BY mesaje.date DESC LIMIT 0,1) AS mesaje_text ';
	$ret['inbox_sql'] .= '  , (SELECT mesaje.date FROM mesaje WHERE ((mesaje.to = mesaje_tmain.to) AND (mesaje.from = mesaje_tmain.from)AND (mesaje.browser = mesaje_tmain.browser)) ORDER BY mesaje.date DESC LIMIT 0,1) AS mesaje_date ';
	$ret['inbox_sql'] .= '  , users.id AS users_id ';
	$ret['inbox_sql'] .= '  , users.nume AS users_nume ';
	$ret['inbox_sql'] .= '  , users.uri AS users_uri ';
	$ret['inbox_sql'] .= '  , users.image AS users_image ';
	$ret['inbox_sql'] .= '  , users.status AS users_status ';
	$ret['inbox_sql'] .= ' FROM mesaje mesaje_tmain';
	$ret['inbox_sql'] .= ' LEFT JOIN users ON mesaje_tmain.from = users.id';
	$ret['inbox_sql'] .= ' WHERE mesaje_tmain.to = '. $_SESSION['cs_users_id'];
	$ret['inbox_sql'] .= ' GROUP BY mesaje_tmain.from, mesaje_tmain.browser ';
	$ret['inbox_sql'] .= ' ORDER BY mesaje_date DESC ';
	$ret['resp']['data'] = cs("_cs_grid/get",array('db_sql'=>$ret['inbox_sql']));
	if (!isset($ret['resp']['data']['success']) || $ret['resp']['data']['success'] == false) {return $ret;}
	if ($ret['resp']['data']['resp']['records'] != 0){
		ob_start(); 
		foreach($ret['resp']['data']['resp']['rows'] as $inboxitem){?>
			<div class="row mesaje_listitem <?php 
					$uri = $inboxitem->users_uri==null?'anonim_'.$inboxitem->mesaje_browser:$inboxitem->users_uri;
					if (intval($inboxitem->mesaje_isread)==0) echo ' unread ';
					if ($uri == $p_arr['activeuri']) echo ' activeuri ';
					?>" onclick="<?php echo 
					$p_arr['callback'].'({'
					. '\'uri\':\'' . $uri
					. '\'})'?>" >
				<div class="col-xs-2 text-center">
				<div class="usersstatus <?php if (intval($inboxitem->users_status) == 1){echo 'online';}else{echo 'offline';}?>" users="<?php echo $inboxitem->users_id?>">
					<div class="usersstatus_circle_online"></div>
					<div class="usersstatus_circle_offline"></div>
						<img src="<?php 
							if (intval($inboxitem->users_image) > 0){
								echo cs_url."/csapi/images/view/?thumb=0&id=" . $inboxitem->users_image;
							}else{
								echo cs_url."/images/default_avatar.jpg";
							}
						?>" class="mesaje_avatar" alt="User Image">
					</div>
				</div>
				<div class="col-xs-10 ">
					<div class="row">
						<div class="col-xs-6 chat-list-text">
							<?php 
								if (intval($inboxitem->users_id)>0){
									echo $inboxitem->users_nume;
								}else{
									echo 'anonim';
								}
							?>
						</div>
						<div class="col-xs-6 text-center">
							<?php 
							$ret['mesaje_date'] = cs('util/dateshort',array('date'=>$inboxitem->mesaje_date));
							if (isset($ret['mesaje_date']['success']) && ($ret['mesaje_date']['success'] == true))
							echo $ret['mesaje_date']['resp'];

							?>
						</div>
						<div class="col-xs-12 ">
							<?php 
								echo $inboxitem->mesaje_text;
							?>
						</div>
					</div>
				</div>
			</div>
		<?php }
		$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	}
	
	$ret['success'] = true;
	return $ret;
}
function mesaje_countnew($p_arr){
	$ret = array('success'=>false,'resp'=>0);
	if(!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'login first'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$ret['inbox_sql'] = 'SELECT count(*) AS count';
	$ret['inbox_sql'] .= ' FROM mesaje ';
	$ret['inbox_sql'] .= ' WHERE mesaje.to = '. $_SESSION['cs_users_id'];
	$ret['inbox_sql'] .= ' AND mesaje.isread = 0 ';
	$ret['inbox_sql'] .= ' LIMIT 0,1 ';
	$ret['data'] = cs("_cs_grid/get",array('db_sql'=>$ret['inbox_sql']));
	if (!isset($ret['data']['success']) || $ret['data']['success'] == false) {return $ret;}
	if ($ret['data']['resp']['records'] != 0){
		$ret['resp'] = intval($ret['data']['resp']['rows'][0]->count);
	}	
	$ret['success'] = true;
	return $ret;
}
function mesaje_conversationlist($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>''));
	$ret['p_arr'] = $p_arr;
	
	if (isset($_SESSION['cs_users_id'])){ 
		$ret['mek'] = 'from';
		$ret['mev'] = $_SESSION['cs_users_id'];
		$mq = array('to','from');
	}else{
		$ret['mek'] = 'browser';
		$ret['mev'] = $_SESSION['browser_id'];
		$mq = array('browser','browser');
	}
	if (isset($p_arr['from'])){ 
		$ret['pk'] = 'from';
		$ret['pv'] = $p_arr['from'];
		$pq = array('from','to');
	}else{
		$ret['pk'] = 'browser';
		$ret['pv'] = $p_arr['browser'];
		$pq = array('browser','browser');
	}
	
	if (!isset($ret['pk'])){$ret['error'] = 'check field - from/browser'; return $ret;}

	
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	$ret['partener'] = array(
		'id'=>0,
		'nume'=>'anonim',
		'image'=>0,
		'uri'=>'',
	);
	if ($ret['pk'] == 'from'){
		$ret['part_users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$ret['pv'])
		))));
		if ($ret['part_users_get'] != null) {$ret['partener'] = $ret['part_users_get'];}
	}
	
	$ret['isread_sql'] = 'UPDATE mesaje';
	$ret['isread_sql'] .= ' SET isread = 1';
	$ret['isread_sql'] .= ' WHERE (';
	$ret['isread_sql'] .= '    mesaje.' . $mq[0] . ' = '. $ret['mev'];
	$ret['isread_sql'] .= '    AND mesaje.' . $pq[0] . ' = '. $GLOBALS['cs_db_conn']->real_escape_string($ret['pv']);
	$ret['isread_sql'] .= '    AND mesaje.isread = 0 ';
	$ret['isread_sql'] .= ' )';
	$ret['isread_resp'] = cs("_cs_grid/get",array('db_sql'=>$ret['isread_sql']));
	if (!isset($ret['isread_resp']['success']) || $ret['isread_resp']['success'] == false) {return $ret;}
	
	$ret['conversation_sql'] = 'SELECT mesaje.id AS mesaje_id';
	$ret['conversation_sql'] .= '  , mesaje.date AS mesaje_date ';
	$ret['conversation_sql'] .= '  , mesaje.text AS mesaje_text ';
	$ret['conversation_sql'] .= '  , mesaje.from AS mesaje_from ';
	$ret['conversation_sql'] .= ' FROM mesaje';
	$ret['conversation_sql'] .= ' LEFT JOIN users ON mesaje.from = users.id';
	$ret['conversation_sql'] .= ' WHERE (';
	$ret['conversation_sql'] .= '    mesaje.' . $mq[0] . ' = '. $ret['mev'];
	$ret['conversation_sql'] .= '    AND mesaje.' . $pq[0] . ' = '. $GLOBALS['cs_db_conn']->real_escape_string($ret['pv']);
	$ret['conversation_sql'] .= ' ) OR (';
	$ret['conversation_sql'] .= '    mesaje.' . $pq[1] . ' = '. $GLOBALS['cs_db_conn']->real_escape_string($ret['pv']);
	$ret['conversation_sql'] .= '    AND mesaje.' . $mq[1] . ' = '. $ret['mev'];
	$ret['conversation_sql'] .= ' )';
	$ret['conversation_sql'] .= ' ORDER BY mesaje.date DESC ';
	$ret['conversation_sql'] .= ' LIMIT 0, 30 ';
	$ret['resp']['data'] = cs("_cs_grid/get",array('db_sql'=>$ret['conversation_sql']));
	if (!isset($ret['resp']['data']['success']) || $ret['resp']['data']['success'] == false) {return $ret;}
	if ($ret['resp']['data']['resp']['records'] != 0){
		$ret['resp']['data']['resp']['rows'] = array_reverse($ret['resp']['data']['resp']['rows']);
		ob_start(); 
		?> <style>
			.mesaje_text{
				max-width:80%;
				padding: 3px 10px;
				border-radius: 5px;
				margin: 2px;
			}
			.mesaje_date{
				color:gray;
				font-size: smaller;
			}
			.mesaje_received .mesaje_text{
				background-color:lightblue;
				float: left;
				text-align: left;
			}
			.mesaje_sent .mesaje_text{
				background-color:cornflowerblue;
				color:white;
				float: right;
				text-align: right;
			}
			.mesaje_received .mesaje_date{
				float: left;
			}
			.mesaje_sent .mesaje_date{
				float: right;
			}
		</style>

		<?php foreach($ret['resp']['data']['resp']['rows'] as $convitem){?>
			<div class="row <?php if (intval($convitem->mesaje_from) == $_SESSION['cs_users_id']){echo 'mesaje_sent';}else{echo 'mesaje_received';}?>">
				<div class="col-xs-12">
					<span class="mesaje_date">
						<?php 
							$ret['mesaje_date'] = cs('util/dateshort',array('date'=>$convitem->mesaje_date));
							if (isset($ret['mesaje_date']['success']) && ($ret['mesaje_date']['success'] == true))
							echo $ret['mesaje_date']['resp'];
						?>
					</span>
				</div>
				<div class="col-xs-12">
					<span class="mesaje_text">
					<?php echo htmlentities($convitem->mesaje_text)?>
					</span>
				</div>
			</div>
		<?php }
		$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	}
	$ret['success'] = true;
	return $ret;
}
function mesaje_minichat_frame($p_arr){
	$ret = array('success'=>false,'resp'=>array('html'=>''));
	ob_start(); ?>
		<div id="minichat_frame" class="minichat_frame" style="">
		<style>
		</style>
		</div>
	<?php
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function mesaje_minichat_frame1($p_arr){
	$ret = array('success'=>false,'resp'=>array('html'=>''));
	$ret['partener'] = array(
		'id'=>0,
		'nume'=>'anonim',
		'image'=>0,
		'uri'=>'',
	);
	if (isset($p_arr['payload']) && isset($p_arr['payload']['from'])){
		$ret['part_users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$p_arr['payload']['from'])
		))));
		if ($ret['part_users_get'] != null) {$ret['partener'] = $ret['part_users_get'];}
	}
	if (isset($_SESSION['cs_users_id'])){ 
		$ret['mek'] = 'from';
		$ret['mev'] = $_SESSION['cs_users_id'];
	}else{
		$ret['mek'] = 'browser';
		$ret['mev'] = $_SESSION['browser_id'];
	}
	if (isset($p_arr['payload']['from'])){ 
		$ret['pk'] = 'from';
		$ret['pv'] = $p_arr['payload']['from'];
	}else{
		$ret['pk'] = 'browser';
		$ret['pv'] = $p_arr['payload']['browser'];
	}
	ob_start(); ?>
		<div id="minichat_frame1" class="minichat_frame1" style="" <?php
			if (isset($p_arr['payload']['browser'])){ echo 'browser="' . $p_arr['payload']['browser'] . '"';}
			if (isset($p_arr['payload']['from'])){ echo 'from="' . $p_arr['payload']['from'] . '"';}
		?> >
			<div class="row" style="margin:3px">
				<div class="col-xs-2 text-center" onclick="minichat_chatrestore_click({pk:'<?php echo $ret['pk']?>',pv:<?php echo $ret['pv']?>})">
					<img src="<?php 
						if (intval($ret['partener']['image']) > 0){
							echo cs_url."/csapi/images/view/?thumb=0&id=" . $ret['partener']['image'];
						}else{
							echo cs_url."/images/default_avatar.jpg";
						}
					?>" style="height:25px;width:20px;border-radius:50%" alt="User Image">
				</div>
				<div class="col-xs-6" style="color:white" onclick="minichat_chatrestore_click({pk:'<?php echo $ret['pk']?>',pv:<?php echo $ret['pv']?>})">
					<span><b><?php echo $ret['partener']['nume']?></b></span>
				</div>
				<div class="col-xs-4" style="color:white;text-align:right">
					<?php if (!isset($p_arr['mobile'])){?>
					<i class="fa fa-window-minimize" aria-hidden="true" onclick="minichat_chatminimize_click({pk:'<?php echo $ret['pk']?>',pv:<?php echo $ret['pv']?>})"></i>
					<?php if ($ret['mek'] == 'from'){?>
					<i class="fa fa-window-maximize" aria-hidden="true" onclick="window.location.href='/mesaje/<?php echo $ret['partener']['uri']?>'"></i>
					<?php }?>
					<i class="fa fa-window-close" aria-hidden="true" onclick="minichat_chatclose_click({pk:'<?php echo $ret['pk']?>',pv:<?php echo $ret['pv']?>})"></i>
					<?php }else{?>
					<i class="fa fa-window-close" style="font-size:35px" aria-hidden="true" onclick="minichat_chatclose_click({pk:'<?php echo $ret['pk']?>',pv:<?php echo $ret['pv']?>})"></i>
					<?php }?>
				</div>
			</div>
			<div class="minichat-mesagelist">
			</div>
			<div class="row" style="margin:2px -11px">
				<form action="javascript:void(0)" onsubmit="minichat_send_click(this)">
					<input type="hidden" name="mek" value="<?php echo $ret['mek']?>">
					<input type="hidden" name="mev" value="<?php echo $ret['mev']?>">
					<input type="hidden" name="pk" value="<?php echo $ret['pk']?>">
					<input type="hidden" name="pv" value="<?php echo $ret['pv']?>">
					<div class="col-xs-12">
						<input type="text" name="text" class="form-control" placeholder="..." autocomplete="off">
					</div>
				</form>
			</div>
		</div>
	<?php
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
?>