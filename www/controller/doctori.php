<?php
$GLOBALS['doctori_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'nume'				=>array('type'=>'text',),
	'specializare'		=>array('type'=>'int',),
	'spital'			=>array('type'=>'int',),
);
function doctori_get($p_arr = array()){
	$ret = null;
	$list = doctori_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["nume"] = $list['resp']['rows'][0]->nume;
		$ret["specializare"] = intval($list['resp']['rows'][0]->specializare);
		$ret["spital"] = intval($list['resp']['rows'][0]->spital);
	}
	return $ret;
}
function doctori_grid($p_arr = array()){
	global $doctori_cols;
	$p_arr['db_cols'] = $doctori_cols;
	$p_arr['db_table'] = 'doctori';
	return cs("_cs_grid/get",$p_arr);
}
function doctori_update($p_arr = array()){
	global $doctori_cols;
	$p_arr['db_cols'] = $doctori_cols;
	$p_arr['db_table'] = 'doctori';
	return cs("_cs_grid/update",$p_arr);
}

function doctori_adauga_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	ob_start(); 

	
	$specializari_grid = cs('specializari/grid',array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
	))));
	cscheck($specializari_grid);
	
	?> <div id="doctori_adauga_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Adauga Specialitate</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="doctori_adauga_modal_action(this)" id="doctori_adauga_modal_form" >
						<input type="hidden" name="spital" value='<?php echo $p_arr['spital']?>'> <!--spitalul-->
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Nume</label>
									<input type="text" name="nume" class="form-control" placeholder="nume" value=''>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Specializare</label>
									<select name="specializare" class="form-control">
									<?php									
									foreach ($specializari_grid['resp']['rows'] as $specializari){?>
										 <option value="<?php echo $specializari->id; ?>"><?php echo $specializari->denumire; ?></option>
									<?php } ?>
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
		$("#doctori_adauga_modal").modal('show')
		doctori_adauga_modal_action = function(form){
			cs('doctori/adauga',new FormData(form)).then(function(d){
				console.log(d)
				$("#doctori_adauga_modal").modal('hide')
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

function doctori_modifica_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['doctorid'])){ $ret['error'] = 'check param - doctorid'; return $ret;}
	if (!isset($p_arr['spitalid'])){ $ret['error'] = 'check param - spitalid'; return $ret;}

	$ret['doctori_get'] = cs('doctori/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['doctorid'])
	))));
	if ($ret['doctori_get'] == null) {return $ret;}

	ob_start(); 
	?> <div id="doctori_modifica_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica Doctor</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="doctori_modifica_modal_action(this)" id="doctori_modifica_modal_form" >
						<input type="hidden" name="id" value='<?php echo $p_arr['doctorid'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>nume doctor</label>
									<input type="text" name="nume" class="form-control" placeholder="nume" value='<?php echo $ret['doctori_get']['nume']?>'>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Specializare</label>
									<?php
									$ret['specializari_grid'] = cs('specializari/grid',array("filters"=>array("rules"=>array(
										array("field"=>"spital","op"=>"eq","data"=>$p_arr['spitalid']),
									))));
									cscheck($ret['specializari_grid']);
									?>
																	
									<select name="specializare" class="form-control">
									<?php									
									foreach ($ret['specializari_grid']['resp']['rows'] as $specializari){
									?>
										<?php 
											$selected = '';
											if ($ret['doctori_get']['specializare'] == $specializari->id) $selected = 'selected'
										?>
										<option value="<?php echo $specializari->id; ?>" <?php echo $selected;?>><?php echo $specializari->denumire; ?></option>
									<?php }?>																
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
		$("#doctori_modifica_modal").modal('show')
		doctori_modifica_modal_action = function(form){
			cs('doctori/modifica',new FormData(form)).then(function(d){
				//console.log(d)
				$("#doctori_modifica_modal").modal('hide')
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

function doctori_adauga($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['nume']) || $p_arr['nume'] == ''){ $ret['error'] = 'check param - nume'; return $ret;}
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$ret['doctori_update'] = doctori_update($p_arr);
	if (!isset($ret['doctori_update']['success'])||($ret['doctori_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function doctori_modifica($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['nume']) || $p_arr['nume'] == ''){ $ret['error'] = 'check param - nume'; return $ret;}
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$p_arr['oper'] = 'edit';
	$ret['doctori_update'] = doctori_update($p_arr);
	if (!isset($ret['doctori_update']['success'])||($ret['doctori_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function doctori_sterge($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$p_arr['oper'] = 'del';
	$ret['doctori_update'] = doctori_update($p_arr);
	if (!isset($ret['doctori_update']['success'])||($ret['doctori_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
?>