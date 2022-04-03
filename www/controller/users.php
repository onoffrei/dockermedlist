<?php
$GLOBALS['user_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'email'				=>array('type'=>'text',	),
	'nume'				=>array('type'=>'text',	),
	'uri'				=>array('type'=>'text',	),
	'password'			=>array('type'=>'password',	),
	'date'				=>array('type'=>'datetime',	),
	'telefon'			=>array('type'=>'text',	),
	'image'				=>array('type'=>'int',	),
	'firebase'			=>array('type'=>'text',	),
	'status'			=>array('type'=>'int',	),
);
$GLOBALS['users_path'] = 'uploadusers';
$GLOBALS['users_path_curent'] = '';
function users_get($p_arr = array()){
	$ret = null;
	$list = users_grid($p_arr);
	if (isset($list['resp']['rows'])&&(count($list['resp']['rows'])>0)){
		$list = $list['resp'];
		$ret["id"] = intval($list['rows'][0]->id);
		$ret["nume"] = $list['rows'][0]->nume;
		$ret["uri"] = $list['rows'][0]->uri;
		$ret["email"] = $list['rows'][0]->email;
		$ret["password"] = $list['rows'][0]->password;
		$ret["date"] = $list['rows'][0]->date;		
		$ret["telefon"] = $list['rows'][0]->telefon;
		$ret["image"] = intval($list['rows'][0]->image);
		$ret["firebase"] = $list['rows'][0]->firebase;
		$ret["status"] = intval($list['rows'][0]->status);
	}
	return $ret;
}
function users_grid($p_arr = array()){
	global $user_cols;
	$p_arr['db_cols'] = $user_cols;
	$p_arr['db_table'] = 'users';
	return cs("_cs_grid/get",$p_arr);
}
function users_update($p_arr = array()){
	global $user_cols;
	$ret['p_arr'] = $p_arr;
	if (
		((!isset($p_arr['nume']))||($p_arr['nume'] == '')) 
		&& ($p_arr['oper'] == 'add')
	){
		$p_arr['nume'] = explode('@',$p_arr['email'])[0];
	}	
	if (
		((!isset($p_arr['uri']))||($p_arr['uri'] == '')) 
		&& isset($p_arr['nume'])
		&& ($p_arr['oper'] == 'add')
	){$p_arr['uri'] = $p_arr['nume'];}
	if (isset($p_arr['uri'])){
		$p_arr['uri'] = mb_strtolower($p_arr['uri']);
		$p_arr['uri'] = cs('util/removeAccents',array('text'=>$p_arr['uri']));
		$p_arr['uri'] = preg_replace('/(&#[0-9]*;)/s', '-', $p_arr['uri']);
		$p_arr['uri'] = preg_replace('/( +)/s', '-', $p_arr['uri']);
		$p_arr['uri'] = preg_replace('/([^-a-zA-Z0-9])/', '-', $p_arr['uri']);
		do{
			//make sure uri not conflict
			$ret['users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
				array("field"=>"id","op"=>"ne","data"=>$p_arr['id']),
				array("field"=>"uri","op"=>"eq","data"=>$p_arr['uri']),
			))));
			if ($ret['users_get'] != null) {
				$p_arr['uri'] = $p_arr['uri'] . rand(0,9);
			}
		}while($ret['users_get'] != null);
	}
	if ($p_arr['oper'] == 'add'){
		if (!isset($p_arr['firebase'])){
			$ret['firebase_register'] = cs('firebase/register');
			if (isset($ret['firebase_register']['success'])&&($ret['firebase_register']['success'] == true)){
				$p_arr['firebase'] = $ret['firebase_register']['resp']['name'];
			}				
		}
	}
	$p_arr['db_cols'] = $user_cols;
	$p_arr['db_table'] = 'users';
	return cs("_cs_grid/update",$p_arr);
}
function users_adaugadoctor_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	ob_start(); 
	?> <div id="users_adaugadoctor_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Adauga Personal</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="users_adaugadoctor_modal_action(this)" id="users_adaugadoctor_modal_form" autocomplete="off">
						<input type="hidden" name="spital" value="<?php echo $p_arr['spital']?>">
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Nume</label>
									<input type="text" name="nume" class="form-control" placeholder="Nume" value=''>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Email</label>
									<input type="email" name="email" class="form-control" placeholder="email" autocomplete="off" value=''>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Password</label>
									<input type="password" name="password" class="form-control" placeholder="password" autocomplete="new-password" value=''>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<button type="submit" class="btn btn-success btn-block">Adauga</button>
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
		$("#users_adaugadoctor_modal").modal('show')
		users_adaugadoctor_modal_action = function(form){
			cs('users/adaugadoctor',new FormData(form)).then(function(d){
				//console.log(d)
				$("#users_adaugadoctor_modal").modal('hide')
				if (typeof(d.success) == 'undefined' || d.success != true) {
					alert(d.error)
					return
				}
				window.location.reload()
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function users_adaugadoctor($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['nume']) || $p_arr['nume'] == ''){ $ret['error'] = 'check param - nume'; return $ret;}
	if (!isset($p_arr['spital']) || $p_arr['spital'] == ''){ $ret['error'] = 'check param - spital'; return $ret;}
	
	$ret['users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"email","op"=>"eq","data"=>$p_arr['email'])
	))));
	if ($ret['users_get'] != null) { $ret['error'] = 'email allready used'; return $ret;}
	
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$p_arr['level'] = 2;
	$ret['users_update'] = users_update($p_arr);
	if (!isset($ret['users_update']['success'])||($ret['users_update']['success'] != true)){return $ret;}
	
	$ret['spitale_users_update'] = cs('spitale_users/update',array(
		'oper'=>'add',
		'id'=>'null',
		'spital'=>$p_arr['spital'],
		'user'=>$ret['users_update']['resp']['id'],
		'level'=>user_level_doctor,
	));
	if (!isset($ret['spitale_users_update']['success'])||($ret['spitale_users_update']['success'] != true)){return $ret;}	
	$ret['success'] = true;
	return $ret;
}
function users_modifica_nume_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['users_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="users_modifica_nume_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="users_modifica_nume_modal_action(this)" id="users_modifica_nume_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Nume</label>
									<input type="text" name="nume" class="form-control" placeholder="Nume" value='<?php echo $ret['users_get']['nume']?>'>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<button type="submit" class="btn btn-success btn-block">Modifica</button>
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
		$("#users_modifica_nume_modal").modal('show')
		users_modifica_nume_modal_action = function(form){
			cs('users/modifica',new FormData(form)).then(function(users_modifica){
				$("#users_modifica_nume_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(users_modifica);'; 					
					}
				}else{ ?>
				window.location.reload()
				<?php } ?>
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function users_modifica_password_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['users_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="sers_modifica_password_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="sers_modifica_password_modal_action(this)" id="sers_modifica_password_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<?php if ($ret['users_get']['password'] != ''){ ?>
								<div class="form-group">
									<label>Parola veche</label>
									<input type="password" name="oldpassword" class="form-control" autocomplete="old-password" placeholder="password" value='' required>
								</div>
								<?php }?>
								<div class="form-group">
									<label>Parola Noua</label>
									<input type="password" name="newpassword" class="form-control" autocomplete="new-password" placeholder="password" value='' required>
								</div>
								<?php if ($ret['users_get']['password'] != ''){ ?>
								<div class="form-group">
									<label>Repeta parola noua</label>
									<input type="password" name="newpasswordrepeat" class="form-control" autocomplete="new-password-repeat" placeholder="password" value='' required>
								</div>
								<?php }?>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<button type="submit" class="btn btn-success btn-block">Modifica</button>
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
		$("#sers_modifica_password_modal").modal('show')
		sers_modifica_password_modal_action = function(form){
			if ($("#sers_modifica_password_modal input[name=oldpassword]").length == 1){
				if ($("#sers_modifica_password_modal input[name=newpassword]").val() != $("#sers_modifica_password_modal input[name=newpasswordrepeat]").val()){
					alert('Parola noua nu coincide')
					return
				}
			}
			cs('users/modifica_password',new FormData(form)).then(function(users_modifica_password){
				$("#sers_modifica_password_modal").modal('hide')
				console.log(users_modifica_password)
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(users_modifica_password);'; 					
					}
				}else{ ?>
				if ((typeof(users_modifica_password.success) == 'undefined') || (users_modifica_password.success != true)){
					if ((typeof(users_modifica_password.error) != 'undefined') && (users_modifica_password.error != '')){
						alert(users_modifica_password.error)
					}else{
						alert('ceva a mers prost')
					}
				}else{
					alert('success')
				}
				window.location.reload()
				<?php } ?>
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}

function users_modifica_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	
	$ret['users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['users_get'] == null) {return $ret;}

	$ret['spitale_users_get'] = cs('spitale_users/get',array("filters"=>array("rules"=>array(
		array("field"=>"user","op"=>"eq","data"=>$p_arr['id']),
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
	))));
	
	if ($ret['spitale_users_get'] == null ) {return $ret;}
	/*
	*/
	
	ob_start(); 
	?> <div id="users_modifica_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="users_modifica_modal_action(this)" id="users_modifica_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<input type="hidden" name="spital" value='<?php echo $p_arr['spital'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Nume</label>
									<input type="text" name="nume" class="form-control" placeholder="Nume" value='<?php echo $ret['users_get']['nume']?>'>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Uri</label>
									<input type="text" name="uri" class="form-control" placeholder="Uri" value='<?php echo $ret['users_get']['uri']?>'>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Descriere (profil doctor)</label>
									<textarea class="form-control" name="spitale_users_descriere" placeholder="Descriere" required rows="2"><?php echo $ret['spitale_users_get']['descriere']; ?></textarea>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Email</label>
									<input type="email" readonly class="form-control" placeholder="email" autocomplete="off"  value='<?php echo $ret['users_get']['email']?>'>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Password</label>
									<input type="password" name="password" class="form-control" autocomplete="new-password" placeholder="password" value='nicetry'>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<button type="submit" class="btn btn-success btn-block">Modifica</button>
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
		$("#users_modifica_modal").modal('show')
		users_modifica_modal_action = function(form){
			cs('users/modifica',new FormData(form)).then(function(d){
				//console.log(d)
				$("#users_modifica_modal").modal('hide')
				if (typeof(d.success) == 'undefined' || d.success != true) {
					alert('salvare nereusita')
					return
				}
				window.location.reload()
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function users_modifica($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	if (!isset($p_arr['nume']) || $p_arr['nume'] == ''){ $ret['error'] = 'check param - nume'; return $ret;}
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	if (isset($p_arr['password']) && ($p_arr['password'] == 'nicetry')) unset($p_arr['password']);
	if (isset($p_arr['spitale_users_descriere']) && isset($p_arr['spital'])){
		$ret['descriere_sql'] = " UPDATE spitale_users SET ";
		$ret['descriere_sql'] .= " 	descriere = '" . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spitale_users_descriere']) . "'";
		$ret['descriere_sql'] .= " 	WHERE  ";
		$ret['descriere_sql'] .= " 	spital = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
		$ret['descriere_sql'] .= " 	AND user = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id']);
		$ret['descriere_sql'] .= " 	LIMIT 1 ";
		$ret['descriere'] = cs("_cs_grid/get",array(
			'db_sql'=>$ret['descriere_sql'],
		));
		if (!isset($ret['descriere']['success'])||($ret['descriere']['success'] != true)){return $ret;}
	} 
	$p_arr['oper'] = 'edit';
	$ret['users_update'] = users_update($p_arr);
	if (!isset($ret['users_update']['success'])||($ret['users_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function users_modifica_password($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	if (!isset($p_arr['newpassword'])){ $ret['error'] = 'check param - newpassword'; return $ret;}

	$ret['users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['users_get'] == null) {return $ret;}
	
	if (($ret['users_get']['password'] != '') && (md5($p_arr['oldpassword']) != $ret['users_get']['password'])){
		$ret['error'] = 'parola gresita'; return $ret;
	}
	$p_arr['oper'] = 'edit';
	$ret['users_update'] = users_update(array(
		'oper' => 'edit',
		'id' => $p_arr['id'],
		'password' => $p_arr['newpassword'],
	));
	if (!isset($ret['users_update']['success'])||($ret['users_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function users_active_set($p_arr = array()){
	$ret = array('success'=>false);
	$ret['p_arr'] = $p_arr;
	$ret['users_get'] = users_get($p_arr); 
	if ($ret['users_get'] == null){
		$ret['users_grid'] = users_grid($p_arr);
		return $ret;
	}
	
	$_SESSION['cs_users_id'] = $ret['users_get']['id'];
	$_SESSION['cs_users_date'] = date('Y-m-d H:i:s');
	$_SESSION['users_id'] = $ret['users_get']['id'];//last login
	$_SESSION['users_date'] = date('Y-m-d H:i:s');
	
	$ret['spitale_users_get'] = cs('spitale_users/get',array("filters"=>array("rules"=>array(
		array("field"=>"user","op"=>"eq","data"=>$ret['users_get']['id']),
		array("field"=>"level","op"=>"eq","data"=>user_level_admin),
	))));
	
	if ($ret['spitale_users_get'] != null ) {
		$_SESSION['cs_users_isadmin'] = true;
	}else{
		$_SESSION['cs_users_isadmin'] = false;
	}
	if (defined('cs_firebase_db_name')) {
		if ($ret['users_get']['firebase'] == ''){
			$ret['firebase_register'] = cs('firebase/register');
			if (isset($ret['firebase_register']['success'])&&($ret['firebase_register']['success'] == true)){
				users_update(array(
					"oper"=>"edit",
					"firebase"=>$ret['firebase_register']['resp']['name'],
					"id"=>$ret['users_get']['id']
				));
				$ret['users_get']['firebase'] = $ret['firebase_register']['resp']['name'];
			}	
		}
		$_SESSION['firebase_usersid'] = $ret['users_get']['firebase'];
		$_SESSION['firebase_path'] = 'users';
		$_SESSION['firebase_activeid'] = $ret['users_get']['firebase'];
	}
	users_update(array(
		"oper"=>"edit",
		"id"=>$_SESSION['cs_users_id'],
		"date"=>date('Y-m-d H:i:s'),
		"status"=>1,
	));
	$ret['success'] = true;
	return $ret;
}
function users_login($p_arr = array()){
	$ret = array('success'=>false,'error'=>'Logare esuata','resp'=>array());
	if ((!isset($p_arr['email']))||($p_arr['email'] == '')||(!isset($p_arr['password']))||($p_arr['password'] == '')){$ret['error'] = 'missing field'; return $ret;}
	$ret['users_active_set'] = users_active_set(array("filters"=>array("rules"=>array(
		array("field"=>"email","op"=>"eq","data"=>$p_arr['email']),
		array("field"=>"password","op"=>"eq","data"=>(md5($p_arr['password'])))
	)))); 
	if (!isset($ret['users_active_set']['success'])||($ret['users_active_set']['success'] != true)) {return $ret;}
	$ret['resp'] = $ret['users_active_set']['users_get'];
	$ret['success'] = true;
	$_SESSION['cs_response_message'] = 'Logare reusita';
	unset($ret['error']);
	return $ret;
}
function users_login_js($p_arr = array()){
	return users_login($p_arr);
}
function users_login_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	ob_start(); 
	if (isset($_SESSION['cs_users_id']) && (intval($_SESSION['cs_users_id']) > 0)){
		$ret['resp']['html'] = "<script>alert('allready in')</script>";
		return $ret;
	}
?>
	<div id="users_login_htmlmodal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-sm" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> Log In</h4>
				</div>
				<div class="modal-body">
					<?php 
						$ret['users_login_form'] = users_login_form($p_arr);
						if (!isset($ret['users_login_form']['success']) || ($ret['users_login_form']['success'] != true)) {$ret['resp']['html'] = 'foo';ob_end_clean();return $ret;}
						echo $ret['users_login_form']['resp']['html'];
					?>
				</div>
				<div class="modal-footer">
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<script>
		$("div.modal-backdrop.fade.in").remove()
		$('#users_login_htmlmodal').modal('show');
	</script>
<?php
	$ret['success'] = true;
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	return $ret;
}
function users_login_form($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'error'=>'');
	if (isset($_SESSION['cs_users_id']) && (intval($_SESSION['cs_users_id']) > 0)){
		$ret['resp']['html'] = "Deja logat!";
		$ret['success'] = true;
		return $ret;
	}
	ob_start(); 
?>
<form action="javascript:void(0)" onsubmit="users_login_form_action()" id="users_login_form" >
	<div class="row">
		<div class="col-xs-12 text-center">
			<?php $state = ''; if (isset($p_arr['callback'])) {$state = '&state=' . $p_arr['callback'];}?>
			<?php if (defined('cs_facebook_auth')) {?>
				<a href="<?php echo cs_facebook_auth . $state; ?>" target="_blank" class="fa fa-mylogin fa-facebook"></a>
			<?php } ?>
			<?php if (defined('cs_google_auth')) { ?>
				<a href="<?php echo cs_google_auth . $state; ?>" target="_blank" class="fa fa-mylogin fa-google"></a>
			<?php } ?>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<div class="form-group">
				<label >email</label>
				<input type="email" class="form-control" name="email" placeholder="email" required>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<div class="form-group">
				<label >password</label>
				<input type="password" class="form-control" name="password" placeholder="password" required>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<button type="submit" class="btn btn-lg btn-success" style="width:100%">Submit</button>
		</div>
	</div>
	<br/>
	<div class="row">
		<div class="col-xs-12">
			<div class="form-group">
				<?php 
					$jsonparr = ''; 
					if (isset($p_arr)) {$jsonparr = ','.json_encode($p_arr);}
				?>
				<a href="javascript:void(0)" onclick='$("#users_login_htmlmodal").modal("hide");cs("users/register_rs"<?php echo $jsonparr; ?>)'>Nu aveti cont? - click aici</a>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<div class="form-group">
				<a href="javascript:void(0)" onclick='$("#users_login_htmlmodal").modal("hide");cs("users/reset_rs"<?php echo $jsonparr; ?>)'>Ati uitat parola? - click aici</a>
			</div>
		</div>
	</div>
</form>
<script>
	users_login_form_action = function(){
		cs("users/login_js",new FormData(document.getElementById("users_login_form"))).then(function(login_js){
			<?php if (isset($p_arr['callback'])){ 
				if ($p_arr['callback'] == 'window.location.reload'){
					echo $p_arr['callback'] . '(true)';//iexplore bug
				}else{
					echo $p_arr['callback'] . '(login_js)'; 					
				}
			}else{ ?>
			if ((typeof(login_js.success) == 'undefined')||(login_js.success != true)){
				if (typeof(login_js.error) != 'undefined'){
					alert(login_js.error)
				}else{
					alert('something went wrong')
				}
				return
			}
			window.location.reload()
			<?php } ?>
		})
	}
</script>
<?php
	$ret['success'] = true;
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	return $ret;
}
function users_login_html($p_arr = array()){
	if (isset($_SESSION['cs_users_id']) && (intval($_SESSION['cs_users_id']) > 0)){
		if (isset($_REQUEST['urlnext']) && ($_REQUEST['urlnext'] != '')){
			header('Location: ' . urldecode($_REQUEST['urlnext']));
			exit;
		}
	}
	function header_ob(){
		$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
		ob_start();
		?>
		<title>programari-medicale-online</title>
		<meta name="robots" content="noindex, nofollow" />	
		<style>
		#users_login_form{
			max-width: 330px;
			padding: 15px;
			margin: 0 auto;
		}
		</style>
		<?php
		$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
		$ret['success'] = true;
		return $ret;
	}
	$GLOBALS['header_ob'] = header_ob();
	require_once(cs_path . DIRECTORY_SEPARATOR . 'header.php' );
	$GLOBALS['users_login_form'] = users_login_form(
		// array('callback'=>'login_callback')
	);
	//$GLOBALS['users_login_form'] = users_login_form(array('callback'=>'console.log'));
	cscheck($GLOBALS['users_login_form']);
	
	echo $GLOBALS['users_login_form']['resp']['html'];

	function footer_ob(){
		global $postid;
		$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
		ob_start();
	?>
		<script>
			document.querySelector('input[type="email"]').focus()
		</script>
	<?php
		$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
		$ret['success'] = true;
		return $ret;
	}
	$GLOBALS['footer_ob'] = footer_ob();
	cscheck($GLOBALS['footer_ob']);
	require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
}
function users_logout($p_arr = array()){
	users_update(array(
		"oper"=>"edit",
		"id"=>$_SESSION['cs_users_id'],
		"status"=>0,
	));
	unset($_SESSION['cs_users_id']);
	unset($_SESSION['cs_users_isadmin']);
	unset($_SESSION['cs_users_date']);
	$_SESSION['cs_response_message'] = 'Deconectare reusita';
	unset($_SESSION['firebase_activeid']);
	if (defined('cs_firebase_db_name')) {
		if (isset($_SESSION['firebase_browserid']) && ($_SESSION['firebase_browserid'] != '')){
			$_SESSION['firebase_path'] = 'browser';
			$_SESSION['firebase_activeid'] = $_SESSION['firebase_browserid'];
		}
	}
	return array('success'=>true);
}
function users_logout_js($p_arr = array()){
	return users_logout($p_arr);
}
function users_logout_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	$ret['users_logout'] = users_logout();
	ob_start(); 
?>
	<script>
			<?php if (isset($p_arr['callback'])){ 
					if ($p_arr['callback'] == 'window.location.reload'){
						echo $p_arr['callback'] . '(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(' . json_encode($ret) . ')'; 				
					}
				}else{ 
			?>
			window.location.reload()
			<?php } ?>
	</script>
<?php
	$ret['success'] = true;
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	return $ret;
}
function users_register($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	if ((!isset($p_arr['email']))||($p_arr['email'] == '')){$ret['error'] = 'missing field'; return $ret;}
	$_SESSION['cs_response_message'] = 'Inregistrare esuata';
	$ret['users_get'] = users_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(
		array("field"=>"email","op"=>"eq","data"=>$p_arr['email'])
	)))); 
	if ($ret['users_get'] != null){
		$_SESSION['cs_response_message'] = 'Inregistrare esuata - allready in';
		$ret['error'] = 'allready in'; 
		return $ret;
	}
	
	$p_arr['date'] = date('Y-m-d H:i:s');
	$p_arr['level'] = 1;
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$ret['users_update'] = users_update($p_arr);
	if (!isset($ret['users_update']['success'])||($ret['users_update']['success'] != true)){
		$_SESSION['cs_response_message'] = 'Inregistrare esuata - ' . json_encode($ret['users_update']);
		return $ret;
	}
	$_SESSION['cs_response_message'] = 'Inregistrare reusita';
	$ret['resp']['id'] = $ret['users_update']['resp']['id'];
	$ret['success'] = true;
	return $ret;
}
function users_register_js($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if ((!isset($p_arr['email']))||($p_arr['email'] == '')||(!isset($p_arr['password']))||($p_arr['password'] == '')){$ret['error'] = 'missing field'; return $ret;}
	$ret['users_register'] = users_register($p_arr);
	if (!isset($ret['users_register']['success'])||($ret['users_register']['success'] != true)){return $ret;}
	if (isset($p_arr['activate'])){
		$ret['users_active_set'] = users_active_set(array("filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$ret['users_register']['resp']['id']),
		)))); 
		if (!isset($ret['users_active_set']['success'])||($ret['users_active_set']['success'] != true)) {return $ret;}
	}
	$ret['resp']['id'] = $ret['users_register']['resp']['id'];
	$ret['success'] = true;
	return $ret;
}
function users_register_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	ob_start(); 
?>
<div id="users_register_htmlmodal" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-sm" style="z-index:105">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> Creeaza cont nou</h4>
			</div>
			<div class="modal-body">
				<form action="javascript:void(0)" onsubmit="users_register_htmlmodal_action()" id="users_register_htmlmodal_form" >
					<?php if (isset($p_arr['activate'])) {?>
						<input type="hidden" name="activate" value="true">
					<?php } ?>
					<div class="row">
						<div class="col-xs-12 text-center">
							<?php $state = ''; if (isset($p_arr['callback'])) {$state = '&state=' . $p_arr['callback'];}?>
							<?php if (defined('cs_facebook_auth')) {?>
								<a href="<?php echo cs_facebook_auth . $state; ?>" target="_blank" class="fa fa-mylogin fa-facebook"></a>
							<?php } ?>
							<?php if (defined('cs_google_auth')) { ?>
								<a href="<?php echo cs_google_auth . $state; ?>" target="_blank" class="fa fa-mylogin fa-google"></a>
							<?php } ?>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label >email</label>
								<input type="email" class="form-control" name="email" placeholder="email" required>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label >password</label>
								<input type="password" class="form-control" name="password" placeholder="password" required>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label >password</label>
								<input type="password" class="form-control" name="pass1" placeholder="password" required>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<button type="submit" class="btn btn-lg btn-success" style="width:100%">Submit</button>
						</div>
					</div>
					<br/>
					<br/>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<?php 
									$jsonparr = ''; 
									if (isset($p_arr)) {$jsonparr = ','.json_encode($p_arr);}
								?>
								<a href="javascript:void(0)" onclick='$("#users_register_htmlmodal").modal("hide");cs("users/login_rs"<?php echo $jsonparr; ?>)'>Aveti deja cont? - click aici</a>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
	$("div.modal-backdrop.fade.in").remove()
	$('#users_register_htmlmodal').modal('show');
	users_register_htmlmodal_action = function(){
		var password = $("#users_register_htmlmodal_form input[name='password']")
		var pass1 = $("#users_register_htmlmodal_form input[name='pass1']")
		if (pass1.val() != password.val()) {
			alert("password mismatch")
			return
		}
		cs("users/register_js",new FormData(document.getElementById("users_register_htmlmodal_form"))).then(function(register_js){
			<?php if (isset($p_arr['callback'])){ 
				if ($p_arr['callback'] == 'window.location.reload'){
					echo $p_arr['callback'] . '(true)';//iexplore bug
				}else{
					echo $p_arr['callback'] . '(register_js)'; 			
				}
			}else{ ?>
			if ((typeof(register_js.success) == 'undefined')||(register_js.success != true)){
				if (typeof(register_js.error) != 'undefined'){
					//alert(register_js.error)
				}else{
					//alert('something went wrong')
				}
				window.location = window.cs_url_po + "csapi/response/error?message=inregistrare esuata!!"
				return
			}
			window.location = window.cs_url_po + "csapi/response/success?message=inregistrare reusita!!"
			<?php } ?>
		})
	}
</script>
<?php
	$ret['success'] = true;
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	return $ret;
}
function users_reset_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs');
	ob_start(); 
?><div id="users_reset_htmlmodal" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-sm" style="z-index:105">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> Reseteaza parola</h4>
			</div>
			<div class="modal-body">
				<form action="javascript:void(0)" onsubmit="users_reset_htmlmodal_action()" id="users_reset_htmlmodal_form" >
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label >email</label>
								<input type="email" class="form-control" name="email" placeholder="email" required>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<button type="submit" class="btn btn-lg btn-success" style="width:100%">Submit</button>
						</div>
					</div>
					<br/>
				</form>
			</div>
			<div class="modal-footer">
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal --><script>
	$("div.modal-backdrop.fade.in").remove()
	$('#users_reset_htmlmodal').modal('show');
	users_reset_htmlmodal_action = function(){
		$("div.modal-backdrop.fade.in").remove()
		$('#users_reset_htmlmodal').modal('show');
		cs("users/reset_js",new FormData(document.getElementById("users_reset_htmlmodal_form"))).then(function(reset_js){
			if ((typeof(reset_js.success) == 'undefined')||(reset_js.success != true)){
				if ((typeof(reset_js.error) != 'undefined') && (reset_js.error != '')){
					alert(reset_js.error)
				}else{
					alert('something went wrong')
				}
				return
			}
			alert('am trimis un mail pe adresa ta, deschide emailul si da click pe linkul primit')
			window.location.reload()
		})
	}
</script><?php
	$ret['success'] = true;
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	return $ret;
}
function users_reset_js($p_arr = array()){
	$ret = array('success'=>false);
	if ((!isset($p_arr['email']))||($p_arr['email'] == '')){$ret['error'] = 'missing field'; return $ret;}
	$ret['users_get'] = users_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(
		array("field"=>"email","op"=>"eq","data"=>$p_arr['email']))
	))); 
	if ($ret['users_get'] == null){$ret['error'] = 'no such email'; return $ret;}
	
	$ret['key'] = md5($ret['users_get']['password'] . $ret['users_get']['date']);
	$ret['link'] = cs_url . '/csapi/users/reset_html?email=' . urlencode($ret['users_get']['email']) . '&key=' . $ret['key'];
	
	$headers = 'MIME-Version: 1.0' . "\r\n"
		. 'Content-type: text/html; charset=iso-8859-1' . "\r\n"
		. 'From: ' . cs_email . "\r\n"
		. 'Reply-To: ' . cs_email . "\r\n"
		. 'X-Mailer: PHP/' . phpversion();
	$message = 		"Salut " . $ret['users_get']['email'] . " \n<br>"
		. "Ai primit acest email deoarece o cerere de resetare parola pe <a href='" . cs_url . "'>" . cs_url . "</a> a fost emisa in numele tau. \n<br>"
		. "Pentru a finaliza inregistrarea, te rugam sa dai click pe linkul de mai jos: \n<br>"
		. "<h2><a href='" . $ret['link'] . "'>" . cs_url . "</a>  \n<br></h2>"
		. "Mesaj generat automat, te rugam nu da reply.\n<br>"
		. "\n<br>"
		. "Cu stima,\n<br>"
		. "Echipa, <a href='" . cs_url . "'>" . cs_url . "</a>\n<br>";

	mail($ret['users_get']['email'] , 'reset password', $message,$headers);
	$ret['success'] = true;
	return $ret;
}
function users_reset_html($p_arr = array()){
	$ret = array('success'=>false);
	if ((!isset($p_arr['email']))||($p_arr['email'] == '')){
		$ret['error'] = 'missing field email'; 
		//return $ret;
	}
	if ((!isset($p_arr['key']))||($p_arr['key'] == '')){
		$ret['error'] = 'missing field key'; 
		//return $ret;
	}
	function header_ob(){
		$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
		ob_start();
		?>
		<title>programari-online</title>
		<meta name="robots" content="noindex, nofollow" />	
		<style>
		.mycenter{
			max-width: 420px;
			padding: 15px;
			margin: 0 auto;
		}
		</style>
		<?php
		$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
		$ret['success'] = true;
		return $ret;
	}
	$GLOBALS['header_ob'] = header_ob();
	require_once(cs_path . DIRECTORY_SEPARATOR . 'header.php' );
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		if ((!isset($p_arr['password']))||($p_arr['password'] == '')){
			$ret['error'] = 'missing field password'; 
			//return $ret;
		}
		if (!isset($ret['error'])){
			$ret['users_get'] = users_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(
				array("field"=>"email","op"=>"eq","data"=>$p_arr['email'])
			)))); 
			if ($ret['users_get'] == null){
				$ret['error'] = 'no such email'; 
				//return $ret;
			}			
			$ret['key'] = md5($ret['users_get']['password'] . $ret['users_get']['date']);
		}
		if (!isset($ret['error'])){
			if ($ret['key'] != $p_arr['key']){
				$ret['error'] = 'no such wrong key'; 
				//return $ret; 
			}		
		}
		if (!isset($ret['error'])){
			$ret['users_update'] = users_update(array(
				'oper'=>'edit',
				'id'=>$ret['users_get']['id'],
				'password'=>$p_arr['password'],
			));
			if (!isset($ret['users_update']['success'])||($ret['users_update']['success'] != true)){
				$ret['error'] = 'upss...';
				//return $ret;
			}
		}
		?>
		<div class="mycenter">
			<div class="panel panel-default ">
				<div class="panel-heading">
					<h3 class="panel-title">Password reset</h3>
				</div>
				<div class="panel-body">
					<?php if (isset($ret['error'])){
						echo $ret['error'];
					?>
					<?php }else{?>
					<a href="<?php echo cs_url . '/'?>">
						Parola a fost schimbata cu succes!. Continua sa vizitezi site-ul
					</a>
					<?php }?>
				</div>
			</div>
		</div>		
		<?php
	}else{	
		?>
		<div class="mycenter">
			<div class="panel panel-default ">
				<div class="panel-heading">
					<h3 class="panel-title">Password reset</h3>
				</div>
				<div class="panel-body">
					<?php if (isset($ret['error'])){
						echo $ret['error'];
					?>
					<?php }else{?>
					<form method="post" class="">
						<input type="hidden" name="email" value="<?php echo $p_arr['email'];?>">
						<input type="hidden" name="key" value="<?php echo $p_arr['key'];?>">
						<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
							<div class="col-xs-12">
								<div class="form-group has-feedback">
									<label class="col-sm-4 control-label">parola noua</label>
									<div class="col-sm-8">
										<input type="password" class="form-control" name="password" placeholder="parola" required autocomplete="off">
									</div>
								</div>
							</div>
						</div>
						<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;">
							<div class="col-xs-12">
								<button type="submit" class="btn btn-primary btn-block">Reset</button>
							</div>
						</div>
					</form>
					<?php }?>
				</div>
			</div>
		</div>
		<?php
	}
	function footer_ob(){
		global $postid;
		$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
		ob_start();
	?>
		<script>
			document.querySelector('input[type="email"]').focus()
		</script>
	<?php
		$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
		$ret['success'] = true;
		return $ret;
	}
	$GLOBALS['footer_ob'] = footer_ob();
	cscheck($GLOBALS['footer_ob']);
	require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
}

