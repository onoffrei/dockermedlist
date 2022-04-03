<?php
$GLOBALS['localitati_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'denumire'			=>array('type'=>'text',),
	'uri'				=>array('type'=>'text',),
	'numescurt'			=>array('type'=>'text',),
	'lat'				=>array('type'=>'float',),
	'long'				=>array('type'=>'float',),
	'parent'			=>array('type'=>'int',),
	'zoom'				=>array('type'=>'int',),
); 
function localitati_get($p_arr = array()){
	$ret = null;
	$list = localitati_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["denumire"] = $list['resp']['rows'][0]->denumire;
		$ret["uri"] = $list['resp']['rows'][0]->uri;
		$ret["numescurt"] = $list['resp']['rows'][0]->numescurt;
		$ret["lat"] = floatval($list['resp']['rows'][0]->lat);
		$ret["long"] = floatval($list['resp']['rows'][0]->long);
		$ret["parent"] = intval($list['resp']['rows'][0]->parent);
		$ret["zoom"] = intval($list['resp']['rows'][0]->zoom);
	}
	return $ret;
}
function localitati_grid($p_arr = array()){
	global $localitati_cols;
	$p_arr['db_cols'] = $localitati_cols;
	if (!isset($p_arr['rows'])) $p_arr['rows'] = 50;
	if (!isset($p_arr['sord'])) $p_arr['sord'] = 'asc';
	if (!isset($p_arr['sidx'])) $p_arr['sidx'] = 'denumire';
	$p_arr['db_table'] = 'localitati';
	return cs("_cs_grid/get",$p_arr);
}
function localitati_update($p_arr = array()){
	global $localitati_cols;
	if (((!isset($p_arr['uri']))||($p_arr['uri'] == '')) && isset($p_arr['denumire'])){$p_arr['uri'] = $p_arr['denumire'];}
	if (isset($p_arr['uri'])){
		$p_arr['uri'] = mb_strtolower($p_arr['uri']);
		$p_arr['uri'] = cs('util/removeAccents',array('text'=>$p_arr['uri']));
		$p_arr['uri'] = preg_replace('/(&#[0-9]*;)/s', '-', $p_arr['uri']);
		$p_arr['uri'] = preg_replace('/( +)/s', '-', $p_arr['uri']);
		$p_arr['uri'] = preg_replace('/([^-a-zA-Z0-9])/', '-', $p_arr['uri']);		
	}
	$p_arr['db_cols'] = $localitati_cols;
	$p_arr['db_table'] = 'localitati';
	return cs("_cs_grid/update",$p_arr);
}

