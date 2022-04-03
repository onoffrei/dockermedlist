<?php
function inscriere_demodoctor($p_arr = array()){
	$ret = array('success'=>false,'error'=>'', 'resp'=>array());
	if (!isset($p_arr['nume']) || $p_arr['nume'] == ''){ $ret['error'] = 'check param - nume'; return $ret;}
	if (!isset($p_arr['spital']) || $p_arr['spital'] == ''){ $ret['error'] = 'check param - spital'; return $ret;}
	
	
	$ret['users_update'] = cs('users/update',array(
		'oper'=>'add',
		'id'=>'null',
		'nume'=>$p_arr['nume'],
		'telefon'=>'1234',
	));
	if (!isset($ret['users_update']['success'])||($ret['users_update']['success'] != true)){return $ret;}
	
	$ret['spitale_users_update'] = cs('spitale_users/update',array(
		'oper'=>'add',
		'id'=>'null',
		'spital'=>$p_arr['spital'],
		'user'=>$ret['users_update']['resp']['id'],
		'level'=>2,
	));
	if (!isset($ret['spitale_users_update']['success'])||($ret['spitale_users_update']['success'] != true)){return $ret;}
	
	
	$ret['success'] = true;
	return $ret;
}
function inscriere_demolegenda($p_arr = array()){
	$ret = array('success'=>false,'error'=>'', 'resp'=>array());
	if (!isset($p_arr['spital']) || $p_arr['spital'] == ''){ $ret['error'] = 'check param - spital'; return $ret;}
	$ret['legenda_update'] = cs('legenda/update',array(
		'oper'=>'add',
		'id'=>'null',
		'nume'=>'p',
		'spital'=>$p_arr['spital'],
		'start'=>'08:00:00',
		'stop'=>'14:00:00',
	));
	if (!isset($ret['legenda_update']['success'])||($ret['legenda_update']['success'] != true)){return $ret;}	
	$ret['legenda_update'] = cs('legenda/update',array(
		'oper'=>'add',
		'id'=>'null',
		'nume'=>'p1',
		'spital'=>$p_arr['spital'],
		'start'=>'14:00:00',
		'stop'=>'17:00:00',
	));
	if (!isset($ret['legenda_update']['success'])||($ret['legenda_update']['success'] != true)){return $ret;}	
	
	$ret['success'] = true;
	return $ret;
}


function inscriere_demoplanificare($p_arr = array()){
	$ret = array('success'=>false,'error'=>'', 'resp'=>array());
	if (!isset($p_arr['spital']) || $p_arr['spital'] == ''){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($p_arr['doctorids']) || $p_arr['doctorids'] == ''){ $ret['error'] = 'check param - doctorids'; return $ret;}
	$ret['paramprep'] = array(
		'y'=>date("Y"),
		'm'=>date("m"),
		'spital'=>$p_arr['spital'],
		'planificare'=>array(),
	);
	$ret['stop'] = intval(date("t"));
	foreach($p_arr['doctorids'] as $doctorid){
		$day = array();
		for ($d = 1; $d <= $ret['stop']; $d++){
			$ret['n'] = date('N',strtotime(date('Y-m-' . $d)));
			if ($ret['n'] < 6)
			$day[] = array(
				'd'=>$d,
				'legenda'=>'p',
			);
		}
		$ret['paramprep']['planificare'][] = array('doctorid'=>$doctorid, 'days'=>$day);
	}
	$ret['planificare_save'] = cs('planificare/save',$ret['paramprep']);
	if (!isset($ret['planificare_save']['success'])||($ret['planificare_save']['success'] != true)){return $ret;}	
	$ret['success'] = true;
	return $ret;
}