function users_edit_js($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'permision'; return $ret;}
	$p_arr['id'] = $_SESSION['cs_users_id'];
	$p_arr['oper'] = 'edit';
	$ret['users_update'] = users_update($p_arr);
	$ret['success'] = true;
	unset($ret['error']);
	return $ret;
}
function users_doctordelete($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'log in first'; return $ret;}
	if ((!isset($p_arr['did']))||(!(intval($p_arr['did']) > 0))){$ret['error'] = 'param did??'; return $ret;}
	if ((!isset($p_arr['sid']))||(!(intval($p_arr['sid']) > 0))){$ret['error'] = 'param sid??'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	
	$ret['spitale_users_unlink'] = cs('spitale_users/unlink',array('uid'=>$p_arr['did'],'sid'=>$p_arr['sid']));
	if (!isset($ret['spitale_users_unlink']['success'])||($ret['spitale_users_unlink']['success'] != true)){return $ret;}
	
	$ret['specializari_user_spitale_sql'] = 'DELETE'
		. ' FROM `specializari_user_spitale`'
		. ' WHERE'
			. '  `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['sid'])
			. ' AND  `user` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['did'])
	;
	$ret['specializari_user_spitale'] = cs("_cs_grid/get",array('db_sql'=>$ret['specializari_user_spitale_sql']));
	if (!isset($ret['specializari_user_spitale']['success'])||($ret['specializari_user_spitale']['success'] != true)){return $ret;}
	
	$ret['programari_sql'] = 'DELETE'
		. ' FROM `programari`'
		. ' WHERE'
			. ' (`spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['sid'])
			. ' AND  `doctor` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['did'])
			. ' ) OR (`user` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['did']) . ')'
	;
	$ret['programari'] = cs("_cs_grid/get",array('db_sql'=>$ret['programari_sql']));
	if (!isset($ret['programari']['success'])||($ret['programari']['success'] != true)){return $ret;}
	
	$ret['detaliiservicii_sql'] = 'DELETE'
		. ' FROM `detaliiservicii`'
		. ' WHERE'
			. '  `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['sid'])
			. ' AND  `doctor` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['did'])
	;
	$ret['detaliiservicii'] = cs("_cs_grid/get",array('db_sql'=>$ret['detaliiservicii_sql']));
	if (!isset($ret['detaliiservicii']['success'])||($ret['detaliiservicii']['success'] != true)){return $ret;}
	
	$ret['users_delete'] = cs('users/delete',array('id'=>$p_arr['did']));
	if (!isset($ret['users_delete']['success'])||($ret['users_delete']['success'] != true)){return $ret;}
	
	$ret['success'] = true;
	return $ret;
}
function users_delete($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'log in first'; return $ret;}
	if ((!isset($p_arr['id']))||(!(intval($p_arr['id']) > 0))){$p_arr['id'] = intval($_SESSION['cs_users_id']);}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	$ret['users_get'] = users_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['users_get'] == null) {return $ret;}
	
	if (intval($ret['users_get']['image']) >0 ){
		$ret['users_image_delete'] = cs('users/image_delete',array("user"=>$p_arr['id']));
		if (!isset($ret['users_image_delete']['success'])||($ret['users_image_delete']['success'] != true)){return $ret;}
	}
	
	$ret['programari_sql'] = 'DELETE'
		. ' FROM `programari`'
		. ' WHERE'
			. ' `user` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id'])
	;
	$ret['programari'] = cs("_cs_grid/get",array('db_sql'=>$ret['programari_sql']));
	if (!isset($ret['programari']['success'])||($ret['programari']['success'] != true)){return $ret;}

	$ret['usersstatus_sql'] = 'DELETE'
		. ' FROM `usersstatus`'
		. ' WHERE'
			. ' `hunter` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id'])
			. ' OR `pray` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id'])
	;
	$ret['usersstatus'] = cs("_cs_grid/get",array('db_sql'=>$ret['usersstatus_sql']));
	if (!isset($ret['usersstatus']['success'])||($ret['usersstatus']['success'] != true)){return $ret;}
	
	
	if (intval($_SESSION['cs_users_id']) == intval($p_arr['id'])){
		$p_arr['id'] = $_SESSION['cs_users_id'];
		users_logout();
	}
	$p_arr['oper'] = 'del';
	$ret['users_update'] = users_update($p_arr);
	if (!isset($ret['users_update']['success'])||($ret['users_update']['success'] != true)){return $ret;}

	$ret['success'] = true;
	return $ret;
}
function users_delete_html($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	$ret['users_delete'] = users_delete($p_arr = array());
	if (!isset($ret['users_delete']['success'])||($ret['users_delete']['success'] != true)){cs('response/error',array('message'=>'user delete error:' . json_encode($ret)));	}
	{cs('response/success',array('message'=>'Cont sters cu succes'));	}
	return $ret;
}
function users_pass_change_htmlmodal($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'permision'; return $ret;}
	$ret['users_get'] = users_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])))));
