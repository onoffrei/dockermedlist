<?php
$GLOBALS['legenda_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'nume'				=>array('type'=>'text',),
	'spital'			=>array('type'=>'int',),
	'start'				=>array('type'=>'time',),
	'stop'				=>array('type'=>'time',),
);
function legenda_get($p_arr = array()){
	$ret = null;
	$list = legenda_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["nume"] = $list['resp']['rows'][0]->nume;
		$ret["spital"] = intval($list['resp']['rows'][0]->spital);
		$ret["start"] = $list['resp']['rows'][0]->start;
		$ret["stop"] = $list['resp']['rows'][0]->stop;
	}
	return $ret;
}
function legenda_grid($p_arr = array()){
	global $legenda_cols;
	$p_arr['db_cols'] = $legenda_cols;
	$p_arr['db_table'] = 'legenda';
	return cs("_cs_grid/get",$p_arr);
}
function legenda_update($p_arr = array()){
	global $legenda_cols;
	$p_arr['db_cols'] = $legenda_cols;
	$p_arr['db_table'] = 'legenda';
	return cs("_cs_grid/update",$p_arr);
}

function legenda_modifica_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}

	$ret['legenda_get'] = cs('legenda/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['legenda_get'] == null) {return $ret;}

	ob_start(); 
	?> <div id="legenda_modifica_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica palier orar</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="legenda_modifica_modal_action(this)" id="legenda_modifica_modal_form" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<input type="hidden" name="spital" value='<?php echo $p_arr['spital'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>nume palier orar</label>
									<input type="text" name="nume" class="form-control" placeholder="nume" value='<?php echo $ret['legenda_get']['nume']?>'>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>ora de inceput</label>
								<!--	<input type="text" name="start" class="form-control" placeholder="start" value='<?php //echo $ret['legenda_get']['start']?>'>-->
									
									<select name="start" class="form-control">									
									<?php
										$steps   = 30; // only edit the minutes value
										$current = 0;
										$loops   = 22*(60/$steps);

										for ($i = 14; $i <= $loops; $i++) {
											$time = sprintf('%02d:%02d', $i/(60/$steps), $current%60);
											
											
											$time2=$time.":00";
											//pt selected
											$selected = '';
											if ($ret['legenda_get']['start'] == $time2) $selected = 'selected';
											//
											?>
											<option value="<?php echo $time;?>" <?php echo $selected;?>><?php echo $time;?></option>
											<?php
											
											$current += $steps;
										}
									?>
									</select>	
									
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>ora de sfarsit</label>
									<!--<input type="text" name="stop" class="form-control" placeholder="stop" value='<?php //echo $ret['legenda_get']['stop']?>'>-->
									
									<select name="stop" class="form-control">									
									<?php
										$ret['legenda_grid'] = cs('legenda/grid',array("filters"=>array("rules"=>array(
											array("field"=>"id","op"=>"eq","data"=>$p_arr['id']),
										))));
										cscheck($ret['legenda_grid']);
									
										$steps   = 30; // only edit the minutes value
										$current = 0;
										$loops   = 22*(60/$steps);

										for ($i = 14; $i <= $loops; $i++) {
											$time = sprintf('%02d:%02d', $i/(60/$steps), $current%60);
											
											$time2=$time.":00";
											//pt selected
											$selected = '';
											if ($ret['legenda_get']['stop'] == $time2) $selected = 'selected';
											//
											?>
											<option value="<?php echo $time;?>" <?php echo $selected;?>><?php echo $time;?></option>
											<?php
											$current += $steps;
										}
									?>
									</select>
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
		$("#legenda_modifica_modal").modal('show')
		legenda_modifica_modal_action = function(form){
			cs('legenda/modifica',new FormData(form)).then(function(legenda_modifica){
				//console.log(d)
				$("#legenda_modifica_modal").modal('hide')
				if (typeof(legenda_modifica.success) == 'undefined' || legenda_modifica.success != true) {
					if ((typeof(legenda_modifica.error)!= 'undefined') && (legenda_modifica.error != '')){
						alert(legenda_modifica.error)
					}else{
						alert('salvare nereusita')						
					}
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
function legenda_modifica($p_arr = array()){
	$ret = array('success'=>false);
	if (!isset($p_arr['nume']) || $p_arr['nume'] == ''){ $ret['error'] = 'check param - nume'; return $ret;}
	$p_arr['nume'] = strtolower($p_arr['nume']);
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($p_arr['start']) || $p_arr['start'] == ''){ $ret['error'] = 'check param - start'; return $ret;}
	if (!isset($p_arr['stop']) || $p_arr['stop'] == ''){ $ret['error'] = 'check param - stop'; return $ret;}
	if(strtotime($p_arr['start']) >= strtotime($p_arr['stop'])){$ret['error'] = 'invalid interval';return $ret;}
	
	$ret['legenda_get'] = cs('legenda/get',array("filters"=>array("rules"=>array(
		array("field"=>"nume","op"=>"eq","data"=>$p_arr['nume']),
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"id","op"=>"ne","data"=>$p_arr['id']),
	))));
	if ($ret['legenda_get'] != null){$ret['error'] = 'Allready in';return $ret;}
	
	$p_arr['oper'] = 'edit';
	$ret['legenda_update'] = legenda_update($p_arr);
	if (!isset($ret['legenda_update']['success'])||($ret['legenda_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function legenda_sterge($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$p_arr['oper'] = 'del';
	$ret['legenda_update'] = legenda_update($p_arr);
	if (!isset($ret['legenda_update']['success'])||($ret['legenda_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function legenda_adauga_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	ob_start(); 
	?> <div id="legenda_adauga_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Adauga Palier Orar</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="legenda_adauga_modal_action(this)" id="legenda_adauga_modal_form" >
						<input type="hidden" name="spital" value='<?php echo $p_arr['spital']?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Nume</label>
									<input type="text" name="nume" class="form-control" placeholder="nume" value=''>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Ora de inceput</label>
								<!--<input type="text" name="start" class="form-control" placeholder="start" value=''>-->
									<select name="start" class="form-control">
									<?php
										$steps   = 30; // only edit the minutes value
										$current = 0;
										$loops   = 22*(60/$steps);

										for ($i = 14; $i <= $loops; $i++) {
											$time = sprintf('%02d:%02d', $i/(60/$steps), $current%60);
											echo '<option>'.$time.'</option>';
											$current += $steps;
										}
									?>
									</select>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Ora de sfarsit</label>
									<!--<input type="text" name="stop" class="form-control" placeholder="stop" value=''>-->
									<select name="stop" class="form-control">
									<?php
										$steps   = 30; // only edit the minutes value
										$current = 0;
										$loops   = 22*(60/$steps);

										for ($i = 14; $i <= $loops; $i++) {
											$time = sprintf('%02d:%02d', $i/(60/$steps), $current%60);
											echo '<option>'.$time.'</option>';
											$current += $steps;
										}
									?>
									</select>									
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
		$("#legenda_adauga_modal").modal('show')
		legenda_adauga_modal_action = function(form){
			cs('legenda/adauga',new FormData(form)).then(function(legenda_adauga){
				console.log(legenda_adauga)
				$("#legenda_adauga_modal").modal('hide')
				if (typeof(legenda_adauga.success) == 'undefined' || legenda_adauga.success != true) {
					if ((typeof(legenda_adauga.error)!= 'undefined') && (legenda_adauga.error != '')){
						alert(legenda_adauga.error)
					}else{
						alert('salvare nereusita')						
					}
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
function legenda_adauga($p_arr = array()){
	$ret = array('success'=>false);
	if (!isset($p_arr['nume']) || $p_arr['nume'] == ''){ $ret['error'] = 'check param - nume'; return $ret;}
	$p_arr['nume'] = strtolower($p_arr['nume']);
	if (!isset($p_arr['start']) || $p_arr['start'] == ''){ $ret['error'] = 'check param - start'; return $ret;}
	if (!isset($p_arr['stop']) || $p_arr['stop'] == ''){ $ret['error'] = 'check param - stop'; return $ret;}
	if (!isset($p_arr['spital']) || $p_arr['spital'] == ''){ $ret['error'] = 'check param - spital'; return $ret;}
	
	if(strtotime($p_arr['start']) >= strtotime($p_arr['stop'])){$ret['error'] = 'invalid interval';return $ret;}
	
	$ret['legenda_get'] = cs('legenda/get',array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"nume","op"=>"eq","data"=>$p_arr['nume']),
	))));
	if ($ret['legenda_get'] != null){$ret['error'] = 'Allready in';return $ret;}
	
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$ret['legenda_update'] = legenda_update($p_arr);
	if (!isset($ret['legenda_update']['success'])||($ret['legenda_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function legenda_adaugainterval($p_arr = array()){
	$ret = array('success'=>false);
	if (!isset($p_arr['start']) || $p_arr['start'] == ''){ $ret['error'] = 'check param - start'; return $ret;}
	if (!isset($p_arr['stop']) || $p_arr['stop'] == ''){ $ret['error'] = 'check param - stop'; return $ret;}
	if (!isset($p_arr['spital']) || $p_arr['spital'] == ''){ $ret['error'] = 'check param - spital'; return $ret;}
	
	$ret['legenda_get'] = cs('legenda/get',array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"start","op"=>"eq","data"=>$p_arr['start']),
		array("field"=>"stop","op"=>"eq","data"=>$p_arr['stop']),
	))));
	if ($ret['legenda_get'] != null){
		$ret['success'] = true;
		$ret['nume'] = $ret['legenda_get']['nume'];
		return $ret;
	}
	$found = false;
	$ret['i'] = 0;
	while($found == false){
		$ret['nume'] = 'a' . $ret['i'];
		$ret['legenda_get'] = cs('legenda/get',array("filters"=>array("rules"=>array(
			array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
			array("field"=>"nume","op"=>"eq","data"=>$ret['nume']),
		))));
		if ($ret['legenda_get'] != null){
			$ret['i']++;
		}else{
			$found = true;
		}
	}
	
	$ret['legenda_update'] = legenda_update(array(
		'oper' => 'add',
		'id' => 'null',
		'spital' => $p_arr['spital'],
		'start' => $p_arr['start'],
		'stop' => $p_arr['stop'],
		'nume' => $ret['nume'],
	));
	if (!isset($ret['legenda_update']['success'])||($ret['legenda_update']['success'] != true)){return $ret;}
	
	$ret['success'] = true;
	return $ret;
}
?>