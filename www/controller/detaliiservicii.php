<?php
$GLOBALS['detaliiservicii_cols'] = array(
	'id'				=>array('type'=>'int',),
	'doctor'			=>array('type'=>'int',),
	'spital'			=>array('type'=>'int',),
	'specializare'		=>array('type'=>'int',),
	'numedetaliu'		=>array('type'=>'text',),
	'valoaredetaliu'	=>array('type'=>'int',),
); 
function detaliiservicii_get($p_arr = array()){
	$ret = null;
	$list = detaliiservicii_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["doctor"] = $list['resp']['rows'][0]->doctor;
		$ret["spital"] = $list['resp']['rows'][0]->spital;
		$ret["specializare"] = intval($list['resp']['rows'][0]->specializare);
		$ret["numedetaliu"] = intval($list['resp']['rows'][0]->numedetaliu);
		$ret["valoaredetaliu"] = intval($list['resp']['rows'][0]->valoaredetaliu);
	}
	return $ret;
}
function detaliiservicii_grid($p_arr = array()){
	global $detaliiservicii_cols;
	$p_arr['db_cols'] = $detaliiservicii_cols;
	$p_arr['db_table'] = 'detaliiservicii';
	return cs("_cs_grid/get",$p_arr);
}
function detaliiservicii_modificatimp_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}

	$ret['detaliiservicii'] = cs('detaliiservicii/get', array("filters"=>array("rules"=>array(
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"numedetaliu","op"=>"eq","data"=>'timp'),
	))));
	
	ob_start(); 
	?> <div id="detaliiservicii_modificatimp_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica Timp</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="detaliiservicii_modificatimp_modal_action(this)" id="detaliiservicii_modificatimp_modal_form" >
						<input type="hidden" name="doctor" value='<?php echo $p_arr['doctor'];?>'>
						<input type="hidden" name="spital" value='<?php echo $p_arr['spital'];?>'>
						<div class="col-xs-12 ">
							<div class="form-group">
								<label>Timp</label>
								<input type="number" name="timp" class="form-control" placeholder="Timp" value='<?php if($ret['detaliiservicii']!=null) echo $ret['detaliiservicii']['valoaredetaliu'] ?>'>
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
		$("#detaliiservicii_modificatimp_modal").modal('show')
		detaliiservicii_modificatimp_modal_action = function(form){
			cs('detaliiservicii/modificatimp',new FormData(form)).then(function(d){
				//console.log(d)
				$("#detaliiservicii_modificatimp_modal").modal('hide')
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
function detaliiservicii_modificatimp($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($p_arr['timp']) || ($p_arr['timp'] == '')){ $ret['error'] = 'check param - timp'; return $ret;}
	
	$ret['detaliiservicii'] = cs('detaliiservicii/get', array("filters"=>array("rules"=>array(
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"numedetaliu","op"=>"eq","data"=>'timp'),
	))));
	
	if ($ret['detaliiservicii'] == null){
		$ret['sql'] = 'INSERT INTO detaliiservicii (doctor,spital,numedetaliu,valoaredetaliu) VALUES (';
		$ret['sql'] .= $GLOBALS['cs_db_conn']->real_escape_string($p_arr['doctor']);
		$ret['sql'] .= ', ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
		$ret['sql'] .= ', "timp"';
		$ret['sql'] .= ', ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['timp']);
		$ret['sql'] .= ')';
		$ret['db_resp'] = cs("_cs_grid/get",array('db_sql'=>$ret['sql']));
		if (!isset($ret['db_resp']['success'])||($ret['db_resp']['success'] != true)){return $ret;}
			$ret['resp'] = $ret['db_resp']['resp'];
			$ret['success'] = true;
			return $ret;
	}else{
		$ret['sql'] = 'UPDATE detaliiservicii SET ';
		$ret['sql'] .= ' valoaredetaliu=' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['timp']);
		$ret['sql'] .= ' WHERE ';
		$ret['sql'] .= ' doctor='.$GLOBALS['cs_db_conn']->real_escape_string($p_arr['doctor']);
		$ret['sql'] .= ' AND spital=' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
		$ret['sql'] .= ' AND numedetaliu="timp"';
		
		
		$ret['db_resp'] = cs("_cs_grid/get",array('db_sql'=>$ret['sql']));
		if (!isset($ret['db_resp']['success'])||($ret['db_resp']['success'] != true)){return $ret;}
			$ret['resp'] = $ret['db_resp']['resp'];
			$ret['success'] = true;
			return $ret;
	}
}