function inscriere_adauga($p_arr = array()){
	$ret = array('success'=>false,'error'=>'', 'resp'=>array());
	if (!isset($_SESSION['cs_users_id'])){
		$ret['error'] = 'login first'; return $ret;
	}
	if (!isset($p_arr['nume']) || $p_arr['nume'] == ''){ $ret['error'] = 'check param - nume'; return $ret;}
	if (!isset($p_arr['localitate']) || $p_arr['localitate'] == ''){ $ret['error'] = 'check param - localitate'; return $ret;}
	if (!isset($p_arr['descriere']) || $p_arr['descriere'] == ''){ $ret['error'] = 'check param - descriere'; return $ret;}
	if (!isset($p_arr['telefon']) || $p_arr['telefon'] == ''){ $ret['error'] = 'check param - telefon'; return $ret;}
	if (!isset($p_arr['adresa']) || $p_arr['adresa'] == ''){ $ret['error'] = 'check param - adresa'; return $ret;}

	$ret['spitale_update'] = cs('spitale/update',array(
		'oper'=>'add',
		'id'=>'null',
		'nume'=>$p_arr['nume'],
		'descriere'=>$p_arr['descriere'],
		'telefon'=>$p_arr['telefon'],
		'adresa'=>$p_arr['adresa'],
		'activman'=>1,
	));
	if (!isset($ret['spitale_update']['success'])||($ret['spitale_update']['success'] != true)){return $ret;}
	
	$ret['localitati_breadcrumb'] = cs('localitati/breadcrumb', array('locid'=>$p_arr['localitate']));
	if (!isset($ret['localitati_breadcrumb']['success'])||($ret['localitati_breadcrumb']['success'] != true)){return $ret;}
	
	foreach($ret['localitati_breadcrumb']['resp']['nodearr'] as $localitate){
		$ret['localitati_spitale_update'] = cs('localitati_spitale/update',array(
			'oper'=>'add',
			'id'=>'null',
			'spital'=>$ret['spitale_update']['resp']['id'],
			'localitate'=>$localitate['id'],
		));
		if (!isset($ret['localitati_spitale_update']['success'])||($ret['localitati_spitale_update']['success'] != true)){return $ret;}
	}
	
	$ret['spitale_users_update'] = cs('spitale_users/update',array(
		'oper'=>'add',
		'id'=>'null',
		'user'=>$_SESSION['cs_users_id'],
		'spital'=>$ret['spitale_update']['resp']['id'],
		'level'=>user_level_manager,
	));
	if (!isset($ret['spitale_users_update']['success'])||($ret['spitale_users_update']['success'] != true)){return $ret;}	
	
	/*
	demo doctor anulat
	$ret['doctorids'] = array();
	$ret['inscriere_demodoctor'] = cs('inscriere/demodoctor',array(
		'nume'=>'demodoctor1',
		'spital'=>$ret['spitale_update']['resp']['id'],
	));
	if (!isset($ret['inscriere_demodoctor']['success'])||($ret['inscriere_demodoctor']['success'] != true)){return $ret;}
	$ret['doctorids'][] = $ret['inscriere_demodoctor']['users_update']['resp']['id'];
	
	$ret['inscriere_demodoctor'] = cs('inscriere/demodoctor',array(
		'nume'=>'demodoctor2',
		'spital'=>$ret['spitale_update']['resp']['id'],
	));
	if (!isset($ret['inscriere_demodoctor']['success'])||($ret['inscriere_demodoctor']['success'] != true)){return $ret;}
	$ret['doctorids'][] = $ret['inscriere_demodoctor']['users_update']['resp']['id'];
	*/
	/*
	$ret['inscriere_demolegenda'] = cs('inscriere/demolegenda',array('spital'=>$ret['spitale_update']['resp']['id']));
	if (!isset($ret['inscriere_demolegenda']['success'])||($ret['inscriere_demolegenda']['success'] != true)){return $ret;}
	*/
	/*
	//demoplanificarea nu e valida, docotorii de demo nu au specilizari
	$ret['inscriere_demoplanificare'] = cs('inscriere/demoplanificare',array(
		'spital'=>$ret['spitale_update']['resp']['id'],
		'doctorids'=>$ret['doctorids'],
	));
	if (!isset($ret['inscriere_demoplanificare']['success'])||($ret['inscriere_demoplanificare']['success'] != true)){return $ret;}
	*/
	
	$ret['push_send_user'] = cs('push/send_user',array(
		'user'=>1,
		'message'=>array(
			'title'=>'Medlist Unitate Noua',
			'message'=>'O unitate noua asteapta aprobarea'
		),
	));
	$ret['inscriere_mailtoadmin'] = cs('inscriere/mailtoadmin');
	

	
	$ret['success'] = true;
	return $ret;
}
function inscriere_mailtoadmin($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'', 'log'=>array());
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	$ret['admin_sql'] = "SELECT ";
	$ret['admin_sql'] .= "    users.id as id";
	$ret['admin_sql'] .= "    ,users.email as email";
	$ret['admin_sql'] .= "    ,users.nume as nume";
	$ret['admin_sql'] .= "    ,users.uri as uri";
	$ret['admin_sql'] .= "    ,users.image as image";
	$ret['admin_sql'] .= " FROM spitale_users";
	$ret['admin_sql'] .=  " LEFT JOIN users ON spitale_users.user = users.id";
	$ret['admin_sql'] .=  " WHERE spitale_users.level = " . $GLOBALS['cs_db_conn']->real_escape_string(user_level_admin);
	$ret['admin_sql'] .=  " AND users.email IS NOT NULL ";

	$ret['admin'] = cs("_cs_grid/get",array('db_sql'=>$ret['admin_sql']));
	if (!isset($ret['admin']['success']) || ($ret['admin']['success'] != true)) {$ret['error'] = 'admin??';return $ret;}
	if ($ret['admin']['resp']['records'] == 0) {$ret['error'] = 'admin empty??';return $ret;}
	
	set_error_handler(function ($severity, $message, $file, $line) {
		throw new \ErrorException($message, $severity, $severity, $file, $line);
	});
	foreach($ret['admin']['resp']['rows'] as $admin){
		$headers = 'MIME-Version: 1.0' . "\r\n"
			. 'Content-type: text/html; charset=iso-8859-1' . "\r\n"
			. 'From: ' . cs_email . "\r\n"
			. 'Reply-To: ' . cs_email . "\r\n"
			. 'X-Mailer: PHP/' . phpversion();
		$message = 		"Salut " . $admin->email . " \n<br>"
			. "Ai primit acest email deoarece o noua unitate s-a inscris pe <a href='" . cs_url . "'>" . cs_url . "</a> . \n<br>"
			. "Mesaj generat automat, te rugam nu da reply.\n<br>"
			. "\n<br>"
			. "Cu stima,\n<br>"
			. "Echipa, <a href='" . cs_url . "'>" . cs_url . "</a>\n<br>";		 
		try {
			mail($admin->email , 'A fost adaugata o noua unitate', $message,$headers);
		}catch (\Throwable | \Warrning | \Error | \Exception $e) {
			$ret['log'][] = array(
				'email'=>$admin->email,
				'error'=>$e->getMessage(),
			); 
		}
	}
	restore_error_handler();
	$ret['success'] = true;
	return $ret;
}
function inscriere_adauga_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	$ret['localitati_grid'] = cs('localitati/grid',array(
		"filters"=>array("rules"=>array(
			array("field"=>"parent","op"=>"eq","data"=>0),
		)),
		'rows'=>50,
		'sord'=>'asc',
		'sidx'=>'denumire',
	));
	if (!isset($ret['localitati_grid']['success']) || ($ret['localitati_grid']['success'] != true)) {return $ret;}	
	ob_start(); 
	?> <div id="inscriere_adauga_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Adauga spital/clinica/cabinet</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="inscriere_adauga_modal_action(this)" id="inscriere_adauga_modal_form" autocomplete="on">
						<div class="row">
							<?php if (!isset($_SESSION['cs_users_id'])){?>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Nume manager</label>
									<input type="text" name="nume" class="form-control" placeholder="nume persoana de contact" value=''>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Telefon</label>
									<input type="text" name="telefon" class="form-control" placeholder="Telefon" value=''>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Email</label>
									<input type="text" name="email" class="form-control" placeholder="Email" autocomplete="off" value=''>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Parola</label>
									<input type="password" name="password" class="form-control"  autocomplete="new-password" value=''>
								</div>
							</div>
							<?php }?>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Denumire spital/clinica/cabinet</label>
									<input type="text" name="spital" class="form-control" placeholder="Denumire spital/clinica/cabinet" value=''>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Localitate</label>
									<select name="localitate" class="form-control">
										<option value="">Alege localitate</option>
										<?php foreach ($ret['localitati_grid']['resp']['rows'] as $localitate){?>
										<option value="<?php echo $localitate->id?>"><?php echo $localitate->denumire?></option>
										<?php }?>
									</select>
								</div>
							</div>
							
							<div class="col-xs-12">
								<button type="submit" class="btn btn-success btn-block">Adauga</button>
							</div>
						</div>
					</form>
				
				<div class="modal-footer">
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal --> <script>
		$("div.modal-backdrop.fade.in").remove()
		$("#inscriere_adauga_modal").modal('show')
		inscriere_adauga_modal_action = function(form){
			cs('inscriere/adauga',new FormData(form)).then(function(inscriere_adauga){
				console.log(inscriere_adauga)
				$("#inscriere_adauga_modal").modal('hide')
				if (typeof(inscriere_adauga.success) == 'undefined' || inscriere_adauga.success != true) {
					alert('salvare nereusita')
					return
				}else{
					alert('inregistrare reusita')
					//window.location.reload()
				}
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
?>