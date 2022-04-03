<?php


function pado_adauga_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	ob_start(); 

	
	$specializari_grid = cs('specializari/grid',array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
	))));
	cscheck($specializari_grid);
	
	?> <div id="pado_adauga_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Adauga Tarif</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="pado_adauga_modal_action(this)" id="pado_adauga_modal_form" >
						<input type="hidden" name="spital" value='<?php echo $p_arr['spital']?>'> <!--spitalul-->
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Denumire</label>
									<input type="text" name="subspecializare" class="form-control" placeholder="subspecializare" value=''>
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
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Tarif</label>
									<input type="text" name="tarif" class="form-control" placeholder="tarif" value=''>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>CNSAS?</label>
									<input type="checkbox" name="tarif" class="form-control" placeholder="CNSAS" value="1">
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
		$("#pado_adauga_modal").modal('show')
		pado_adauga_modal_action = function(form){
			cs('pado/adauga',new FormData(form)).then(function(d){
				console.log(d)
				$("#pado_adauga_modal").modal('hide')
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

function pado_modifica_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['padod'])){ $ret['error'] = 'check param - padod'; return $ret;}
	if (!isset($p_arr['spitalid'])){ $ret['error'] = 'check param - spitalid'; return $ret;}

	$ret['pado_get'] = cs('pado/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['padod'])
	))));
	if ($ret['pado_get'] == null) {return $ret;}

	ob_start(); 
	?> <div id="pado_modifica_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica Doctor</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="pado_modifica_modal_action(this)" id="pado_modifica_modal_form" >
						<input type="hidden" name="id" value='<?php echo $p_arr['padod'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Subspecializare</label>
									<input type="text" name="subspecializare" class="form-control" placeholder="subspecializare" value='<?php echo $ret['pado_get']['subspecializare']?>'>
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
											if ($ret['pado_get']['specializare'] == $specializari->id) $selected = 'selected'
										?>
										<option value="<?php echo $specializari->id; ?>" <?php echo $selected;?>><?php echo $specializari->denumire; ?></option>
									<?php }?>																
									</select>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Tarif</label>
									<input type="text" name="tarif" class="form-control" placeholder="tarif" value='<?php echo $ret['pado_get']['tarif']?>'>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>CNSAS?</label>
									<?php $checked=""; if($ret['pado_get']['cnsas'] ==1) $checked="checked"; ?>
									<input type="checkbox" name="cnsas" class="form-control" placeholder="CNSAS" value="" <?php echo $checked; ?> >
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
		$("#pado_modifica_modal").modal('show')
		pado_modifica_modal_action = function(form){
			cs('pado/modifica',new FormData(form)).then(function(d){
				//console.log(d)
				$("#pado_modifica_modal").modal('hide')
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

function pado_adauga($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['subspecializare']) || $p_arr['subspecializare'] == ''){ $ret['error'] = 'check param - subspecializare'; return $ret;}
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$ret['pado_update'] = pado_update($p_arr);
	if (!isset($ret['pado_update']['success'])||($ret['pado_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function pado_modifica($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['subspecializare']) || $p_arr['subspecializare'] == ''){ $ret['error'] = 'check param - subspecializare'; return $ret;}
	
	$p_arr['oper'] = 'edit';
	$ret['pado_update'] = pado_update($p_arr);
	if (!isset($ret['pado_update']['success'])||($ret['pado_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function pado_sterge($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$p_arr['oper'] = 'del';
	$ret['pado_update'] = pado_update($p_arr);
	if (!isset($ret['pado_update']['success'])||($ret['pado_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
?>