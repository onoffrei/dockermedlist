<?php
$GLOBALS['spitale_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'nume'				=>array('type'=>'text',),
	'uri'				=>array('type'=>'text',),
	'contractcas'		=>array('type'=>'int',),
	'descriere'			=>array('type'=>'text',),
	'logo'				=>array('type'=>'int',),
	'telefon'			=>array('type'=>'text',),
	'adresa'			=>array('type'=>'text',),
	'activman'			=>array('type'=>'int',),
	'aprobat'			=>array('type'=>'int',),
	'platacontract'		=>array('type'=>'int',),
	'datastartcontract'	=>array('type'=>'datetime',),
	'tipcontract'		=>array('type'=>'text',),
); 
function spitale_get($p_arr = array()){
	$ret = null;
	$list = spitale_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["nume"] = $list['resp']['rows'][0]->nume;
		$ret["uri"] = $list['resp']['rows'][0]->uri;
		$ret["contractcas"] = intval($list['resp']['rows'][0]->contractcas);
		$ret["descriere"] = $list['resp']['rows'][0]->descriere;
		$ret["logo"] = intval($list['resp']['rows'][0]->logo);
		$ret["telefon"] = $list['resp']['rows'][0]->telefon;
		$ret["adresa"] = $list['resp']['rows'][0]->adresa;
		$ret["activman"] = intval($list['resp']['rows'][0]->activman);
		$ret["aprobat"] = intval($list['resp']['rows'][0]->aprobat);
		$ret["platacontract"] = intval($list['resp']['rows'][0]->platacontract);
		$ret["datastartcontract"] = $list['resp']['rows'][0]->datastartcontract;
		$ret["tipcontract"] = intval($list['resp']['rows'][0]->tipcontract);
	}
	return $ret;
}
function spitale_grid($p_arr = array()){
	global $spitale_cols;
	$p_arr['db_cols'] = $spitale_cols;
	$p_arr['db_table'] = 'spitale';
	return cs("_cs_grid/get",$p_arr);
}
function spitale_update($p_arr = array()){
	global $spitale_cols;
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
			//make sure uri is spital uri not conflict
			$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
				array("field"=>"id","op"=>"ne","data"=>$p_arr['id']),
				array("field"=>"uri","op"=>"eq","data"=>$p_arr['uri']),
			))));
			if ($ret['spitale_get'] != null) {
				$p_arr['uri'] = $p_arr['uri'] . rand(0,9);
			}
			//make sure uri is spital uri not conflict
			$ret['localitati_get'] = cs('localitati/get', array("filters"=>array("rules"=>array(
				array("field"=>"uri","op"=>"eq","data"=>$p_arr['uri']),
			))));
			if ($ret['localitati_get'] != null) {
				$p_arr['uri'] = $p_arr['uri'] . rand(0,9);
			}
			//make sure uri is spital uri not conflict
			$ret['specializari_get'] = cs('specializari/get', array("filters"=>array("rules"=>array(
				array("field"=>"uri","op"=>"eq","data"=>$p_arr['uri']),
			))));
			if ($ret['specializari_get'] != null) {
				$p_arr['uri'] = $p_arr['uri'] . rand(0,9);
			}
		}while($ret['spitale_get'] != null);
	}
	$p_arr['db_cols'] = $spitale_cols;
	$p_arr['db_table'] = 'spitale';
	return cs("_cs_grid/update",$p_arr);
}
function spitale_modifica_localitate_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}

	$ret['localitati_spitale_sql'] = 'SELECT localitati.id';
	$ret['localitati_spitale_sql'] .= '	, localitati.denumire';
	$ret['localitati_spitale_sql'] .= '	, localitati.uri';
	$ret['localitati_spitale_sql'] .= ' FROM localitati_spitale';
	$ret['localitati_spitale_sql'] .= ' LEFT JOIN localitati ON localitati_spitale.localitate = localitati.id';
	$ret['localitati_spitale_sql'] .= ' WHERE localitati_spitale.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id']);
	$ret['localitati_spitale_sql'] .= ' ORDER BY localitati.id DESC';
	$ret['localitati_spitale_sql'] .= ' LIMIT 0,1';

	$ret['localitati_spitale'] = cs("_cs_grid/get",array('db_sql'=>$ret['localitati_spitale_sql']));
	if (!isset($ret['localitati_spitale']['success'])||($ret['localitati_spitale']['success'] != true)){return $ret;}
	$localitate_denimire = '';
	$localitate_id = 0;
	if ($ret['localitati_spitale']['resp']['records'] > 0){
		$localitate_denimire = $ret['localitati_spitale']['resp']['rows'][0]->denumire;
		$localitate_id = $ret['localitati_spitale']['resp']['rows'][0]->id;		
	}
	ob_start(); 
	?> <style>
	</style>
	<div id="spitale_modifica_localitate_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_modifica_localitate_modal_action(this)" id="spitale_modifica_localitate_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Localitate</label>
									<input type="text" class="form-control" name="localitate_name" placeholder="localitate" value="<?php echo $localitate_denimire;?>" autocomplete="off">
									<input type="hidden" class="form-control" name="localitate" value="<?php echo $localitate_id;?>">
									<span name="localitate_span" style="position:absolute;"></span>
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
		$("#spitale_modifica_localitate_modal").modal('show')
		spital_validschema = {
			feedbackIcons: {
				valid: 'glyphicon glyphicon-ok',
				invalid: 'glyphicon glyphicon-remove',
				validating: 'glyphicon glyphicon-refresh'
			},
			fields: {
				localitate_name: {
					container: '#spitale_modifica_localitate_modal_form span[name=localitate_span]',
					validators: {
						callback: {
							callback: function(value, validator, $field) {
								var error = {valid: false,message: 'Localitatea este obligatorie'}
								if ((parseInt($("#spitale_modifica_localitate_modal_form input[name=localitate]").val()) > 0)) return true
								return error
							}
						},
					},
				},
			}
		}
		$("#spitale_modifica_localitate_modal_form").bootstrapValidator(spital_validschema)
		autocomplete(document.querySelector("#spitale_modifica_localitate_modal_form input[name=localitate_name]")
			, cs_url + "csapi/localitati/autocomplete?justsub=1&q="
			,function(id){
			if ($("#spitale_modifica_localitate_modal_form input[name=localitate]").val() != id){
				$("#spitale_modifica_localitate_modal_form input[name=localitate]").val(id)
				$("#spitale_modifica_localitate_modal_form").data('bootstrapValidator').resetForm()
				$("#spitale_modifica_localitate_modal_form").data('bootstrapValidator').validate()
				
			}
		})
		spitale_modifica_localitate_modal_action = function(form){
			cs('spitale/modifica_localitate',new FormData(form)).then(function(modifica_localitate){
				$("#spitale_modifica_localitate_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(modifica_localitate);'; 					
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
function spitale_modifica_nume_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="spitale_modifica_nume_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_modifica_nume_modal_action(this)" id="spitale_modifica_nume_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Nume</label>
									<input type="text" name="nume" class="form-control" placeholder="Nume" value='<?php echo $ret['spitale_get']['nume']?>'>
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
		$("#spitale_modifica_nume_modal").modal('show')
		spitale_modifica_nume_modal_action = function(form){
			cs('spitale/modifica',new FormData(form)).then(function(spitale_modifica){
				$("#spitale_modifica_nume_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(spitale_modifica);'; 					
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
function spitale_modifica_adresa_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="spitale_modifica_adresa_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_modifica_adresa_modal_action(this)" id="spitale_modifica_adresa_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>adresa</label>
									<input type="text" name="adresa" class="form-control" placeholder="adresa" value='<?php echo $ret['spitale_get']['adresa']?>'>
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
		$("#spitale_modifica_adresa_modal").modal('show')
		spitale_modifica_adresa_modal_action = function(form){
			cs('spitale/modifica',new FormData(form)).then(function(spitale_modifica){
				$("#spitale_modifica_adresa_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(spitale_modifica);'; 					
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
function spitale_modifica_telefon_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="spitale_modifica_telefon_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_modifica_telefon_modal_action(this)" id="spitale_modifica_telefon_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>telefon</label>
									<input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="telefon" class="form-control" placeholder="telefon" value='<?php echo $ret['spitale_get']['telefon']?>'>
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
		$("#spitale_modifica_telefon_modal").modal('show')
		spitale_modifica_telefon_modal_action = function(form){
			cs('spitale/modifica',new FormData(form)).then(function(spitale_modifica){
				$("#spitale_modifica_telefon_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(spitale_modifica);'; 					
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
function spitale_modifica_uri_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="spitale_modifica_uri_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_modifica_uri_modal_action(this)" id="spitale_modifica_uri_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Uri</label>
									<input type="text" name="uri" class="form-control" placeholder="Uri" value='<?php echo $ret['spitale_get']['uri']?>'>
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
		$("#spitale_modifica_uri_modal").modal('show')
		spitale_modifica_uri_modal_action = function(form){
			cs('spitale/modifica',new FormData(form)).then(function(spitale_modifica){
				$("#spitale_modifica_uri_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(spitale_modifica);'; 					
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
function spitale_modifica_descriere_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="spitale_modifica_descriere_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:spitale_modifica_descriere_modal_action(this)" onsubmit="javascript:void(0)" id="spitale_modifica_descriere_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Descriere</label>
									<div id="mytextarea">
									<?php echo $ret['spitale_get']['descriere']; ?>
									</div>
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
		$("#spitale_modifica_descriere_modal").modal('show')
		qeditor = new Quill('#mytextarea', {
			modules: {
				toolbar: [
					[
						{ 'font': [] },
						{ 'size': [] },
					],
					['bold', 'italic', 'underline'],
					[
						{'color': []},
						{'background': []},
					],
					[{ 'align': [] }],
					['link'],
					[{ list: 'ordered' }, { list: 'bullet' }]
				]
			},
			placeholder: 'Descriere',
			theme: 'snow'
		});
		spitale_modifica_descriere_modal_action = function(form){
			var myform = document.querySelector('#spitale_modifica_descriere_modal_form')
			var myformData = new FormData(myform)
			myformData.set('descriere',document.querySelector('#spitale_modifica_descriere_modal_form .ql-editor').innerHTML)
			cs('spitale/modifica',myformData).then(function(spitale_modifica){
				$("#spitale_modifica_descriere_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(spitale_modifica);'; 					
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
function spitale_modifica_contract_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="spitale_modifica_contract_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_modifica_contract_modal_action(this)" id="spitale_modifica_contract_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Contract cu C.A.S.</label>
									<select name="contractcas" class="form-control">
										<option value="0" <?php if ($ret['spitale_get']['contractcas'] == 0){echo 'selected="selected"';}?>>NU</option>
										<option value="1" <?php if ($ret['spitale_get']['contractcas'] == 1){echo 'selected="selected"';}?>>DA</option>
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
		$("#spitale_modifica_contract_modal").modal('show')
		spitale_modifica_contract_modal_action = function(form){
			cs('spitale/modifica',new FormData(form)).then(function(spitale_modifica){
				$("#spitale_modifica_contract_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(spitale_modifica);'; 					
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
function spitale_modifica_activman_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="spitale_modifica_contract_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_modifica_contract_modal_action(this)" id="spitale_modifica_contract_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Activ?</label>
									<select name="activman" class="form-control">
										<option value="0" <?php if ($ret['spitale_get']['activman'] == 0){echo 'selected="selected"';}?>>NU</option>
										<option value="1" <?php if ($ret['spitale_get']['activman'] == 1){echo 'selected="selected"';}?>>DA</option>
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
		$("#spitale_modifica_contract_modal").modal('show')
		spitale_modifica_contract_modal_action = function(form){
			cs('spitale/modifica',new FormData(form)).then(function(spitale_modifica){
				$("#spitale_modifica_contract_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(spitale_modifica);'; 					
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
function spitale_modifica_aprobat_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="spitale_modifica_contract_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_modifica_contract_modal_action(this)" id="spitale_modifica_contract_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Aprobat</label>
									<select name="aprobat" class="form-control">
										<option value="0" <?php if ($ret['spitale_get']['aprobat'] == 0){echo 'selected="selected"';}?>>NU</option>
										<option value="1" <?php if ($ret['spitale_get']['aprobat'] == 1){echo 'selected="selected"';}?>>DA</option>
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
		$("#spitale_modifica_contract_modal").modal('show')
		spitale_modifica_contract_modal_action = function(form){
			cs('spitale/modifica',new FormData(form)).then(function(spitale_modifica){
				$("#spitale_modifica_contract_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(spitale_modifica);'; 					
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
function spitale_adauga_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	ob_start(); 
	?> <div id="spitale_adauga_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Adauga Spital</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_adauga_modal_action(this)" id="spitale_adauga_modal_form" >
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Nume</label>
									<input type="text" name="nume" class="form-control" placeholder="Nume" value=''>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Uri</label>
									<input type="text" name="uri" class="form-control" placeholder="Uri" value=''>
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
		$("#spitale_adauga_modal").modal('show')
		spitale_adauga_modal_action = function(form){
			cs('spitale/adauga',new FormData(form)).then(function(d){
				//console.log(d)
				$("#spitale_adauga_modal").modal('hide')
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
function spitale_modifica_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}

	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}

	ob_start(); 
	?> <div id="spitale_modifica_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica Spital</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_modifica_modal_action(this)" id="spitale_modifica_modal_form" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Nume</label>
									<input type="text" name="nume" class="form-control" placeholder="Nume" value='<?php echo $ret['spitale_get']['nume']?>'>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Uri</label>
									<input type="text" name="uri" class="form-control" placeholder="Uri" value='<?php echo $ret['spitale_get']['uri']?>'>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Aprobat</label>
									<select name="aprobat" class="form-control">
										<option value="0" <?php if ($ret['spitale_get']['aprobat'] == 0){echo 'selected="selected"';}?>>NU</option>
										<option value="1" <?php if ($ret['spitale_get']['aprobat'] == 1){echo 'selected="selected"';}?>>DA</option>
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
		$("#spitale_modifica_modal").modal('show')
		spitale_modifica_modal_action = function(form){
			cs('spitale/modifica',new FormData(form)).then(function(d){
				//console.log(d)
				$("#spitale_modifica_modal").modal('hide')
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
function spitale_adauga($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['nume']) || $p_arr['nume'] == ''){ $ret['error'] = 'check param - nume'; return $ret;}
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$ret['spitale_update'] = spitale_update($p_arr);
	if (!isset($ret['spitale_update']['success'])||($ret['spitale_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function spitale_modifica($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	if (isset($p_arr['localitate']) && (intval($p_arr['localitate']) > 0)){
		$ret['spitale_modifica_localitate'] = cs('spitale/modifica_localitate',$p_arr);
		if (!isset($ret['spitale_modifica_localitate']['success'])||($ret['spitale_modifica_localitate']['success'] != true)){return $ret;}
	}
	$p_arr['oper'] = 'edit';
	$ret['spitale_update'] = spitale_update($p_arr);
	if (!isset($ret['spitale_update']['success'])||($ret['spitale_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function spitale_modifica_localitate($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	if (!isset($p_arr['localitate']) || (!(intval($p_arr['localitate']) > 0))){ $ret['error'] = 'check param - localitate'; return $ret;}
	$ret['localitati_spitale_sql'] = 'DELETE'
		. ' FROM `localitati_spitale`'
		. ' WHERE'
			. '  `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id']);
	;
	$ret['localitati_spitale'] = cs("_cs_grid/get",array('db_sql'=>$ret['localitati_spitale_sql']));
	if (!isset($ret['localitati_spitale']['success'])||($ret['localitati_spitale']['success'] != true)){return $ret;}
	
	$ret['localitati_breadcrumb'] = cs('localitati/breadcrumb', array('locid'=>$p_arr['localitate']));
	if (!isset($ret['localitati_breadcrumb']['success'])||($ret['localitati_breadcrumb']['success'] != true)){return $ret;}
	
	foreach($ret['localitati_breadcrumb']['resp']['nodearr'] as $localitate){
		$ret['localitati_spitale_update'] = cs('localitati_spitale/update',array(
			'oper'=>'add',
			'id'=>'null',
			'spital'=>$GLOBALS['cs_db_conn']->real_escape_string($p_arr['id']),
			'localitate'=>$localitate['id'],
		));
		if (!isset($ret['localitati_spitale_update']['success'])||($ret['localitati_spitale_update']['success'] != true)){return $ret;}
	}
	$ret['success'] = true;
	return $ret;
}
function spitale_sterge($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null ){return $ret;}

	if (intval($ret['spitale_get']['logo']) >0 ){
		$ret['spitale_logo_delete'] = cs('spitale/logo_delete',array("spital_id"=>$p_arr['id']));
		if (!isset($ret['spitale_logo_delete']['success'])||($ret['spitale_logo_delete']['success'] != true)){return $ret;}
	}
	
	$ret['users_getspitalusers'] = cs('users/getspitalusers',array('spital'=>$p_arr['id'],'level'=>user_level_manager));
	if (!isset($ret['users_getspitalusers']['success'])||($ret['users_getspitalusers']['success'] != true)){return $ret;}
	if (count($ret['users_getspitalusers']['resp']['rows']) >0){
		foreach ($ret['users_getspitalusers']['resp']['rows'] as $spitaluser){
			$ret['spitale_users_unlink'] = cs('spitale_users/unlink',array('uid'=>$spitaluser->spitale_users_userid,'sid'=>$p_arr['id']));
			if (!isset($ret['spitale_users_unlink']['success'])||($ret['spitale_users_unlink']['success'] != true)){return $ret;}
			if (intval($spitaluser->spitale_users_userid) != $_SESSION['cs_users_id']){
				$ret['users_doctordelete'] = cs('users/doctordelete',array(
					'did'=>$spitaluser->spitale_users_userid,
					'sid'=>$p_arr['id'],
				));
				if (!isset($ret['users_doctordelete']['success'])||($ret['users_doctordelete']['success'] != true)){return $ret;}				
			}
		}
	}
	
	$ret['legenda_grid'] = cs('legenda/grid', array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['id'])
	))));
	if (!isset($ret['legenda_grid']['success'])||($ret['legenda_grid']['success'] != true)){return $ret;}
	if (count($ret['legenda_grid']['resp']['rows']) >0){
		foreach ($ret['legenda_grid']['resp']['rows'] as $legenda){
			$ret['legenda_sterge'] = cs('legenda/sterge',array("id"=>$legenda->id));
			if (!isset($ret['legenda_sterge']['success'])||($ret['legenda_sterge']['success'] != true)){return $ret;}
		}
	}
	$ret['planificaredel_sql'] = 'DELETE'
		. ' FROM `planificare`'
		. ' WHERE'
			. '  `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id']);
	;
	//$r = $GLOBALS['cs_db_conn']->query($ret['planificaredel_sql']);
	$ret['planificaredel'] = cs("_cs_grid/get",array('db_sql'=>$ret['planificaredel_sql']));
	if (!isset($ret['planificaredel']['success'])||($ret['planificaredel']['success'] != true)){return $ret;}

	$ret['programaridel_sql'] = 'DELETE'
		. ' FROM `programari`'
		. ' WHERE'
			. '  `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id']);
	;
	//$r = $GLOBALS['cs_db_conn']->query($ret['programaridel_sql']);
	$ret['programaridel'] = cs("_cs_grid/get",array('db_sql'=>$ret['programaridel_sql']));
	if (!isset($ret['programaridel']['success'])||($ret['programaridel']['success'] != true)){return $ret;}
	
	$ret['specializari_user_spitale_sql'] = 'DELETE'
		. ' FROM `specializari_user_spitale`'
		. ' WHERE'
			. '  `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id']);
	;
	//$r = $GLOBALS['cs_db_conn']->query($ret['specializari_user_spitale_sql']);
	$ret['specializari_user_spitale'] = cs("_cs_grid/get",array('db_sql'=>$ret['specializari_user_spitale_sql']));
	if (!isset($ret['specializari_user_spitale']['success'])||($ret['specializari_user_spitale']['success'] != true)){return $ret;}

	$ret['localitati_spitale_sql'] = 'DELETE'
		. ' FROM `localitati_spitale`'
		. ' WHERE'
			. '  `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id']);
	;
	//$r = $GLOBALS['cs_db_conn']->query($ret['localitati_spitale_sql']);
	$ret['localitati_spitale'] = cs("_cs_grid/get",array('db_sql'=>$ret['localitati_spitale_sql']));
	if (!isset($ret['localitati_spitale']['success'])||($ret['localitati_spitale']['success'] != true)){return $ret;}

	$ret['detaliiservicii_sql'] = 'DELETE'
		. ' FROM `detaliiservicii`'
		. ' WHERE'
			. '  `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id']);
	;
	//$r = $GLOBALS['cs_db_conn']->query($ret['detaliiservicii_sql']);
	$ret['detaliiservicii'] = cs("_cs_grid/get",array('db_sql'=>$ret['detaliiservicii_sql']));
	if (!isset($ret['detaliiservicii']['success'])||($ret['detaliiservicii']['success'] != true)){return $ret;}
	
	$ret['spitale_images_grid'] = cs('spitale_images/grid', array("filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['id'])
	))));
	if (!isset($ret['spitale_images_grid']['success'])||($ret['spitale_images_grid']['success'] != true)){return $ret;}
	if (count($ret['spitale_images_grid']['resp']['rows']) >0){
		foreach ($ret['spitale_images_grid']['resp']['rows'] as $image){
			$ret['spitale_images_delete_js'] = cs('spitale_images/delete_js',array("image"=>$image->image));
			if (!isset($ret['spitale_images_delete_js']['success'])||($ret['spitale_images_delete_js']['success'] != true)){return $ret;}
		}
	}
	
	$p_arr['oper'] = 'del';
	$ret['spitale_update'] = spitale_update($p_arr);
	if (!isset($ret['spitale_update']['success'])||($ret['spitale_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function spitale_logo_change($p_arr){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['spital_id'])){ $ret['error'] = 'check param - spital_id'; return $ret;}
	if (!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'permision'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['spital_id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	if ($ret['spitale_get']['logo'] > 0){
		$ret['images_delete'] = cs("images/delete",array("filters"=>array("groupOp"=>"AND","rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$ret['spitale_get']['logo'])
		))));
	}
	$ret['images_add'] = cs('images/add');
	if(!isset($ret['images_add']['success']) || ($ret['images_add']['success'] != true)) return $ret;
	$ret['spitale_update'] = spitale_update(array(
		"oper"=>"edit",
		"id"=>$p_arr['spital_id'], 
		"logo"=>$ret['images_add']['resp']['id']
	));
	if(!isset($ret['spitale_update']['success']) || ($ret['spitale_update']['success'] != true)) return $ret;
	$ret['success'] = true; 
	unset($ret['error']);
	return $ret;
}
function spitale_logo_delete($p_arr){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($_SESSION['cs_users_id'])) {$ret['error'] = 'permision'; return $ret;}
	if (!isset($p_arr['spital_id'])){ $ret['error'] = 'check param - spital_id'; return $ret;}
	
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['spital_id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	
	$ret['images_delete'] = cs("images/delete",array("filters"=>array("groupOp"=>"AND","rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$ret['spitale_get']['logo'])
	))));
	$ret['spitale_update'] = spitale_update(array("oper"=>"edit","id"=>$p_arr['spital_id'], "logo"=>"0"));
	if(!isset($ret['spitale_update']['success']) || ($ret['spitale_update']['success'] != true)) return $ret;
	$ret['success'] = true;
	return $ret;
}
function spitale_modifica_tipcontract_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['spitale_get'] == null) {return $ret;}
	ob_start(); 
	?> <div id="spitale_modifica_tipcontract_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="spitale_modifica_contract_modal_action(this)" id="spitale_modifica_contract_modal_form" autocomplete="off" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Tip contract</label>
									<select name="tipcontract" class="form-control">
										<option value="0" <?php if ($ret['spitale_get']['tipcontract'] == 0){echo 'selected="selected"';}?>>Fara contract</option>
										<option value="363" <?php if ($ret['spitale_get']['tipcontract'] == 363){echo 'selected="selected"';}?>>363</option>
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
		$("#spitale_modifica_tipcontract_modal").modal('show')
		spitale_modifica_contract_modal_action = function(form){
			cs('spitale/modifica',new FormData(form)).then(function(spitale_modifica){
				$("#spitale_modifica_tipcontract_modal").modal('hide')
				<?php if (isset($p_arr['callback'])){
					if ($p_arr['callback'] == 'window.location.reload'){
						echo 'window.location.reload(true)';//iexplore bug
					}else{
						echo $p_arr['callback'] . '(spitale_modifica);'; 					
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
?>