function localitati_adauga_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	ob_start(); 
	?> <div id="localitati_adauga_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Adauga Localitate</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="localitati_adauga_modal_action(this)" id="localitati_adauga_modal_form" >
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Localitatea</label>
									<input type="text" name="denumire" class="form-control" placeholder="denumire" value=''>
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
		$("#localitati_adauga_modal").modal('show')
		localitati_adauga_modal_action = function(form){
			cs('localitati/adauga',new FormData(form)).then(function(d){
				//console.log(d)
				$("#localitati_adauga_modal").modal('hide')
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
function localitati_modifica_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}

	$ret['localitati_get'] = cs('localitati/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['localitati_get'] == null) {return $ret;}

	ob_start(); 
	?> <div id="localitati_modifica_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica Localitate</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="localitati_modifica_modal_action(this)" id="localitati_modifica_modal_form" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Localitatea</label>
									<input type="text" name="denumire" class="form-control" placeholder="denumire" value='<?php echo $ret['localitati_get']['denumire']?>'>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Uri</label>
									<input type="text" name="uri" class="form-control" placeholder="Uri" value='<?php echo $ret['localitati_get']['uri']?>'>
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
		$("#localitati_modifica_modal").modal('show')
		localitati_modifica_modal_action = function(form){
			cs('localitati/modifica',new FormData(form)).then(function(d){
				//console.log(d)
				$("#localitati_modifica_modal").modal('hide')
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
function localitati_adauga($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['denumire']) || $p_arr['denumire'] == ''){ $ret['error'] = 'check param - denumire'; return $ret;}
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$ret['localitati_update'] = localitati_update($p_arr);
	if (!isset($ret['localitati_update']['success'])||($ret['localitati_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function localitati_modifica($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['denumire']) || $p_arr['denumire'] == ''){ $ret['error'] = 'check param - denumire'; return $ret;}
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$p_arr['oper'] = 'edit';
	$ret['localitati_update'] = localitati_update($p_arr);
	if (!isset($ret['localitati_update']['success'])||($ret['localitati_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function localitati_sterge($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$p_arr['oper'] = 'del';
	$ret['localitati_update'] = localitati_update($p_arr);
	if (!isset($ret['localitati_update']['success'])||($ret['localitati_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function localitati_autocomplete($p_arr = array()){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	if (!isset($p_arr['q'])){$ret['error'] = 'missing field'; return $ret;}
	$filters = array(
		"filters"=>array("groupOp"=>"AND"
			, 'rules'=>array()
			, 'groups'=>array(
				array('groupOp'=>'OR'
					,'rules'=>array(
						array("field"=>"denumire","op"=>"cn","data"=>$p_arr['q']),
						array("field"=>"uri","op"=>"cn","data"=>$p_arr['q']),
					)
				),
			)
		),
		"sidx"=>"zoom",
		"sord"=>"desc",
	);
	if (isset($p_arr['justsub'])){
		$filters['filters']['rules'][] = array("field"=>"parent","op"=>"ne","data"=>0);
	}
	$ret['localitati_grid'] = localitati_grid($filters); 
	if (!isset($ret['localitati_grid']['success'])||($ret['localitati_grid']['success'] != true)) return $ret;
	$ret['resp'] = $ret['localitati_grid']['resp']; 
	$ret['success'] = true;
	return $ret;
}

function localitati_breadcrumb($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'error'=>'');
	if (!isset($p_arr['callback']) || ($p_arr['callback'] == '')){$p_arr['callback'] = "javascript:void";}
	if (!isset($p_arr['locid'])) { $p_arr['locid'] = 0; } 
	$ret['resp']['nodearr'] = array();


	if (intval($p_arr['locid']) > 0){
		$localitati_get = localitati_get(array("filters" => array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$p_arr['locid']),
		))));
		
		if ($localitati_get == null) {
			//$ret['error'] = 'e1 localitati_get parent ' . $p_arr['locid']; return $ret;
			$ret['reset'] = '';
			$ret['resp']['nodearr'] = array();
		}
		$ret['resp']['nodearr'][] = $localitati_get;
		while($localitati_get['parent'] != 0){
			$localitati_get = localitati_get(array("filters" => array("rules"=>array(
				array("field"=>"id","op"=>"eq","data"=>$localitati_get['parent']),
			))));
			if ($localitati_get == null) {
				//$ret['error'] = 'e2 localitati_get parent ' . $localitati_get['parent']; return $ret;
				$ret['reset'] = '';
				$ret['resp']['nodearr'] = array();
				break;
			}
			$ret['resp']['nodearr'][] = $localitati_get;
		}
	}
	$nodearr_count = count($ret['resp']['nodearr']); 
	ob_start(); 
	?> <ol id="localitati_breadcrumb" class="breadcrumb"> <?php 
			if ($nodearr_count == 0){
				echo '<li class="active">Alege localitatea</li>';
			}else{
				echo '<li><a href="javascript:void(0)" onclick="event.stopPropagation(); event.preventDefault();' . $p_arr['callback'] . '(0); return false;">localitati</a></li>';
			}
			for ($i = $nodearr_count - 1; $i >= 0; $i-- ){
				if ($i == 0){
					echo '<li class="active">' . $ret['resp']['nodearr'][$i]['denumire'] . '</li>';						
				}else{
					echo '<li><a href="javascript:void(0)" onclick="event.stopPropagation(); event.preventDefault(); ' . $p_arr['callback'] . '(' . $ret['resp']['nodearr'][$i]['id'] . '); return false;">' . $ret['resp']['nodearr'][$i]['denumire'] . '</a></li>';						
				}
			}
	?> </ol> <?php
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}

?>