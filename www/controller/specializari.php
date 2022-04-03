<?php
$GLOBALS['specializari_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'denumire'			=>array('type'=>'text',),
	'uri'				=>array('type'=>'text',),
	'parent'			=>array('type'=>'int',),
	'sorder'			=>array('type'=>'int',),
);
function specializari_get($p_arr = array()){
	$ret = null;
	$list = specializari_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["denumire"] = $list['resp']['rows'][0]->denumire;
		$ret["uri"] = $list['resp']['rows'][0]->uri;
		$ret["parent"] = intval($list['resp']['rows'][0]->parent);
		$ret["sorder"] = intval($list['resp']['rows'][0]->sorder);
	}
	return $ret;
}
function specializari_grid($p_arr = array()){
	global $specializari_cols;
	if (!isset($p_arr['sord'])) {$p_arr['sord'] = 'asc';}
	if (!isset($p_arr['sidx'])) {$p_arr['sidx'] = 'sorder';}
	if (!isset($p_arr['rows'])) {$p_arr['rows'] = 50;}
	$p_arr['db_cols'] = $specializari_cols;
	$p_arr['db_table'] = 'specializari';
	$p_arr['db_sql'] = "SELECT " 
		." SQL_CALC_FOUND_ROWS `id`, `denumire`, `uri`, `parent`, `sorder`"
		." ,(select `denumire` from `specializari` as cat1 where `cat1`.`id` = `specializari`.`parent`) as parentname"
		." FROM `specializari` __filters__ __order__ __limit__";
	return cs("_cs_grid/get",$p_arr);
}
function specializari_update($p_arr = array()){
	global $specializari_cols;
	if (((!isset($p_arr['uri']))||($p_arr['uri'] == '')) && isset($p_arr['denumire'])){$p_arr['uri'] = $p_arr['denumire'];}
	if (isset($p_arr['uri'])){
		$p_arr['uri'] = mb_strtolower($p_arr['uri']);
		$p_arr['uri'] = cs('util/removeAccents',array('text'=>$p_arr['uri']));
		$p_arr['uri'] = preg_replace('/(&#[0-9]*;)/s', '-', $p_arr['uri']);
		$p_arr['uri'] = preg_replace('/( +)/s', '-', $p_arr['uri']);
		$p_arr['uri'] = preg_replace('/([^-a-zA-Z0-9])/', '-', $p_arr['uri']);		
	}
	$p_arr['db_cols'] = $specializari_cols;
	$p_arr['db_table'] = 'specializari';
	return cs("_cs_grid/update",$p_arr);
}
function specializari_adauga_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['parent'])){ $ret['error'] = 'check param - parent'; return $ret;}
	if (!isset($p_arr['callback'])) { $p_arr['callback'] = 'javascript:void'; } 
	ob_start(); 
	?> <div id="specializari_adauga_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Adauga specializare</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="specializari_adauga_modal_action(this)" id="specializari_adauga_modal_form" >
						<input type="hidden" name="parent" value="<?php echo $p_arr['parent']?>">
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>denumire</label>
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
		$("#specializari_adauga_modal").modal('show')
		specializari_adauga_modal_action = function(form){
			cs('specializari/adauga',new FormData(form)).then(function(specializari_adauga){
				//console.log(d)
				$("#specializari_adauga_modal").modal('hide')
				if (typeof(specializari_adauga.success) == 'undefined' || specializari_adauga.success != true) {
					alert('salvare nereusita')
					return
				}
				<?php echo $p_arr['callback']; ?>(specializari_adauga);
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function specializari_adauga($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['denumire']) || $p_arr['denumire'] == ''){ $ret['error'] = 'check param - denumire'; return $ret;}
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$ret['specializari_update'] = specializari_update($p_arr);
	if (!isset($ret['specializari_update']['success'])||($ret['specializari_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function specializari_modifica_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	if (!isset($p_arr['callback'])) { $p_arr['callback'] = 'javascript:void'; } 
	
	$ret['specializari_get'] = cs('specializari/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id'])
	))));
	if ($ret['specializari_get'] == null) {return $ret;}

	ob_start(); 
	?> <div id="specializari_modifica_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-pencil" aria-hidden="true"></i> Modifica Specializare</h4>
				</div>
				<div class="modal-body">
					<form action="javascript:void(0)" onsubmit="specializari_modifica_modal_action(this)" id="specializari_modifica_modal_form" >
						<input type="hidden" name="id" value='<?php echo $p_arr['id'];?>'>
						<div class="row">
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>denumire</label>
									<input type="text" name="denumire" class="form-control" placeholder="denumire" value='<?php echo $ret['specializari_get']['denumire']?>'>
								</div>
							</div>
							<div class="col-xs-12 ">
								<div class="form-group">
									<label>Uri</label>
									<input type="text" name="uri" class="form-control" placeholder="Uri" value='<?php echo $ret['specializari_get']['uri']?>'>
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
		$("#specializari_modifica_modal").modal('show')
		specializari_modifica_modal_action = function(form){
			cs('specializari/modifica',new FormData(form)).then(function(specializari_modifica){
				//console.log(d)
				$("#specializari_modifica_modal").modal('hide')
				if (typeof(specializari_modifica.success) == 'undefined' || specializari_modifica.success != true) {
					alert('salvare nereusita')
					return
				}
				<?php echo $p_arr['callback']; ?>(specializari_modifica);
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;

}
function specializari_modifica($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['denumire']) || $p_arr['denumire'] == ''){ $ret['error'] = 'check param - denumire'; return $ret;}
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$p_arr['oper'] = 'edit';
	$ret['specializari_update'] = specializari_update($p_arr);
	if (!isset($ret['specializari_update']['success'])||($ret['specializari_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
function specializari_sterge($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['id'])){ $ret['error'] = 'check param - id'; return $ret;}
	$ret['rec'] = array();
	$ret['specializari_grid_sub'] = specializari_grid(array("filters"=>array("rules"=>array(
		array("field"=>"parent","op"=>"eq","data"=>$p_arr['id']),
	))));
	if (!isset($ret['specializari_grid_sub']['success']) || ($ret['specializari_grid_sub']['success'] != true)) return $ret;
	foreach($ret['specializari_grid_sub']['resp']['rows'] as $row_sub){
		$ret['rec'][] = specializari_sterge(array("id"=>$row_sub->id));
	}
	specializari_update(array("oper"=>"del","id"=>$p_arr['id']));
	$ret['success'] = true;
	return $ret;
}
function specializari_sortorderinit($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if ((!isset($p_arr['parent']))||($p_arr['parent'] === '')){$ret['error'] = 'missing field - parent'; return $ret;}
	$ret['specializari_get'] = specializari_get(array("filters"=>array("rules"=>array(
		array("field"=>"parent","op"=>"eq","data"=>$p_arr['parent']),
		array("field"=>"sorder","op"=>"eq","data"=>0)),
	))); 
	if ($ret['specializari_get'] != null){
		$ret['specializari_grid'] = specializari_grid(array(
			"filters"=>array("rules"=>array(
				array("field"=>"parent","op"=>"eq","data"=>$p_arr['parent']),
			)),
			"rows"=>500,
		));
		$i = 1;
		if (!isset($ret['specializari_grid']['success']) || ($ret['specializari_grid']['success'] != true)) return $ret;
		foreach($ret['specializari_grid']['resp']['rows'] as $row){
			specializari_update(array("oper"=>"edit","id"=>$row->id,"sorder"=>$i));
			$i++;
		}
	}
	$ret['success'] = true;
	return $ret;
}
function specializari_sortorderchange($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if ((!isset($p_arr['id']))||($p_arr['id'] == '')){$ret['error'] = 'missing field - id'; return $ret;}
	if ((!isset($p_arr['newval']))||($p_arr['newval'] == '')){$ret['error'] = 'missing field - newval'; return $ret;}
	$ret['specializari_get'] = specializari_get(array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['id']),
	)))); 
	if ($ret['specializari_get'] == null) {$ret['error'] = 'invalid id'; return $ret;}
	$ret['specializari_sortorderinit'] = specializari_sortorderinit(array('parent'=>$ret['specializari_get']['parent']));
	if (!isset($ret['specializari_sortorderinit']['success']) || ($ret['specializari_sortorderinit']['success'] != true)) {$ret['error'] = 'specializari_sortorderinit'; return $ret;}
	$ret['specializari_get1'] = specializari_get(array("filters"=>array("rules"=>array(
		array("field"=>"parent","op"=>"eq","data"=>$ret['specializari_get']['parent']),
		array("field"=>"sorder","op"=>"eq","data"=>$p_arr['newval']),
	)))); 
	if ($ret['specializari_get1'] == null) {$ret['error'] = 'invalid newval'; return $ret;}
	specializari_update(array("oper"=>"edit","id"=>$ret['specializari_get1']['id'],"sorder"=>$ret['specializari_get']['sorder']));
	specializari_update(array("oper"=>"edit","id"=>$p_arr['id'],"sorder"=>$p_arr['newval']));
	$ret['success'] = true;
	return $ret;
}
function specializari_inputedit_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if ((!isset($p_arr['id']))||($p_arr['id'] == '')){$ret['error'] = 'check field - id'; return $ret;}
	if ((!isset($p_arr['spital']))||($p_arr['spital'] == '')){$ret['error'] = 'check field - spital'; return $ret;}
	
	$ret['catid'] = 0;
	$ret['specializari_user_spitale_grid'] = cs('specializari_user_spitale/grid', array(
		"filters"=>array("rules"=>array(
			array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
			array("field"=>"user","op"=>"eq","data"=>$p_arr['id']),
		)),
		"sidx" => "specializare",
		"sord" => "asc",
	));
	if (!isset($ret['specializari_user_spitale_grid']['success']) || ($ret['specializari_user_spitale_grid']['success'] != true)) return $ret;
	if ($ret['specializari_user_spitale_grid']['resp']['records'] > 0){
		$ret['catid'] = $ret['specializari_user_spitale_grid']['resp']['rows'][0]->specializare;
	}
	
	$ret['specializari_user_spitale_tags_a'] = cs('specializari_user_spitale/tags_a', array("user"=>$p_arr['id'],"spital"=>$p_arr['spital'],"callback"=>'specializari_tagdelete'));
	if (!isset($ret['specializari_user_spitale_tags_a']['success']) || ($ret['specializari_user_spitale_tags_a']['success'] != true)) return $ret;
	
	$ret['specializari_breadcrumb'] = cs('specializari/breadcrumb', array('catid'=>$ret['catid'],'callback'=>'specializari_inputedit_onclick'));
	if (!isset($ret['specializari_breadcrumb']['success']) ||  ($ret['specializari_breadcrumb']['success'] != true)) return $ret;

	$ret['specializari_list_a'] = cs('specializari/list_a',array('catid'=>$ret['catid'],'callback'=>'specializari_inputedit_onclick'));
	if (!isset($ret['specializari_list_a']['success']) ||  ($ret['specializari_list_a']['success'] != true)) return $ret;
	ob_start(); 
	?><div id="specializari_inputedit_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-tags" aria-hidden="true"></i> Alege specializare</h4>
				</div>
				<div class="modal-body">
					<div class="specializari_breadcrumb">
					<?php echo $ret['specializari_breadcrumb']['resp']['html']?>
					</div>
					<div class="specializari_tags">
					<?php echo $ret['specializari_user_spitale_tags_a']['resp']['html']?>
					</div>
					<form id="specializari_inputedit_form">
						<div class="list-group">
							<?php echo $ret['specializari_list_a']['resp']['html']?>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success" onclick="specializari_inputedit_saveonclick()"><i class="fa fa-floppy-o" aria-hidden="true"></i> Salveaza</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal --><script>
		specializari_breadcrumb_nodearr = <?php echo json_encode($ret['specializari_breadcrumb']['resp']['nodearr'])?>;
		specializari_list_a = <?php echo json_encode($ret['specializari_list_a'])?>;
		specializari_user_spitale_tags_a = <?php echo json_encode($ret['specializari_user_spitale_tags_a'])?>;
		specializari_breadcrumb_parentid = 0;
		specializari_breadcrumb_parentparent = 0;
		specializari_inputedit_saveonclick = function(){
			var tagids = $('#specializari_user_spitale_tags_a .specializari_tag').map(function(i,e){return parseInt(e.dataset.sid)}).get()
			cs('specializari_user_spitale/userset',{
				user:<?php echo $p_arr['id']?>,
				spital:<?php echo $p_arr['spital']?>,
				specializari:tagids
			}).then(function(specializari_user_spitale_userset){
				$("#specializari_inputedit_modal").modal('hide')
				window.location.reload()
			})
		}
		specializari_tagdelete = function(sid){
			$('#specializari_user_spitale_tags_a .specializari_tag').each(function(i,e){
				if (parseInt(e.dataset.sid) == sid){
					$(e).remove()
					return false
				}
			})
		}
		specializari_inputedit_onclick = function(catid){
			cs('specializari/list_a',{
				catid:catid,
				callback:'specializari_inputedit_onclick',
				then:['specializari/breadcrumb',{catid:catid,callback:'specializari_inputedit_onclick'}]
			})
			.then(function(specializari_list_a,specializari_breadcrumb){
				if (typeof(specializari_list_a.then) == 'undefined') {console.error('where then')}
				specializari_breadcrumb = specializari_list_a.then
				$("#specializari_inputedit_modal div.specializari_breadcrumb").html(specializari_breadcrumb.resp.html)
				specializari_breadcrumb_nodearr = specializari_breadcrumb.resp.nodearr
				specializari_breadcrumb_parentid = 0
				specializari_breadcrumb_parentparent = 0
				if (specializari_breadcrumb_nodearr.length > 0) specializari_breadcrumb_parentid = specializari_breadcrumb_nodearr[0]['id']
				if (specializari_breadcrumb_nodearr.length > 1) specializari_breadcrumb_parentparent = specializari_breadcrumb_nodearr[1]['id']
				window.specializari_list_a = specializari_list_a
				specializari_inputedit_listdraw()
			})
		}
		specializari_checkbox_click = function(e,d){
			//console.log(e,d)
			if (e.target.checked){
				var div = $('<div/>',{
					class:'specializari_tag',
					'data-sid':d.id,
					html: d.parentname + '/' + d.denumire
				})
				var i = $('<i/>',{
					class:"fa fa-times-circle-o",
					'aria-hidden':"true",
					click:function(){specializari_tagdelete(d.id)}
				})
				div.append(i)
				$('#specializari_user_spitale_tags_a').append(div)
			}else{
				$('#specializari_user_spitale_tags_a .specializari_tag').each(function(i,e){
					//console.log(i)
					if (parseInt(e.dataset.sid) == d.id){
						$(e).remove()
						return false
					}
				})
			}
		}
		specializari_inputedit_listdraw = function(){
			if (specializari_breadcrumb_nodearr.length == 0){
				$("#specializari_inputedit_modal div.modal-body div.list-group").html(specializari_list_a.resp.html)
			}else{
				var tagids = $('#specializari_user_spitale_tags_a .specializari_tag').map(function(i,e){return parseInt(e.dataset.sid)}).get()
				//console.log(tagids)
				$("#specializari_inputedit_modal div.modal-body div.list-group").empty()
				$("#specializari_inputedit_modal div.modal-body div.list-group").append(
					specializari_list_a.resp.specializari_grid.resp.rows.map(function(d,i){
						//console.log(d)
						var div = $('<div/>',{
							class:'list-group-item',
						})
						if (d.isparent){
							div.append(d.denumire)
							div.on('click',function(){specializari_inputedit_onclick(d.id)}) 
						}else{
							var label = $('<label/>')
							div.addClass('checkbox')
							var checkbox_options = {
								type:'checkbox',
								'data-sid':d.id,
								click:function(e){specializari_checkbox_click(e,d)}
							}
							var found = tagids.indexOf(parseInt(d.id))
							if (found >= 0) {
								checkbox_options.checked = 'checked';
							}
							var checkbox = $('<input/>',checkbox_options)
							label.append(checkbox)
							label.append(' ' + d.denumire)
						}
						div.append(label)
						return div
					})
				)
			}
		}
		$("div.modal-backdrop.fade.in").remove()
		$("#specializari_inputedit_modal").modal('show')
		specializari_inputedit_listdraw()
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function specializari_inputchoose_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['catid'])) { $p_arr['catid'] = 0; } 
	if (!isset($p_arr['callback'])) { $p_arr['callback'] = 'javascript:void'; } 
	
	
	$ret['specializari_breadcrumb'] = cs('specializari/breadcrumb', array('catid'=>$p_arr['catid'],'callback'=>'specializari_inputchoose_onclick'));
	if (!isset($ret['specializari_breadcrumb']['success']) ||  ($ret['specializari_breadcrumb']['success'] != true)) return $ret;

	$options = array(
		'catid'=>$p_arr['catid'],
		'callback'=>'specializari_inputchoose_onclick',
	);
	if (isset($p_arr['active'])) { 
		$options['catid'] = (count($ret['specializari_breadcrumb']['resp']['nodearr']) > 1)?$ret['specializari_breadcrumb']['resp']['nodearr'][1]['id']:0;
		$options['active'] = $p_arr['catid'];
	} 

	$ret['specializari_list_a'] = cs('specializari/list_a',$options);
	if (!isset($ret['specializari_list_a']['success']) ||  ($ret['specializari_list_a']['success'] != true)) return $ret;
	
	ob_start(); 
	?><div id="specializari_inputchoose_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-tags" aria-hidden="true"></i> Alege specializare</h4>
				</div>
				<div class="modal-body">
					<div class="specializari_breadcrumb">
					<?php echo $ret['specializari_breadcrumb']['resp']['html']?>
					</div>
					<div class="list-group">
					<?php echo $ret['specializari_list_a']['resp']['html']?>
					</div>
				</div>
				<div class="modal-footer">
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal --><script>
		$("div.modal-backdrop.fade.in").remove()
		$("#specializari_inputchoose_modal").modal('show')
		specializari_inputchoose_level = 0;
		specializari_inputchoose_onclick = function(catid){
			cs('specializari/breadcrumb',{catid:catid,callback:'specializari_inputchoose_onclick'}).then(function(specializari_breadcrumb){
				<?php if (isset($p_arr['maxlevel'])) { ?>
				if (<?php echo $p_arr['maxlevel']; ?> <= specializari_breadcrumb.resp.nodearr.length){
					<?php echo $p_arr['callback']; ?>(catid);
					$("#specializari_inputchoose_modal").modal('hide')
				}
				<?php }?>
				$("#specializari_inputchoose_modal div.specializari_breadcrumb").html(specializari_breadcrumb.resp.html)
			})
			cs('specializari/list_a',{catid:catid,callback:'specializari_inputchoose_onclick'}).then(function(specializari_list_a){
				if (specializari_list_a['resp']['specializari_grid']['resp']['records'] > 0){
					$("#specializari_inputchoose_modal div.modal-body div.list-group").html(specializari_list_a.resp.html)
				}else{
					<?php echo $p_arr['callback']; ?>(catid);
					$("#specializari_inputchoose_modal").modal('hide')
				}
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function specializari_list_a($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') ,'error'=>'');
	if (!isset($p_arr['catid'])) { $p_arr['catid'] = 0; } 
	if (!isset($p_arr['active'])) { $p_arr['active'] = 0; } 
	if (!isset($p_arr['callback'])) { $p_arr['callback'] = 'javascript:void'; } 
	if (!isset($p_arr['element'])) { $p_arr['element'] = 'a'; } 
	$ret['resp']['specializari_grid'] = cs('specializari/grid',array(
		"filters" => array("rules"=>array(
			array("field"=>"parent","op"=>"eq","data"=>$p_arr['catid']),
		)),
		"sidx" => "sorder",
		"sord" => "asc",
	));
	if (!isset($ret['resp']['specializari_grid']['success']) || ($ret['resp']['specializari_grid']['success'] != true)) return $ret;
	
	ob_start(); 
	?><?php foreach ($ret['resp']['specializari_grid']['resp']['rows'] as $specializare){
		$active = '';
		if ($specializare->id == $p_arr['active']) $active = 'active';
		$specializare->isparent = false;
		$specializari_get = cs('specializari/get', array(
			"filters"=>array("rules"=>array(
				array("field"=>"parent","op"=>"eq","data"=>$specializare->id),
			)),
		));
		if ($specializari_get != null) $specializare->isparent = true;
		?>
		<<?php echo $p_arr['element']?> class="list-group-item <?php echo $active?> " href="javascript:void(0)" onclick="<?php echo $p_arr['callback'] . '(' . $specializare->id . ')'; ?>" data-id="<?php echo $specializare->id?>" data-sorder="<?php echo $specializare->sorder?>"><?php echo $specializare->denumire?></<?php echo $p_arr['element']?>>
	<?php }?><script>
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function specializari_breadcrumb($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'error'=>'');
	if (!isset($p_arr['callback']) || ($p_arr['callback'] == '')){$p_arr['callback'] = "javascript:void";}
	if (!isset($p_arr['catid'])) { $p_arr['catid'] = 0; } 
	$ret['resp']['nodearr'] = array();


	if (intval($p_arr['catid']) > 0){
		$specializari_get = specializari_get(array("filters" => array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$p_arr['catid']),
		))));
		
		if ($specializari_get == null) {
			//$ret['error'] = 'e1 specializari_get parent ' . $p_arr['catid']; return $ret;
			$ret['reset'] = '';
			$ret['resp']['nodearr'] = array();
		}
		$ret['resp']['nodearr'][] = $specializari_get;
		while($specializari_get['parent'] != 0){
			$specializari_get = specializari_get(array("filters" => array("rules"=>array(
				array("field"=>"id","op"=>"eq","data"=>$specializari_get['parent']),
			))));
			if ($specializari_get == null) {
				//$ret['error'] = 'e2 specializari_get parent ' . $specializari_get['parent']; return $ret;
				$ret['reset'] = '';
				$ret['resp']['nodearr'] = array();
				break;
			}
			$ret['resp']['nodearr'][] = $specializari_get;
		}
	}
	$nodearr_count = count($ret['resp']['nodearr']); 
	ob_start(); 
	?> <ol id="specializari_breadcrumb" class="breadcrumb"> <?php 
			if ($nodearr_count == 0){
				echo '<li class="active">Alege specializare</li>';
			}else{
				echo '<li><a href="javascript:void(0)" onclick="event.stopPropagation(); event.preventDefault();' . $p_arr['callback'] . '(0); return false;">Specializari</a></li>';
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