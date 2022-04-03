<?php
function cauta_specializari_grid($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$sql_where_and = ' where ';
	$ret['localitatisql'] = '';
	if (isset($p_arr['localitati']) && (intval($p_arr['localitati'])>0)){ 
		$ret['localitatisql'] =  " LEFT JOIN localitati_spitale ON localitati_spitale.spital = specializari_user_spitale.spital";
		$ret['localitatisql'] .=  $sql_where_and . ' specializari.denumire is not NULL';
		$ret['localitatisql'] .=  '  AND localitati_spitale.localitate = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['localitati']);
		$sql_where_and = ' and ';
	}
	$ret['parentsql'] = '';
	if (isset($p_arr['parent'])){
		$ret['parentsql'] .=  $sql_where_and . ' specializari.parent = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['parent']);
		$sql_where_and = ' and ';
	} 
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  'specializari.php');
	global $specializari_cols;
	$p_arr['db_cols'] = $specializari_cols;
	$p_arr['db_table'] = 'specializari';
	$p_arr['db_sql'] = "SELECT DISTINCT specializari.id, specializari.uri, specializari.parent, specializari.denumire, specializari.sorder";
	$p_arr['db_sql'] .= " FROM specializari_user_spitale";
	$p_arr['db_sql'] .=  " LEFT JOIN specializari ON specializari.id = specializari_user_spitale.specializare";
	$p_arr['db_sql'] .=  $ret['localitatisql'];
	$p_arr['db_sql'] .=  $ret['parentsql'];
	$p_arr['db_sql'] .=  " ORDER BY specializari.sorder ASC __limit__";
	$ret['p_arr'] = $p_arr;
	$ret['db_resp'] = cs("_cs_grid/get",$p_arr);
	if (!isset($ret['db_resp']['success'])||($ret['db_resp']['success'] != true)){return $ret;}
	$ret['resp'] = $ret['db_resp']['resp'];
	$ret['success'] = true;
	return $ret;
}
function cauta_localitati_grid($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$sql_where_and = ' where ';
	$ret['specializarisql'] = '';
	if (isset($p_arr['specializari']) && (intval($p_arr['specializari'])>0)){ 
		$ret['specializarisql'] =  " LEFT JOIN specializari_user_spitale ON specializari_user_spitale.spital = localitati_spitale.spital";
		$ret['specializarisql'] .=  $sql_where_and . ' localitati.denumire is not NULL';
		$ret['specializarisql'] .=  '  AND specializari_user_spitale.specializare = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['specializari']);
		$sql_where_and = ' and ';
	}
	$ret['parentsql'] = '';
	if (isset($p_arr['parent'])){
		$ret['parentsql'] .=  $sql_where_and . ' localitati.parent = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['parent']);
		$sql_where_and = ' and ';
	} 
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  'localitati.php');
	global $localitati_cols;
	$p_arr['db_cols'] = $localitati_cols;
	$p_arr['db_table'] = 'localitati';
	$p_arr['db_sql'] = "SELECT DISTINCT localitati.id, localitati.uri, localitati.parent, localitati.denumire";
	$p_arr['db_sql'] .= " FROM localitati_spitale";
	$p_arr['db_sql'] .=  " LEFT JOIN localitati ON localitati.id = localitati_spitale.localitate";
	$p_arr['db_sql'] .=  $ret['specializarisql'];
	$p_arr['db_sql'] .=  $ret['parentsql'];
	$p_arr['db_sql'] .=  " ORDER BY localitati.denumire ASC __limit__";
	$ret['p_arr'] = $p_arr;
	$ret['db_resp'] = cs("_cs_grid/get",$p_arr);
	if (!isset($ret['db_resp']['success'])||($ret['db_resp']['success'] != true)){return $ret;}
	$ret['resp'] = $ret['db_resp']['resp'];
	$ret['success'] = true;
	return $ret;
}
function cauta_specializari_list_a($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') ,'error'=>'');
	$ret['p_arr'] = $p_arr;
	if (!isset($p_arr['catid'])) { $p_arr['catid'] = 0; } 
	if (!isset($p_arr['active'])) { $p_arr['active'] = 0; } 
	if (!isset($p_arr['callback'])) { $p_arr['callback'] = 'javascript:void'; } 
	if (!isset($p_arr['element'])) { $p_arr['element'] = 'a'; } 

	$cauta_specializari_grid_opt = array('parent'=>$p_arr['catid']);
	if (isset($p_arr['localitati'])) { $cauta_specializari_grid_opt['localitati'] = $p_arr['localitati']; } 
	
	$ret['resp']['specializari_grid'] = cs('cauta/specializari_grid',$cauta_specializari_grid_opt);
	if (!isset($ret['resp']['specializari_grid']['success']) || ($ret['resp']['specializari_grid']['success'] != true)) return $ret;
	
	ob_start(); 
	?><?php foreach ($ret['resp']['specializari_grid']['resp']['rows'] as $specializare){
		$active = '';
		if ($specializare->id == $p_arr['active']) $active = 'active';
		$specializare->isparent = false;
		$specializari_get = cs('specializari/get', array(
			"filters"=>array("rules"=>array(
				array("field"=>"parent","op"=>"eq","data"=>$specializare->id),
			)),//localit
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
function cauta_localitati_list_a($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') ,'error'=>'');
	$ret['p_arr'] = $p_arr;
	if (!isset($p_arr['locid'])) { $p_arr['locid'] = 0; } 
	if (!isset($p_arr['active'])) { $p_arr['active'] = 0; } 
	if (!isset($p_arr['callback'])) { $p_arr['callback'] = 'javascript:void'; } 
	if (!isset($p_arr['element'])) { $p_arr['element'] = 'a'; } 

	$cauta_localitati_grid_opt = array('parent'=>$p_arr['locid']);
	if (isset($p_arr['specializari'])) { $cauta_localitati_grid_opt['specializari'] = $p_arr['specializari']; } 

	$ret['resp']['localitati_grid'] = cs('cauta/localitati_grid',$cauta_localitati_grid_opt);
	if (!isset($ret['resp']['localitati_grid']['success']) || ($ret['resp']['localitati_grid']['success'] != true)) return $ret;
	
	ob_start(); 
	?><?php foreach ($ret['resp']['localitati_grid']['resp']['rows'] as $localitate){
		$active = '';
		if ($localitate->id == $p_arr['active']) $active = 'active';
		$localitate->isparent = false;
		$localitati_get = cs('localitati/get', array(
			"filters"=>array("rules"=>array(
				array("field"=>"parent","op"=>"eq","data"=>$localitate->id),
			)),
		));
		if ($localitati_get != null) $localitate->isparent = true;
		?>
		<<?php echo $p_arr['element']?> class="list-group-item <?php echo $active?> " href="javascript:void(0)" onclick="<?php echo $p_arr['callback'] . '(' . $localitate->id . ')'; ?>" data-id="<?php echo $localitate->id?>" ><?php echo $localitate->denumire?></<?php echo $p_arr['element']?>>
	<?php }?><script>
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function cauta_specializari_inputchoose_rs($p_arr = array()){
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
	if (isset($p_arr['localitati'])) { 
		$options['localitati'] = $p_arr['localitati'];
	} 

	$ret['specializari_list_a'] = cs('cauta/specializari_list_a',$options);
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
					<button type="button" class="btn btn-danger" onclick="$('#specializari_inputchoose_modal').modal('hide')">Anuleaza</button>
					<button type="button" class="btn btn-success" onclick="<?php echo $p_arr['callback']; ?>(specializari_inputchoose_catid);$('#specializari_inputchoose_modal').modal('hide')">Alege</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal --><script>
		$("div.modal-backdrop.fade.in").remove()
		$("#specializari_inputchoose_modal").modal('show')
		specializari_inputchoose_catid = <?php echo $p_arr['catid']?>;
		specializari_inputchoose_level = 0;
		specializari_inputchoose_onclick = function(catid){
			specializari_inputchoose_catid = catid
			cs('specializari/breadcrumb',{catid:catid,callback:'specializari_inputchoose_onclick'}).then(function(specializari_breadcrumb){
				<?php if (isset($p_arr['maxlevel'])) { ?>
				if (<?php echo $p_arr['maxlevel']; ?> <= specializari_breadcrumb.resp.nodearr.length){
					<?php echo $p_arr['callback']; ?>(catid);
					$("#specializari_inputchoose_modal").modal('hide')
				}
				<?php }?>
				$("#specializari_inputchoose_modal div.specializari_breadcrumb").html(specializari_breadcrumb.resp.html)
			})
			cs('cauta/specializari_list_a',{<?php if (isset($p_arr['localitati'])) echo 'localitati:' . $p_arr['localitati'] . ','?>catid:catid,callback:'specializari_inputchoose_onclick'}).then(function(specializari_list_a){
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
function cauta_localitati_inputchoose_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['locid'])) { $p_arr['locid'] = 0; } 
	if (!isset($p_arr['callback'])) { $p_arr['callback'] = 'javascript:void'; } 
	
	
	$ret['localitati_breadcrumb'] = cs('localitati/breadcrumb', array('locid'=>$p_arr['locid'],'callback'=>'localitati_inputchoose_onclick'));
	if (!isset($ret['localitati_breadcrumb']['success']) ||  ($ret['localitati_breadcrumb']['success'] != true)) return $ret;

	$options = array(
		'locid'=>$p_arr['locid'],
		'callback'=>'localitati_inputchoose_onclick',
	);
	if (isset($p_arr['active'])) { 
		$options['locid'] = (count($ret['localitati_breadcrumb']['resp']['nodearr']) > 1)?$ret['localitati_breadcrumb']['resp']['nodearr'][1]['id']:0;
		$options['active'] = $p_arr['locid'];
	} 
	if (isset($p_arr['specializari'])) { 
		$options['specializari'] = $p_arr['specializari'];
	} 

	$ret['localitati_list_a'] = cs('cauta/localitati_list_a',$options);
	if (!isset($ret['localitati_list_a']['success']) ||  ($ret['localitati_list_a']['success'] != true)) return $ret;
	
	ob_start(); 
	?><div id="localitati_inputchoose_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-map" aria-hidden="true"></i> Alege localitatea</h4>
				</div>
				<div class="modal-body">
					<div class="localitati_breadcrumb">
					<?php echo $ret['localitati_breadcrumb']['resp']['html']?>
					</div>
					<div class="list-group">
					<?php echo $ret['localitati_list_a']['resp']['html']?>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" onclick="$('#localitati_inputchoose_modal').modal('hide')">Anuleaza</button>
					<button type="button" class="btn btn-success" onclick="<?php echo $p_arr['callback']; ?>(localitati_inputchoose_locid);$('#localitati_inputchoose_modal').modal('hide')">Alege</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal --><script>
		$("div.modal-backdrop.fade.in").remove()
		$("#localitati_inputchoose_modal").modal('show')
		localitati_inputchoose_locid = <?php echo $p_arr['locid']?>;
		localitati_inputchoose_level = 0;
		localitati_inputchoose_onclick = function(locid){
			localitati_inputchoose_locid = locid
			cs('localitati/breadcrumb',{locid:locid,callback:'localitati_inputchoose_onclick'}).then(function(localitati_breadcrumb){
				<?php if (isset($p_arr['maxlevel'])) { ?>
				if (<?php echo $p_arr['maxlevel']; ?> <= localitati_breadcrumb.resp.nodearr.length){
					<?php echo $p_arr['callback']; ?>(locid);
					$("#localitati_inputchoose_modal").modal('hide')
				}
				<?php }?>
				$("#localitati_inputchoose_modal div.localitati_breadcrumb").html(localitati_breadcrumb.resp.html)
			})
			cs('cauta/localitati_list_a',{<?php if (isset($p_arr['specializari'])) echo 'specializari:' . $p_arr['specializari'] . ','?>locid:locid,callback:'localitati_inputchoose_onclick'}).then(function(localitati_list_a){
				if (localitati_list_a['resp']['localitati_grid']['resp']['records'] > 0){
					$("#localitati_inputchoose_modal div.modal-body div.list-group").html(localitati_list_a.resp.html)
				}else{
					<?php echo $p_arr['callback']; ?>(locid);
					$("#localitati_inputchoose_modal").modal('hide')
				}
			})
		}
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function cauta_uridecode($p_arr = array()){
	$ret = array(
		'success'=>false,
		'error'=>'',
		'items' => array(
			'localitati'=>array(),
			'specializari'=>array(),
		),
		'rest'=>array(),
		'rootstr'=>'doctori',
	);
	if (isset($p_arr['rootstr'])) $ret['rootstr'] = $p_arr['rootstr'];
	$itemnames = array_keys($ret['items']);
	$itemnamei = 0;
	if (!isset($p_arr['REQUEST_URI'])){$p_arr['REQUEST_URI'] = $_SERVER['REQUEST_URI']; }
	$ret['p_arr'] = $p_arr;
	$parsed = parse_url($p_arr['REQUEST_URI']);
	preg_match_all('/\/' . $ret['rootstr'] . '(\/?.*)$/mi', $parsed['path'], $ret['matches'], PREG_SET_ORDER, 0);
	if (!isset($ret['matches'][0]) || (count($ret['matches'][0]) < 2)){
		$ret['error'] = 'wrong input.. '; return $ret;
	} 
	$ret['uri'] = $ret['matches'][0][1];
	$ret['uri_a'] = explode('/',$ret['uri']);
	$ret['uri_a1'] = array();
	foreach($ret['uri_a'] as $uri_ak=>$uri_av){
		if ($uri_av != ''){
			$ret['uri_a1'][] = $uri_av;
		}
	}
	$count = count($ret['uri_a1']);
	$i = 0;
	while($i < $count){
		$filters = array(
			"filters"=>array("rules"=>array(
				array("field"=>"uri","op"=>"eq","data"=>$ret['uri_a1'][$i]),
		)));
		$found_count = count($ret['items'][$itemnames[$itemnamei]]);
		if ($found_count > 0) {
			$filters['filters']['rules'][] = array("field"=>"parent","op"=>"eq","data"=>$ret['items'][$itemnames[$itemnamei]][$found_count - 1]['id']);
		}else{
			$filters['filters']['rules'][] = array("field"=>"parent","op"=>"eq","data"=>0);
		}
			
		$resp = cs($itemnames[$itemnamei] . '/get',$filters);
		if ($resp != null){
			$ret['items'][$itemnames[$itemnamei]][] = $resp;
			$i++;
		}else{
			if (($itemnamei + 1) < count($ret['items'])){
				$itemnamei++;
				continue;
			}else{
				break;
			}
		}
	}
	if ($i != $count){
		for($j = $i; $j < $count; $j++){
			$ret['rest'][] = $ret['uri_a1'][$j];
		}
	};
	
	$ret['success'] = true;
	return $ret;
}
function cauta_listhtmlwrapper($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>''));
	$ret['resp']['cauta_list'] = cs('cauta/list',$p_arr);
	if (!isset($ret['resp']['cauta_list']['success'])||($ret['resp']['cauta_list']['success'] != true)){return $ret;}
	if ($ret['resp']['cauta_list']['resp']['records'] == 0){$ret['success'] = true;return $ret;}
	ob_start();
	foreach($ret['resp']['cauta_list']['resp']['rows'] as $ret['item']){
		$ret['localitati_breadcrumb'] = cs('localitati/breadcrumb',array(
			'locid'=>$ret['item']->localitati_id,
		));
		if (!isset($ret['localitati_breadcrumb']['success'])||($ret['localitati_breadcrumb']['success'] != true)){ob_end_clean();return $ret;}
		$ret['localitate_uri'] = array();
		foreach($ret['localitati_breadcrumb']['resp']['nodearr'] as $item_sd){
			array_unshift($ret['localitate_uri'],$item_sd['uri']);
		}
		$ret['localitate_uri']  = implode('/',$ret['localitate_uri']);
		?>
		<div class="row listitem">
			<div class="col-sm-4">
				<div class="row">
					<div class="col-xs-12">
						<div class="cauta_item_pic">
							<?php if ($ret['item']->doctor_image > 0){?>
							<a href="<?php echo cs_url . '/doctori/' . $ret['localitate_uri'] . '/' . $ret['item']->spitale_uri. '/' . $ret['item']->doctor_uri ?>">
								<img id="user_image_img" src="<?php echo cs_url."/csapi/images/view/?thumb=0&id=" . $ret['item']->doctor_image;?>" class="cauta_imageaccount" alt="User Image">
							</a>
							<?php }?>
						</div>
						<div class="cauta_item_info">
							<?php if ($ret['item']->spitale_logo > 0){?>
							<a href="<?php echo cs_url . '/doctori/' . $ret['localitate_uri'] . '/' . $ret['item']->spitale_uri ?>">
								<img src="<?php echo cs_url."/csapi/images/view/?thumb=0&id=" . $ret['item']->spitale_logo;?>" class="cauta_imagespital" alt="spital Image">
							</a>
							<?php }?>
							<div>
								<a href="<?php echo cs_url . '/doctori/' . $ret['localitate_uri'] . '/' . $ret['item']->spitale_uri ?>">
									<?php echo $ret['item']->spitale_nume?>
								</a>
							</div>
							<div>
								<a href="<?php echo cs_url . '/doctori/' . $ret['localitate_uri'] . '/' . $ret['item']->spitale_uri. '/' . $ret['item']->doctor_uri ?>">
									<?php echo $ret['item']->doctor_nume?>
								</a>
							</div>
									
							<div><?php 
							$ret['specializari_breadcrumb'] = cs('specializari/breadcrumb',array(
								'catid'=>$ret['item']->specializari_id,
							));
							if (!isset($ret['specializari_breadcrumb']['success'])||($ret['specializari_breadcrumb']['success'] != true)){ob_end_clean();return $ret;}
							$item_specializare_denumire = array();
							foreach($ret['specializari_breadcrumb']['resp']['nodearr'] as $item_sd){
								array_unshift($item_specializare_denumire,$item_sd['denumire']);
							}
							echo implode('->',$item_specializare_denumire);
							
							?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-8">
				<div class="row">
					<div class="col-xs-12">
						Ore disponibile:
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
					<?php 
					if (strtotime(date('Y-m-d',strtotime($ret['item']->planificare_start))) > strtotime(date('Y-m-d',strtotime($ret['resp']['cauta_list']['date'])))){
						?>
						<a href="javascript:void(0)" class="interval_item" onclick="programari('<?php 
								echo $ret['localitate_uri'] 
									. '/' . $ret['item']->spitale_uri 
									. '/' . $ret['item']->doctor_uri 
								?>',{doctor:<?php echo $ret['item']->doctor_id
								?>,spital:<?php echo $ret['item']->spitale_id
								?>,y:<?php echo date('Y',strtotime($ret['item']->planificare_start))
								?>,m:<?php echo date('m',strtotime($ret['item']->planificare_start))
								?>,d:<?php echo date('d',strtotime($ret['item']->planificare_start))
								?>,h:<?php echo date('H',strtotime($ret['item']->planificare_start))
								?>,i:<?php echo date('i',strtotime($ret['item']->planificare_start))
								?>,specializare:<?php echo $ret['item']->specializari_id?>})">
						<?php echo 'Vezi programari incepand cu data de ' . date('d.m.Y',strtotime($ret['item']->planificare_start));?>
						</a>
						<?php	
					}else{
						//echo date('Y-m-d',strtotime($ret['item']->planificare_start));
						$ret['daycheck'] = cs('programari/daycheck',array(
							'doctor'=>$ret['item']->doctor_id,
							'spital'=>$ret['item']->spitale_id,
							'd'=>date('d',strtotime($ret['item']->planificare_start)),
							'm'=>date('m',strtotime($ret['item']->planificare_start)),
							'y'=>date('Y',strtotime($ret['item']->planificare_start)),
						));
						if (!isset($ret['daycheck']['success'])||($ret['daycheck']['success'] != true)){ob_end_clean();return $ret;}
						foreach($ret['daycheck']['resp'] as $interval){
							if (strtotime($interval['start']) > strtotime(date('Y-m-d H:i:s'))){
							?>
							<a href="javascript:void(0)" class="interval_item status_<?php echo $interval['status']?>" <?php if ($interval['status'] == 'disponibil'){?>onclick="finalizaezaprogramare({doctor:<?php echo $ret['item']->doctor_id
																				?>,spital:<?php echo $ret['item']->spitale_id
																				?>,y:<?php echo date('Y',strtotime($interval['start']))
																				?>,m:<?php echo date('m',strtotime($interval['start']))
																				?>,d:<?php echo date('d',strtotime($interval['start']))
																				?>,h:<?php echo date('H',strtotime($interval['start']))
																				?>,i:<?php echo date('i',strtotime($interval['start']))
																				?>,specializare:<?php echo $ret['item']->specializari_id?>})" <?php }?>>
								<?php echo date('H:i',strtotime($interval['start'])) ?>
							</a>
							<?php 
							}
						}
					}
					?>
					</div>
				</div>
			</div>
		</div>
	<?php }
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function cauta_list($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array());
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	if (!isset($p_arr['page'])){$p_arr['page'] = 1;}
	if (!isset($p_arr['rows'])){$p_arr['rows'] = 20;}
	
	if (!isset($p_arr['date']) || ($p_arr['date'] == '')) {
		$p_arr['date'] = date('Y-m-d H:i:s');
	}else{
		if (date('Y-m-d',strtotime($p_arr['date'])) != date('Y-m-d')){
			$p_arr['date'] = date('Y-m-d H:i:s',strtotime(
				date('Y',strtotime($p_arr['date']))
				. '-' . date('m',strtotime($p_arr['date']))
				. '-' . date('d',strtotime($p_arr['date']))
				. ' 00:00:00'
			));
		}else{
			$p_arr['date'] = date('Y-m-d H:i:s',strtotime(
				date('Y',strtotime($p_arr['date']))
				. '-' . date('m',strtotime($p_arr['date']))
				. '-' . date('d',strtotime($p_arr['date']))
				. ' ' . date('H')
				. ':' . date('i')
				. ':' . date('s')
			));
		}
	}
	$p_arr['date'] = date('Y-m-d H:i:s',strtotime(
		date('Y',strtotime($p_arr['date']))
		. '-' . date('m',strtotime($p_arr['date']))
		. '-' . date('d',strtotime($p_arr['date']))
		. ' ' . date('H',strtotime($p_arr['date']))
		. ':' . date('i',strtotime($p_arr['date']))
		. ':' . date('s',strtotime($p_arr['date']))
	));
	
	$ret['date'] = $GLOBALS['cs_db_conn']->real_escape_string($p_arr['date']);
	
	$ret['specializaresql'] = " AND specializari.parent = 0";
	if (isset($p_arr['specializare']) && (intval($p_arr['specializare']) > 0)){
		$ret['specializaresql'] = " AND specializari_user_spitale.specializare = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['specializare']);
	}
	$ret['localitatesql'] = "";
	if (isset($p_arr['localitate']) && (intval($p_arr['localitate']) > 0)){
		$ret['localitatesql'] = " AND localitati.id = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['localitate']);
	}
	
	$ret['cauta_sql'] = " SELECT SQL_CALC_FOUND_ROWS planificare_tmain.spital spitale_id";
	$ret['cauta_sql'] .= " 	, spitale.nume as spitale_nume";
	$ret['cauta_sql'] .= " 	, spitale.logo as spitale_logo";
	$ret['cauta_sql'] .= " 	, spitale.uri as spitale_uri";
	$ret['cauta_sql'] .= " 	, localitati.denumire as localitati_denumire";
	$ret['cauta_sql'] .= " 	, localitati.uri as localitati_uri";
	$ret['cauta_sql'] .= " 	, localitati.id as localitati_id";
	$ret['cauta_sql'] .= " 	, planificare_tmain.doctor as doctor_id";
	$ret['cauta_sql'] .= " 	, users.nume as doctor_nume";
	$ret['cauta_sql'] .= " 	, users.image as doctor_image";
	$ret['cauta_sql'] .= " 	, users.uri as doctor_uri";
	$ret['cauta_sql'] .= " 	, specializari.denumire as specializari_denumire";
	$ret['cauta_sql'] .= " 	, specializari.id as specializari_id";
	$ret['cauta_sql'] .= " 	, IFNULL((";
	$ret['cauta_sql'] .= " 		SELECT detaliiservicii.valoaredetaliu";
	$ret['cauta_sql'] .= " 		FROM detaliiservicii";
	$ret['cauta_sql'] .= " 		WHERE ";
	$ret['cauta_sql'] .= " 			detaliiservicii.doctor = planificare_tmain.doctor  ";
	$ret['cauta_sql'] .= " 			AND detaliiservicii.spital = planificare_tmain.spital  ";
	$ret['cauta_sql'] .= " 			AND detaliiservicii.numedetaliu = 'timp'";
	$ret['cauta_sql'] .= " 	),30) as detaliiservicii_timp";
	$ret['cauta_sql'] .= " 	, (";
	$ret['cauta_sql'] .= " 		SELECT planificare_tsub.start ";
	$ret['cauta_sql'] .= " 		FROM planificare planificare_tsub";
	$ret['cauta_sql'] .= " 		WHERE ";
	$ret['cauta_sql'] .= " 			planificare_tsub.doctor = planificare_tmain.doctor";
	$ret['cauta_sql'] .= " 			AND planificare_tsub.spital = planificare_tmain.spital";
	$ret['cauta_sql'] .= " 			AND planificare_tsub.stop >= '" . $ret['date'] . "'";
	$ret['cauta_sql'] .= " 			AND (";
	$ret['cauta_sql'] .= " 				SELECT COUNT(*) ";
	$ret['cauta_sql'] .= " 				FROM programari";
	$ret['cauta_sql'] .= " 				WHERE ";
	$ret['cauta_sql'] .= " 					'" . $ret['date'] . "' <= programari.start ";
	$ret['cauta_sql'] .= " 					AND planificare_tsub.stop >= programari.stop";
	$ret['cauta_sql'] .= " 					AND planificare_tsub.spital = programari.spital";
	$ret['cauta_sql'] .= " 					AND planificare_tsub.doctor = programari.doctor";
	$ret['cauta_sql'] .= " 			) < FLOOR(TIMESTAMPDIFF(minute,'" . $ret['date'] . "', planificare_tsub.stop) / detaliiservicii_timp)";
	$ret['cauta_sql'] .= " 		ORDER BY planificare_tsub.start ASC";
	$ret['cauta_sql'] .= " 		LIMIT 1";
	$ret['cauta_sql'] .= " 	) as planificare_start";
	$ret['cauta_sql'] .= " FROM planificare planificare_tmain";
	$ret['cauta_sql'] .= " LEFT JOIN users on users.id = planificare_tmain.doctor";
	$ret['cauta_sql'] .= " LEFT JOIN specializari_user_spitale on (specializari_user_spitale.spital = planificare_tmain.spital) AND (specializari_user_spitale.user = planificare_tmain.doctor)";
	$ret['cauta_sql'] .= " LEFT JOIN specializari on (specializari_user_spitale.specializare = specializari.id)";
	$ret['cauta_sql'] .= " LEFT JOIN spitale on (specializari_user_spitale.spital = spitale.id)";
	$ret['cauta_sql'] .= " LEFT JOIN localitati_spitale on (specializari_user_spitale.spital = localitati_spitale.spital)";
	$ret['cauta_sql'] .= " LEFT JOIN localitati on (localitati_spitale.localitate = localitati.id)";
	$ret['cauta_sql'] .= " WHERE spitale.activman = 1 ";
	$ret['cauta_sql'] .= " AND spitale.aprobat = 1 ";
	$ret['cauta_sql'] .= $ret['localitatesql'];
	$ret['cauta_sql'] .= $ret['specializaresql'];
	$ret['cauta_sql'] .= " GROUP BY ";
	$ret['cauta_sql'] .= " 	 planificare_tmain.doctor";
	$ret['cauta_sql'] .= " 	, planificare_tmain.spital";
	$ret['cauta_sql'] .= " 	, specializari_user_spitale.specializare ";
	$ret['cauta_sql'] .= " HAVING planificare_start IS NOT NULL ";
	$ret['cauta_sql'] .=  " __order__ __limit__";
	$ret['cauta'] = cs("_cs_grid/get",array(
		'db_sql'=>$ret['cauta_sql'],
		'page'=>$p_arr['page'],
		'rows'=>$p_arr['rows'],
	));
	if (!isset($ret['cauta']['success'])||($ret['cauta']['success'] != true)){return $ret;}
	$ret['p_arr'] = $p_arr;
	$ret['resp'] = $ret['cauta']['resp'];
	$ret['success'] = true;
	return $ret;
}

function cauta_side_ob($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	if (!isset($p_arr['nodes'])){return $ret;}
	$ret['p_arr'] = $p_arr;
	$nodecount = count($p_arr['nodes']);
	$array_keys = array_keys($p_arr['nodes']);
	ob_start();
	?> <div class="cauta_side">
		<a class="cauta_side_root" href="<?php echo cs_url . "/doctori"?>">Doctori</a> 
	<?php 
	$ret['pathprev'] = "";
	$ret['pathnext'] = "";
	$i = 0;
	$ret['restrict'] = array();
	foreach($p_arr['nodes'] as $nodekey=>$nodedata){
		if (count($nodedata)>0) $ret['restrict'][$nodekey] = $nodedata[count($nodedata)-1]['id'];
	}
	foreach($p_arr['nodes'] as $nodekey=>$nodedata){
		//$p_arr['nodes'][$i]['resp']
		echo "<b>" . ucfirst($nodekey) . "</b>";
		$ret['pathcurent'] = "";
		$ret['pathnext'] = "";
		foreach ($nodedata as $srcnodeitem) $ret['pathcurent'] .= "/" . $srcnodeitem['uri'];
		for ($j = $i + 1; $j < $nodecount; $j++){
			foreach ($p_arr['nodes'][$array_keys[$j]] as $srcnodeitem1) {
				$ret['pathnext'] .= "/" . $srcnodeitem1['uri'];
			}
		}
		$opt = array(
			'node' => $nodedata, 
			'nodekey' => $nodekey, 
			'pathprev' => $ret['pathprev'], 
			'pathnext' => $ret['pathnext'],
			'restrict' => $ret['restrict'],
		);
		if (isset($p_arr['callback'])) $opt['callback'] = $p_arr['callback'];
		if (isset($p_arr['QUERY_STRING'])) $opt['QUERY_STRING'] = $p_arr['QUERY_STRING'];
		$ret['cauta_side_part_ob'] = cauta_side_part_ob($opt);
		if (!isset($ret['cauta_side_part_ob']['success'])||($ret['cauta_side_part_ob']['success'] != true)){$ret['resp'] = ob_get_contents(); ob_end_clean();return $ret;}
		echo $ret['cauta_side_part_ob']['resp'];
		$ret['pathprev'] .= $ret['pathcurent'];	
		$i++;
	} ?>
	</div> <?php
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function cauta_side_part_ob($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['node'])){return $ret;}
	if (!isset($p_arr['nodekey'])){return $ret;}
	if (!isset($p_arr['pathprev'])){$p_arr['pathprev'] = "";}
	if (!isset($p_arr['pathnext'])){$p_arr['pathnext'] = "";}
	ob_start();
	//var_dump($p_arr);
	$tabs = "";
	$nodecount = count($p_arr['node']);
	$nodei = 0;
	$path = "";
	while($nodei < $nodecount){
		$opt = array(
			'link' => $p_arr['pathprev'] . $path . $p_arr['pathnext'],
			'name' => $tabs . $p_arr['node'][$nodei]['denumire'] . "&nbsp;<i class='fa fa-window-close-o' aria-hidden='true'></i>"
		);
		if (isset($p_arr['callback'])) $opt['callback'] = $p_arr['callback'];
		if (isset($p_arr['QUERY_STRING'])) $opt['QUERY_STRING'] = $p_arr['QUERY_STRING'];
		echo cauta_side_part_getink_ob($opt)['resp'];
		$tabs .= "&nbsp;&nbsp;";
		$path = $path .  "/" . $p_arr['node'][$nodei]['uri'];
		$nodei++;
	}
	$lastparentid = 0;
	if ($nodecount > 0)$lastparentid = intval($p_arr['node'][$nodecount - 1]['id']);
	$ret['lastchilds_params'] = array(
		"parent"=>$lastparentid,
		"rows"=>200,
	);
	if (isset($p_arr['restrict']) && (count($p_arr['restrict']) > 0)){
		foreach($p_arr['restrict'] as $rk=>$rv){
			$ret['lastchilds_params'][$rk] = $rv;
		}
	}
	$ret['lastchilds'] = cs('cauta' . '/' . $p_arr['nodekey'] . '_grid', $ret['lastchilds_params']);
	if (!isset($ret['lastchilds']['success'])||($ret['lastchilds']['success'] != true)) {$ret['resp'] = ob_get_contents(); ob_end_clean();return $ret;}
	
	if (count($ret['lastchilds']['resp']['rows']) > 0) foreach($ret['lastchilds']['resp']['rows'] as $row){
		$opt = array(
			'link' => $p_arr['pathprev'] . $path . "/" . $row->uri . $p_arr['pathnext'],
			'name' => $tabs. $row->denumire
		);
		if (isset($p_arr['callback'])) $opt['callback'] = $p_arr['callback'];
		if (isset($p_arr['QUERY_STRING'])) $opt['QUERY_STRING'] = $p_arr['QUERY_STRING'];
		echo cauta_side_part_getink_ob($opt)['resp'];
	}
	$ret['resp'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function cauta_side_part_getink_ob($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['link'])){return $ret;}
	if (!isset($p_arr['name'])){return $ret;}
	$QUERY_STRING = '';
	if (isset($p_arr['QUERY_STRING']) && $p_arr['QUERY_STRING'] != ''){$QUERY_STRING = '?' . $p_arr['QUERY_STRING'];}
	if (isset($p_arr['callback'])) {
		$ret['resp'] = "<a href='javascript:void(0)' onclick='" . $p_arr['callback'] . "(\"" . $p_arr['link'] . "\")' class='list-group-item'>" . $p_arr['name'] . "</a>";
	}else{
		$ret['resp'] = "<a href='" . cs_url . "/doctori" . $p_arr['link'] . $QUERY_STRING . "'>" . $p_arr['name'] . "</a>";
	}
	$ret['success'] = true;
	return $ret;
}
function cauta_side_wrapper_ob($p_arr = array()){
	$ret = array('success'=>false, 'resp' => array(), 'error'=>'');
	if (!isset($p_arr['REQUEST_URI'])) {$ret['error'] = 'mising param - REQUEST_URI'; return $ret;}
	if (!isset($p_arr['callback'])) {$ret['error'] = 'mising param - callback'; return $ret;}
	
	$ret['cauta_uridecode_ob'] = cauta_uridecode_ob(array('REQUEST_URI'=>$p_arr['REQUEST_URI']));
	if (!isset($ret['cauta_uridecode_ob']['success']) || ($ret['cauta_uridecode_ob']['success'] != true)) {return $ret;}
	
	$ret['cauta_side_ob'] = cauta_side_ob(array('nodes' => $ret['cauta_uridecode_ob']['resp'], 'callback' => $p_arr['callback']));
	if (!isset($ret['cauta_side_ob']['success']) || ($ret['cauta_side_ob']['success'] != true)) {return $ret;}

	$ret['resp']['html'] = $ret['cauta_side_ob']['resp']['html'];
	$ret['resp']['cauta_uridecode_ob'] = $ret['cauta_uridecode_ob'];
	$ret['success'] = true;
	return $ret;
}
function cauta_form($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>''));
	if (!isset($p_arr['cauta_initparams'])){
		$p_arr['cauta_initparams'] = cs('cauta/initparams');
		if (!isset($ret['cauta_initparams']['success']) || ($ret['cauta_initparams']['success'] == false)){$ret['error'] = 'cauta_initparams'; return $ret;}
	}
	ob_start(); 
	?>
	<form action="javascript:void(0)" onsubmit="cauta_submit(this)" class="navbar-form" role="search" id="cauta" method="POST">
		<div class="form-group">
			<div class="localitati_breadcrumb" onclick="cauta_localitati_onclick()">
				<?php echo $p_arr['cauta_initparams']['resp']['cauta_localitati_breadcrumb']['resp']['html']?>
			</div>
		</div>
		<div class="form-group">
			<div class="specializari_breadcrumb" onclick="cauta_specializari_onclick()">
				<?php echo $p_arr['cauta_initparams']['resp']['cauta_specializari_breadcrumb']['resp']['html']?>
			</div>
		</div>
		<div class="form-group">
			<input type="date" name="cautadate" class="form-control"  value="<?php 
				echo $p_arr['cauta_initparams']['resp']['cauta_date'];
				?>" min="<?php echo date('Y-m-d')?>" lang="ro-RO">
		</div>
		<button type="submit" class="btn btn-success"><i class="fa fa-search" aria-hidden="true"></i> Cauta</button>
	</form>
	<?php 
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function cauta_initparams($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array());
	if (!isset($p_arr['cauta_uridecode'])) { 
		if (!isset($p_arr['REQUEST_URI'])) {$p_arr['REQUEST_URI'] = '/doctori';} 
		$ret['cauta_uridecode'] = cs('cauta/uridecode',array('REQUEST_URI'=>$p_arr['REQUEST_URI'],'rootstr'=>'doctori')); 
		if (!isset($ret['cauta_uridecode']['success']) || ($ret['cauta_uridecode']['success'] == false)){
			$ret['error'] = 'check cauta_uridecode'; 
			return $ret;
		}
		$p_arr['cauta_uridecode'] = $ret['cauta_uridecode'];
	} 
	$ret['resp']['cauta_localitati_grid_params'] = array();
	$ret['resp']['cauta_specializari_grid_params'] = array();
	
	$ret['resp']['cauta_localitate_id']  = 0;
	if (count($p_arr['cauta_uridecode']['items']['localitati']) > 0){
		$ret['resp']['cauta_specializari_grid_params']['localitati'] = $p_arr['cauta_uridecode']['items']['localitati'][
			count($p_arr['cauta_uridecode']['items']['localitati']) - 1]['id'];
		$ret['resp']['cauta_localitate_id'] = $ret['resp']['cauta_specializari_grid_params']['localitati'];
	}

	$ret['resp']['cauta_specializare_id'] = 0;
	if (count($p_arr['cauta_uridecode']['items']['specializari']) > 0){
		$ret['resp']['cauta_localitati_grid_params']['specializari'] = $p_arr['cauta_uridecode']['items']['specializari'][
			count($p_arr['cauta_uridecode']['items']['specializari']) - 1]['id'];
		$ret['resp']['cauta_specializare_id'] = $ret['resp']['cauta_localitati_grid_params']['specializari'];
	}
	
	$ret['resp']['cauta_specializari_breadcrumb'] = cs('specializari/breadcrumb', array('catid'=>$ret['resp']['cauta_specializare_id'],'callback'=>'cauta_specializari_onclick'));
	if (!isset($ret['resp']['cauta_specializari_breadcrumb']) || ($ret['resp']['cauta_specializari_breadcrumb'] == false)){$ret['error'] = 'check cauta_specializari_breadcrumb'; return $ret;}

	$ret['resp']['cauta_localitati_breadcrumb'] = cs('localitati/breadcrumb', array('locid'=>$ret['resp']['cauta_localitate_id'],'callback'=>'cauta_localitati_onclick'));
	if (!isset($ret['resp']['cauta_localitati_breadcrumb']) || ($ret['resp']['cauta_localitati_breadcrumb'] == false)){$ret['error'] = 'check cauta_localitati_breadcrumb'; return $ret;}

	$ret['resp']['cauta_localitati_grid'] = cs('cauta/localitati_grid',$ret['resp']['cauta_localitati_grid_params']);
	if (!isset($ret['resp']['cauta_localitati_grid']) || ($ret['resp']['cauta_localitati_grid'] == false)){$ret['error'] = 'check cauta_localitati_grid'; return $ret;}

	$ret['resp']['cauta_date']  = date('Y-m-d',strtotime('+ 1 day',strtotime(date('Y-m-d'))));
	if (isset($_REQUEST['cautadate']) && $_REQUEST['cautadate'] != '') $ret['resp']['cauta_date'] = date('Y-m-d', strtotime($_REQUEST['cautadate']));
	if (strtotime($ret['resp']['cauta_date']) < strtotime(date('Y-m-d'))) $ret['resp']['cauta_date'] = date('Y-m-d');
	$ret['success'] = true;
	return $ret;
}

?>