?><div id="users_pass_change_htmlmodal" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-sm" style="z-index:105">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> Schimba parola</h4>
			</div>
			<div class="modal-body">
				<form action="javascript:void(0)" onsubmit="users_pass_change_htmlmodal_action()" id="users_pass_change_htmlmodal_form" >
					<?php if ($ret['users_get']['password'] != ''){ ?>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label >Parola veche</label>
								<input type="password" class="form-control" name="pass0" placeholder="password" required>
							</div>
						</div>
					</div>
					<?php } ?>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label >parola noua</label>
								<input type="password" class="form-control" name="password" placeholder="password" required>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label >repeta parola noua</label>
								<input type="password" class="form-control" name="pass1" placeholder="password" required>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<button type="submit" class="btn btn-lg btn-success" style="width:100%">Submit</button>
						</div>
					</div>
					<br/>
				</form>
			</div>
			<div class="modal-footer">
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal --><?php
}
function users_pass_change($p_arr){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'permision'; return $ret;}
	$ret['users_get'] = users_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])))));
	if (($ret['users_get']['password'] != '')){
		if (!isset($p_arr['pass0'])) {$ret['error'] = 'missing password'; return $ret;}
		if (md5($p_arr['pass0'])!=$ret['users_get']['password']){$ret['error'] = 'wrong password'; return $ret;}
	}
	$p_arr['id'] = $_SESSION['cs_users_id'];
	$p_arr['oper'] = 'edit';
	$ret['users_update'] = users_update($p_arr);
	$ret['success'] = true;
	unset($ret['error']);
	return $ret;
}
function users_getspitalusers($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'error'=>'');
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$ret['levelsql'] = '';
	if (isset($p_arr['level'])){ $ret['levelsql'] = ' AND spitale_users.level <= ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['level']);}
	global $user_cols;
	$p_arr['db_cols'] = $user_cols;
	$p_arr['db_table'] = 'users';
	$p_arr['db_sql'] = "SELECT SQL_CALC_FOUND_ROWS ";
	$p_arr['db_sql'] .= "    users.id";
	$p_arr['db_sql'] .= "    ,users.email";
	$p_arr['db_sql'] .= "    ,users.password";
	$p_arr['db_sql'] .= "    , users.date";
	$p_arr['db_sql'] .= "    , users.nume";
	$p_arr['db_sql'] .= "    , users.uri";
	$p_arr['db_sql'] .= "    , users.image";
	$p_arr['db_sql'] .= "    , users.status as status";
	$p_arr['db_sql'] .= "    , spitale_users.user AS spitale_users_userid";
	$p_arr['db_sql'] .= "    , spitale_users.level AS spitale_users_level";
	$p_arr['db_sql'] .= " FROM spitale_users";
	$p_arr['db_sql'] .=  " LEFT JOIN users ON users.id = spitale_users.user";
	$p_arr['db_sql'] .=  " WHERE spitale_users.spital = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
	$p_arr['db_sql'] .=  $ret['levelsql'];
	$p_arr['db_sql'] .=  " __order__ __limit__";
	$ret['p_arr'] = $p_arr;
	$ret['resp'] = cs("_cs_grid/get",$p_arr);
	if (!isset($ret['resp']['success'])||($ret['resp']['success'] != true)){return $ret;}
	$ret['resp'] = $ret['resp']['resp'];
	$ret['success'] = true;
	return $ret;
}
function users_glanding_html($p_arr){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	if (!isset($p_arr['code'])){ $ret['error'] = 'google login error. mising code parameter'; $p_arr['ret'] = $ret; users_landingpopupcallback($p_arr);}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v4/token");
	$post = "client_id=" . cs_google_clientid 
	. "&client_secret=" . cs_google_clientsecret 
	. "&redirect_uri=" . urlencode(cs_url . '/' . cs_google_landing )
	. "&grant_type=authorization_code"
	. "&code=" . $p_arr['code'];
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$q1 = curl_exec($ch);
	$q1 = json_decode($q1,true);
	$ret['q1'] = $q1;
	if (!isset($q1['access_token'])){$ret['error'] = 'google login error. acces token parameter'; $p_arr['ret'] = $ret; users_landingpopupcallback($p_arr);	}
	curl_setopt($ch, CURLOPT_POST, 0);
	curl_setopt($ch, CURLOPT_URL, "https://people.googleapis.com/v1/people/me"
		. "?requestMask.includeField=person.email_addresses,person.names,person.photos"
		. "&access_token=" . $q1['access_token']
	);
	$q2 = curl_exec($ch);
	$q2 = json_decode($q2, true);
	$ret['q2'] = $q2;
	if (!isset($q2['emailAddresses'][0]['value'])){$ret['error'] = 'google login error. acces mail parameter'; $p_arr['ret'] = $ret; users_landingpopupcallback($p_arr);}
	$ret['message'] = 'Google login success';
	$ret['users_get'] = users_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(
		array("field"=>"email","op"=>"eq","data"=>$q2['emailAddresses'][0]['value'])
	))));
	if ($ret['users_get'] == null){
		$ret['users_update_param'] = array(
			'oper'=>'add',
			'id'=>'null',
			'email'=>$q2['emailAddresses'][0]['value'],
			'nume'=>$q2['names'][0]['displayName'],
			'date'=>date('Y-m-d H:i:s'),
		);
		$ret['image'] = cs('images/add',array('image'=>file_get_contents($q2['photos'][0]['url'])));
		if (isset($ret['image']['resp']['id'])) {$ret['users_update_param']['image'] = $ret['image']['resp']['id'];}
		$ret['users_update'] = cs('users/update',$ret['users_update_param']);
		if (!isset($ret['users_update']['success'])||($ret['users_update']['success'] != true)){$p_arr['ret'] = $ret; users_landingpopupcallback($p_arr);}		
		$ret['user_id'] = $ret['users_update']['resp']['id'];
		$ret['message'] = 'Google register & login success';
	}
	$ret['users_active_set'] = users_active_set(array("filters"=>array("rules"=>array(
		array("field"=>"email","op"=>"eq","data"=>$q2['emailAddresses'][0]['value'])
	)))); 
	if (!isset($ret['users_active_set']['success'])||($ret['users_active_set']['success'] != true)){
		$ret['error'] = 'google login error.'; $p_arr['ret'] = $ret; users_landingpopupcallback($p_arr);
	};
	$ret['success'] = true; 
	$p_arr['ret'] = $ret; 
	users_landingpopupcallback($p_arr);
}
function users_fblanding_html($p_arr){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	if (!isset($p_arr['code'])){ $ret['error'] = 'fb login error. mising code parameter'; $p_arr['ret'] = $ret; users_landingpopupcallback($p_arr);}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 0);
	curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v2.3/oauth/access_token"
		. "?client_id=" . cs_facebook_clientid 
		. "&redirect_uri=" . urlencode(cs_url . '/' . cs_facebook_landing )
		. "&client_secret=" . cs_facebook_clientsecret 
		. "&code=" . $p_arr['code']
	);
	$q1 = curl_exec($ch);
	$q1 = json_decode($q1,true);
	$ret['q1'] = $q1;
	if (!isset($q1['access_token'])){$ret['error'] = 'fb login error. acces token parameter'; $p_arr['ret'] = $ret; users_landingpopupcallback($p_arr);	}
	curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v2.3/me/"
		. "?fields=id,name,email,picture"
		. "&access_token=" . $q1['access_token']
	);
	$q2 = curl_exec($ch);
	$q2 = json_decode($q2, true);
	$ret['q2'] = $q2;
	if (!isset($q2['email'])){$ret['error'] = 'fb login error. acces mail parameter'; $p_arr['ret'] = $ret; users_landingpopupcallback($p_arr);}
	$ret['message'] = 'fb login success';
	$ret['users_get'] = users_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(
		array("field"=>"email","op"=>"eq","data"=>$q2['email'])
	))));
	if ($ret['users_get'] == null){
		$ret['users_update_param'] = array(
			'oper'=>'add',
			'id'=>'null',
			'email'=>$q2['email'],
			'nume'=>$q2['name'],
			'date'=>date('Y-m-d H:i:s'),
		);
		$ret['image'] = cs('images/add',array('image'=>file_get_contents($q2['picture']['data']['url'])));
		if (isset($ret['image']['resp']['id'])) {$ret['users_update_param']['image'] = $ret['image']['resp']['id'];}
		$ret['users_update'] = cs('users/update',$ret['users_update_param']);
		if (!isset($ret['users_update']['success'])||($ret['users_update']['success'] != true)){$p_arr['ret'] = $ret; users_landingpopupcallback($p_arr);}		
		$ret['user_id'] = $ret['users_update']['resp']['id'];
		$ret['message'] = 'fb register & login success';
	}
	$ret['users_active_set'] = users_active_set(array("filters"=>array("rules"=>array(
		array("field"=>"email","op"=>"eq","data"=>$q2['email'])
	)))); 
	if (!isset($ret['users_active_set']['success'])||($ret['users_active_set']['success'] != true)){
		$ret['error'] = 'fb login error.'; $p_arr['ret'] = $ret; users_landingpopupcallback($p_arr);
	};
	$ret['success'] = true; 
	$p_arr['ret'] = $ret; 
	users_landingpopupcallback($p_arr);
}
function users_landingpopupcallback($p_arr = array()){
	$script = '';
	if (isset($p_arr['ret']['success'])){
		$p_arr['success'] = $p_arr['ret']['success'];
	}
	if (isset($p_arr['state']) && ($p_arr['state'] != '')){
		ob_start();
		?>
		<script>
			var opener = window.opener
			if (opener){
				if (typeof(opener.<?php echo $p_arr['state']?>) == 'function') opener.<?php echo $p_arr['state']?>(<?php echo json_encode($p_arr); ?>)
			}
		</script>
		<?php
		$script = ob_get_contents(); ob_end_clean();
	}
	ob_start(); 
?>
<html lang="en">
    <head>
    </head>
	<body>
		<?php echo $script; ?>
		<script>
			window.close()
		</script>
		<?php echo json_encode($p_arr) ?>
	</body>
</html>
<?php
	$html = ob_get_contents(); ob_end_clean();
	echo $html;
	exit;
}
function users_image_change($p_arr){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'permision'; return $ret;}
	$ret['users_get'] = users_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
	))));
	if ($ret['users_get'] == null) {return $ret;}
	if ($ret['users_get']['image'] > 0) $ret['images_delete'] = cs("images/delete",array("filters"=>array("groupOp"=>"AND","rules"=>array(array("field"=>"id","op"=>"eq","data"=>$ret['users_get']['image'])))));
	$ret['images_add'] = cs('images/add');
	if(!isset($ret['images_add']['success']) || ($ret['images_add']['success'] != true)) return $ret;
	$ret['users_update'] = users_update(array(
		"oper"=>"edit",
		"id"=>$ret['users_get']['id'], 
		"image"=>$ret['images_add']['resp']['id']
	));
	if(!isset($ret['users_update']['success']) || ($ret['users_update']['success'] != true)) return $ret;
	$ret['success'] = true; 
	unset($ret['error']);
	return $ret;
}
function users_image_delete($p_arr){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'permision'; return $ret;}
	if ((!isset($p_arr['user']))||(!(intval($p_arr['user']) > 0))){$p_arr['user'] = intval($_SESSION['cs_users_id']);}
	
	$ret['users_get'] = users_get(array("filters"=>array("groupOp"=>"AND","rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['user'])
	))));
	if ($ret['users_get'] == null) {return $ret;}
	if (intval($ret['users_get']['image']) > 0){
		$ret['images_delete'] = cs("images/delete",array("filters"=>array("groupOp"=>"AND","rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$ret['users_get']['image'])
		))));
		$ret['users_update'] = users_update(array("oper"=>"edit","id"=>$ret['users_get']['id'], "image"=>"0"));
		if(!isset($ret['users_update']['success']) || ($ret['users_update']['success'] != true)) return $ret;		
	}

	$ret['success'] = true;
	return $ret;
}
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
function users_foldercheck($p_arr = array()){
	global $users_path;
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $users_path)){ 
		mkdir(cs_path . DIRECTORY_SEPARATOR . $users_path); 
		chmod(cs_path . DIRECTORY_SEPARATOR . $users_path, 0755);
	}
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $users_path . DIRECTORY_SEPARATOR . '.htaccess')){
		$out			= fopen(cs_path . DIRECTORY_SEPARATOR . $users_path . DIRECTORY_SEPARATOR . '.htaccess', 'w+');
		fwrite($out,"deny from all\n<Files ~ '^.+\\.(xls|xlsx)$'>\norder deny,allow\nallow from all\n</Files>");
		fclose($out);			
	}
	$datetime = new DateTime();
	$timestamp = $datetime->getTimestamp();
	$dt_year = date("Y", $timestamp);
	$dt_month = date("m", $timestamp);
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $users_path . DIRECTORY_SEPARATOR . $dt_year)){ 
		mkdir(cs_path . DIRECTORY_SEPARATOR . $users_path . DIRECTORY_SEPARATOR . $dt_year); 
		chmod(cs_path . DIRECTORY_SEPARATOR . $users_path . DIRECTORY_SEPARATOR . $dt_year, 0755);
	}
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $users_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month)){ 
		mkdir(cs_path . DIRECTORY_SEPARATOR . $users_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month); 
		chmod(cs_path . DIRECTORY_SEPARATOR . $users_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month, 0755);
	}
	$GLOBALS['users_path_curent'] = $users_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month;
}
function users_xls_import($p_arr = array()){
	users_foldercheck();
	global $users_path_curent;
	$ret = array('success'=>false,'resp'=>array());
	require_once __DIR__ . '/../PhpSpreadsheet-develop/src/Bootstrap.php';
	$ret['p_arr'] = $p_arr;
	$input = file_get_contents('php://input');
	if (strlen($input) == 0) {$ret['error'] = 'empty'; return $ret;}
	$ret['resp']['fname'] = uniqid() . '.xls';
	file_put_contents($users_path_curent . DIRECTORY_SEPARATOR . $ret['resp']['fname'], $input);
	
	$spreadsheet = IOFactory::load($users_path_curent . DIRECTORY_SEPARATOR . $ret['resp']['fname']);
	$ret['users_parse'] =  cs('users/parse',array("spreadsheet"=>$spreadsheet));
	if (!isset($ret['users_parse']['success']) || ($ret['users_parse']['success'] != true)) {
		return $ret;
	}
	$ret['resp']['users_parse'] = $ret['users_parse']['resp'];
	$ret['success'] = true;
	return $ret;
}
function users_paramsfromfile($p_arr){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	users_foldercheck();
	global $users_path_curent;
	require_once __DIR__ . '/../PhpSpreadsheet-develop/src/Bootstrap.php';	

	if (!isset($p_arr['spreadsheet'])){ 
		if (!isset($p_arr['fname'])){ $ret['error'] = 'check param - fname'; return $ret;}
		$spreadsheet = IOFactory::load($users_path_curent . DIRECTORY_SEPARATOR . $p_arr['fname']);
	}else{
		$spreadsheet = $p_arr['spreadsheet'];
	}
	
	for ($i = 1; $i < 20; $i++){
		$ret['cv'] = $spreadsheet->setActiveSheetIndex(1)->getCell('A'.$i)->getValue();
		if ($ret['cv'] != ''){
			$ret['resp'][$ret['cv']] = $spreadsheet->setActiveSheetIndex(1)->getCell('B'.$i)->getValue();
		}else{
			break;
		}
	}
	$ret['success'] = true;
	return $ret;
}
function users_parse($p_arr){
	$ret = array('success'=>false,'resp'=>array('addcount'=>0));
	users_foldercheck();
	global $users_path_curent;
	require_once __DIR__ . '/../PhpSpreadsheet-develop/src/Bootstrap.php';	
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');

	if (!isset($p_arr['spreadsheet'])){ 
		if (!isset($p_arr['fname'])){ $ret['error'] = 'check param - fname'; return $ret;}
		$spreadsheet = IOFactory::load($users_path_curent . DIRECTORY_SEPARATOR . $p_arr['fname']);
	}else{
		$spreadsheet = $p_arr['spreadsheet'];
	}
	
	$ret['users_paramsfromfile'] =  cs('planificare/paramsfromfile',array("spreadsheet"=>$spreadsheet));
	if (!isset($ret['users_paramsfromfile']['success']) || ($ret['users_paramsfromfile']['success'] != true)) {return $ret;}
	$ret['key'] = md5($ret['users_paramsfromfile']['resp']['spital'] 
		. 'poscrt1222'
	);
	if ($ret['key'] != $ret['users_paramsfromfile']['resp']['key']) {$ret['error'] = 'security'; return $ret;}
	
	$ret['doctori_sql'] = 'SELECT SQL_CALC_FOUND_ROWS users.id';
	$ret['doctori_sql'] .= '	, users.nume ';
	$ret['doctori_sql'] .= '	, users.email ';
	$ret['doctori_sql'] .= ' FROM spitale_users';
	$ret['doctori_sql'] .= ' LEFT JOIN users ON spitale_users.user = users.id';
	$ret['doctori_sql'] .= ' WHERE spitale_users.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($ret['users_paramsfromfile']['resp']['spital']);
	$ret['doctori_sql'] .= ' ORDER BY users.id ASC';
	
	$ret['doctori'] = cs("_cs_grid/get",array('db_sql'=>$ret['doctori_sql']));
	if (!isset($ret['doctori']['success']) || ($ret['doctori']['success'] != true)) {return $ret;}
	$ret['row'] = $ret['doctori']['resp']['records'];

	$ret['nume'] = $spreadsheet->setActiveSheetIndex(0)->getCell('B'.(7 + $ret['row']))->getValue();

	while($ret['nume'] != ''){
		$ret['email'] = $spreadsheet->getActiveSheet(0)->getCell('C'.(7 + $ret['row']))->getValue();
		$ret['users_update_param'] = array(
			'oper'=>'add'
			,'id'=>null
			,'nume'=>$ret['nume']
		);
		if ($ret['email'] != '') $ret['users_update_param']['email'] = $ret['email'];
		$ret['users_update'] = users_update($ret['users_update_param']);
		if (!isset($ret['users_update']['success'])||($ret['users_update']['success'] != true)){return $ret;}
		$ret['spitale_users_update_param'] = array(
			'oper'=>'add'
			,'id'=>null
			,'level'=>user_level_doctor
			,'spital'=>$ret['users_paramsfromfile']['resp']['spital']
			,'user'=>$ret['users_update']['resp']['id']
		);
		$ret['spitale_users_update'] = cs('spitale_users/update',$ret['spitale_users_update_param']);
		if (!isset($ret['spitale_users_update']['success'])||($ret['spitale_users_update']['success'] != true)){return $ret;}

		$ret['row']++;
		$ret['resp']['addcount']++;
		$ret['nume'] = $spreadsheet->getActiveSheet(0)->getCell('B'.(7 + $ret['row']))->getValue();
	}
	$ret['success'] = true;
	return $ret;
}
function users_xls_export($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array());
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	if (!isset($_SESSION['cs_users_id'])){$ret['error'] = 'login first'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	
	$ret['spitale_users_getlevel'] = cs('spitale_users/getlevel',array('spital'=>$p_arr['spital']));
	if (!isset($ret['spitale_users_getlevel']['success']) || ($ret['spitale_users_getlevel']['success'] != true)) {return $ret;}
	if ($ret['spitale_users_getlevel']['resp'] < user_level_manager){$ret['error'] = 'level min manager required'; return $ret;}

	$ret['spitale_get'] = cs('spitale/get',array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['spital']),
	))));
	if ($ret['spitale_get'] == null) { $ret['error'] = 'no such'; return $ret;}

	$ret['doctori_sql'] = 'SELECT SQL_CALC_FOUND_ROWS users.id';
	$ret['doctori_sql'] .= '	, users.nume ';
	$ret['doctori_sql'] .= '	, users.email ';
	$ret['doctori_sql'] .= ' FROM spitale_users';
	$ret['doctori_sql'] .= ' LEFT JOIN users ON spitale_users.user = users.id';
	$ret['doctori_sql'] .= ' WHERE spitale_users.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
	$ret['doctori_sql'] .= ' ORDER BY users.id ASC';
	
	$ret['doctori'] = cs("_cs_grid/get",array('db_sql'=>$ret['doctori_sql']));
	if (!isset($ret['doctori']['success']) || ($ret['doctori']['success'] != true)) {return $ret;}

	require_once __DIR__ . '/../PhpSpreadsheet-develop/src/Bootstrap.php';	
	$spreadsheet = IOFactory::load(__DIR__ . '/../template/potemplatedoctori.xls');
	// Add some data
	$spreadsheet->setActiveSheetIndex(1)
		->setCellValue('A1', 'spital')
		->setCellValue('B1',  $ret['spitale_get']['id'])
		->setCellValue('A2', 'key')
		->setCellValue('B2',  md5($ret['spitale_get']['id'] . 'poscrt1222'))
	;
	$spreadsheet->setActiveSheetIndex(0)
		->setCellValue('A1', 'Nume unitate: ' . $ret['spitale_get']['nume'])
		->setCellValue('A2', 'Lista doctorilor ')
		->setCellValue('A3', 'Generat pe data de: ' . date("d.m.Y"))
	;

	for ($di = 0; $di < $ret['doctori']['resp']['records']; $di++){
		$spreadsheet->getActiveSheet()->setCellValue('A'.($di + 7), $di + 1);
		$spreadsheet->getActiveSheet()->setCellValue('B'.($di + 7), $ret['doctori']['resp']['rows'][$di]->nume);
		$spreadsheet->getActiveSheet()->setCellValue('C'.($di + 7), $ret['doctori']['resp']['rows'][$di]->email);
	}
	if (isset($p_arr['debug'])) {
		$ret['success'] = true;
		return $ret;
	}
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$spreadsheet->setActiveSheetIndex(0);
	
	// Redirect output to a clientâ€™s web browser (Xls)
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="doctori' 
		. '.xls"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
	header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header('Pragma: public'); // HTTP/1.0

	$writer = IOFactory::createWriter($spreadsheet, 'Xls');
	$writer->save('php://output');
	exit;
}
?>