function detaliiservicii_modificapret_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($p_arr['specializare'])){ $ret['error'] = 'check param - specializare'; return $ret;}

	$ret['detaliiservicii'] = cs('detaliiservicii/get', array("filters"=>array("rules"=>array(
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"numedetaliu","op"=>"eq","data"=>'valoare'),
		array("field"=>"specializare","op"=>"eq","data"=>$p_arr['specializare']),
	))));
	
	ob_start(); 
	?> <div id="detaliiservicii_modificapret_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica Pret</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="detaliiservicii_modificapret_modal_action(this)" id="detaliiservicii_modificapret_modal_form" >
						<input type="hidden" name="doctor" value='<?php echo $p_arr['doctor'];?>'>
						<input type="hidden" name="spital" value='<?php echo $p_arr['spital'];?>'>
						<input type="hidden" name="specializare" value='<?php echo $p_arr['specializare'];?>'>
						<div class="col-xs-12 ">
							<div class="form-group">
								<label>Pret</label>
								<input type="number" name="pret" class="form-control" placeholder="Pret" value='<?php if($ret['detaliiservicii']!=null) echo $ret['detaliiservicii']['valoaredetaliu'] ?>'>
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
		$("#detaliiservicii_modificapret_modal").modal('show')
		detaliiservicii_modificapret_modal_action = function(form){
			cs('detaliiservicii/modificapret',new FormData(form)).then(function(d){
				//console.log(d)
				$("#detaliiservicii_modificapret_modal").modal('hide')
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
function detaliiservicii_modificapret($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($p_arr['specializare'])){ $ret['error'] = 'check param - specializare'; return $ret;}
	if (!isset($p_arr['pret']) || ($p_arr['pret'] == '')){ $ret['error'] = 'check param - pret'; return $ret;}
	
	$ret['detaliiservicii'] = cs('detaliiservicii/get', array("filters"=>array("rules"=>array(
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"specializare","op"=>"eq","data"=>$p_arr['specializare']),
		array("field"=>"numedetaliu","op"=>"eq","data"=>'valoare'),
	))));
	
	if ($ret['detaliiservicii'] == null){
		$ret['sql'] = 'INSERT INTO detaliiservicii (doctor,spital,specializare,numedetaliu,valoaredetaliu) VALUES (';
		$ret['sql'] .= $GLOBALS['cs_db_conn']->real_escape_string($p_arr['doctor']);
		$ret['sql'] .= ', ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
		$ret['sql'] .= ', ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['specializare']);
		$ret['sql'] .= ', "valoare"';
		$ret['sql'] .= ', ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['pret']);
		$ret['sql'] .= ')';
		$ret['db_resp'] = cs("_cs_grid/get",array('db_sql'=>$ret['sql']));
		if (!isset($ret['db_resp']['success'])||($ret['db_resp']['success'] != true)){return $ret;}
			$ret['resp'] = $ret['db_resp']['resp'];
			$ret['success'] = true;
			return $ret;
	}else{
		$ret['sql'] = 'UPDATE detaliiservicii SET ';
		$ret['sql'] .= ' valoaredetaliu=' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['pret']);
		$ret['sql'] .= ' WHERE ';
		$ret['sql'] .= ' doctor='.$GLOBALS['cs_db_conn']->real_escape_string($p_arr['doctor']);
		$ret['sql'] .= ' AND spital=' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
		$ret['sql'] .= ' AND specializare=' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['specializare']);
		$ret['sql'] .= ' AND numedetaliu="valoare"';
		
		
		$ret['db_resp'] = cs("_cs_grid/get",array('db_sql'=>$ret['sql']));
		if (!isset($ret['db_resp']['success'])||($ret['db_resp']['success'] != true)){return $ret;}
			$ret['resp'] = $ret['db_resp']['resp'];
			$ret['success'] = true;
			return $ret;
	}
}



?>