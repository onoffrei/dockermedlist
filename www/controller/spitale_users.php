<?php
$GLOBALS['spitale_users_cols'] = array(
	'id'				=>array('type'=>'int',),
	'spital'			=>array('type'=>'int',),
	'user'				=>array('type'=>'int',),
	'level'				=>array('type'=>'int',	), //0-neautentificat, 1-autentificat,2-doctor,3-administratorspital,4-administratorsite
	'descriere'			=>array('type'=>'text',),
	'autoplanificare'	=>array('type'=>'int',),
	'cronstart'			=>array('type'=>'datetime',),
	'cronnext'			=>array('type'=>'datetime',),
	'croncontinue'		=>array('type'=>'datetime',),
); 
function spitale_users_get($p_arr = array()){
	$ret = null;
	$list = spitale_users_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["user"] = intval($list['resp']['rows'][0]->user);
		$ret["spital"] = intval($list['resp']['rows'][0]->spital);
		$ret["level"] = intval($list['resp']['rows'][0]->level);
		$ret["descriere"] = $list['resp']['rows'][0]->descriere;
		$ret["autoplanificare"] = intval($list['resp']['rows'][0]->autoplanificare);
		$ret["cronstart"] = $list['resp']['rows'][0]->cronstart;
		$ret["cronnext"] = $list['resp']['rows'][0]->cronnext;
		$ret["croncontinue"] = $list['resp']['rows'][0]->croncontinue;
	}
	return $ret;
}
function spitale_users_grid($p_arr = array()){
	global $spitale_users_cols;
	$p_arr['db_cols'] = $spitale_users_cols;
	$p_arr['db_table'] = 'spitale_users';
	return cs("_cs_grid/get",$p_arr);
}
function spitale_users_update($p_arr = array()){
	global $spitale_users_cols;
	$p_arr['db_cols'] = $spitale_users_cols;
	$p_arr['db_table'] = 'spitale_users';
	return cs("_cs_grid/update",$p_arr);
}
function spitale_users_getlevel($p_arr){
	$ret = array('success'=>false, 'resp'=>0 , 'error'=>'');
	
	if (isset($_SESSION['cs_users_id'])){
		$ret['resp'] = user_level_pacient;
	}
	if (isset($p_arr['spital']) && isset($_SESSION['cs_users_id'])){
		$ret['spitale_users_get'] = cs('spitale_users/get',array("filters"=>array("rules"=>array(
			array("field"=>"user","op"=>"eq","data"=>$_SESSION['cs_users_id']),
			array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		))));
		if ($ret['spitale_users_get'] != null) $ret['resp'] = $ret['spitale_users_get']['level'];
	}else{
		// return ($p_arr);
		$ret['spitale_users_get'] = cs('spitale_users/get',array("filters"=>array("rules"=>array(
			array("field"=>"user","op"=>"eq","data"=>$_SESSION['cs_users_id']),
		))));
		if ($ret['spitale_users_get'] != null) $ret['resp'] = $ret['spitale_users_get']['level'];
	}
	if (isset($_SESSION['cs_users_isadmin']) && ($_SESSION['cs_users_isadmin'] == true)){
		$ret['resp'] = user_level_admin;
	}
	$ret['success'] = true; 
	return $ret;
}
function spitale_users_spitalactivselect_paged($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>''));
	if (!isset($p_arr['spital_activ'])){ $ret['error'] = 'check param - spital_activ'; return $ret;}
	$p_arr['spital_activ'] = intval($p_arr['spital_activ']);
	if (!isset($_SESSION['cs_users_id'])){$ret['error'] = 'login first'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	if (isset($_SESSION['cs_users_isadmin']) && ($_SESSION['cs_users_isadmin'] == true)){
		$ret['spitale_grid'] = cs('spitale/grid',array(
			'sord'=>'desc',
			'sidx'=>'id',
			'page'=>$p_arr['page'],
			'rows'=>$p_arr['rows'],
		));
		if (!isset($ret['spitale_grid']['success']) || ($ret['spitale_grid']['success'] != true)) {return $ret;}
	}else{
		$ret['spitale_sql'] = 'SELECT spitale.id AS id ';
		$ret['spitale_sql'] .= '	, spitale.nume AS nume ';
		$ret['spitale_sql'] .= '	, spitale.uri AS uri ';
		$ret['spitale_sql'] .= ' FROM spitale_users';
		$ret['spitale_sql'] .= ' LEFT JOIN spitale ON spitale_users.spital = spitale.id';
		$ret['spitale_sql'] .= ' WHERE spitale_users.user = '. $_SESSION['cs_users_id'];
		$ret['spitale_grid'] = cs("_cs_grid/get",array('db_sql'=>$ret['spitale_sql']));
		if (!isset($ret['spitale_grid']['success']) || ($ret['spitale_grid']['success'] != true)) {return $ret;}
	}
	if ($ret['spitale_grid']['resp']['records'] == 0){$ret['error'] = 'zero';return $ret;}
	if ($ret['spitale_grid']['resp']['records'] == 0){$ret['error'] = 'zero';return $ret;}
	ob_start(); 
	?> 
					<div class="row">
						<div class="col-xs-12">
							<table class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>#</th>
										<th>nume</th>
									</tr>
								</thead>
								<tbody>
									<?php 
										foreach ($ret['spitale_grid']['resp']['rows'] as $spital){
									?>
									<tr class="<?php if (intval($spital->id) == $p_arr['spital_activ']) echo 'info'?>" onclick="window.location.href = setURLParameter({name:'p_spital',value:<?php echo $spital->id?>})">
										<td><?php echo $spital->id; ?></td>
										<td><?php echo $spital->nume; ?></td>
									</tr>
									<?php 	}?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 text-center">
							<nav aria-label="...">
								<ul class="pager">
									<li><a href="javascript:void(0)" onclick="pagination_button_click(-1)">Previous</a></li>
									<li><a href="javascript:void(0)" onclick="pagination_button_click(1)">Next</a></li>
								</ul>
							</nav>
							pagina <?php echo $ret['spitale_grid']['resp']['page']?> din <?php echo $ret['spitale_grid']['resp']['total']?>
							<script>
								pagination_button_click = function(next){
									var p_page = <?php echo $p_arr['page'];?>;
									var totalpages = <?php echo $ret['spitale_grid']['resp']['total'];?>;
									var param = <?php echo json_encode($p_arr);?>;
									var nextpage = p_page + next;
									if ((nextpage > 0) && (nextpage <=totalpages) && (nextpage != p_page)){
										console.log(nextpage)
										param.page = nextpage
										cs('spitale_users/spitalactivselect_paged',param).then(function(spitalactivselect_paged){
											if ((typeof(spitalactivselect_paged.success) != 'undefined') && (spitalactivselect_paged.success == true)){
												$('.paged_spitaleplace').html(spitalactivselect_paged.resp.html)
											}
										})
									}
								}
							</script>
						</div>
					</div>
	<?php 
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;

}
function spitale_users_spitalactivselect($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['spital_activ'])){ $ret['error'] = 'check param - spital_activ'; return $ret;}
	if (!isset($p_arr['page'])){$p_arr['page'] = 1;}
	if (!isset($p_arr['rows'])){$p_arr['rows'] = 10;}
	$p_arr['spital_activ'] = intval($p_arr['spital_activ']);
	
	if (!isset($_SESSION['cs_users_id'])){$ret['error'] = 'login first'; return $ret;}
	
	$ret['spitale_users_spitalactivselect_paged'] = cs("spitale_users/spitalactivselect_paged",$p_arr);
	if (!isset($ret['spitale_users_spitalactivselect_paged']['success']) || ($ret['spitale_users_spitalactivselect_paged']['success'] != true)) {return $ret;}
	$ret['pag_maxrows'] = $p_arr['rows'];
	$ret['pag_p_page'] = $p_arr['page'];
	
	ob_start(); 
	?> <div id="spitale_users_spitalactivselect_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Alege Spital</h4>
				</div>
				<div class="modal-body ">
					<div class="row">
						<div class="col-xs-12 paged_spitaleplace">
							<?php echo $ret['spitale_users_spitalactivselect_paged']['resp']['html']?>							
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
						</div>
					</div>
				</div>
				<div class="modal-footer">
				click to select
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal --> <script>
		$("div.modal-backdrop.fade.in").remove()
		$("#spitale_users_spitalactivselect_modal").modal('show')
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;
}

function spitale_users_spitalactivselect_old($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['spital_activ'])){ $ret['error'] = 'check param - spital_activ'; return $ret;}
	$p_arr['spital_activ'] = intval($p_arr['spital_activ']);
	
	if (!isset($_SESSION['cs_users_id'])){$ret['error'] = 'login first'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	
	if (isset($_SESSION['cs_users_isadmin']) && ($_SESSION['cs_users_isadmin'] == true)){
		$ret['spitale_grid'] = cs('spitale/grid',array(
			'sord'=>'desc',
			'sidx'=>'id',
		));
		if (!isset($ret['spitale_grid']['success']) || ($ret['spitale_grid']['success'] != true)) {return $ret;}
	}else{
		$ret['spitale_sql'] = 'SELECT spitale.id AS id ';
		$ret['spitale_sql'] .= '	, spitale.nume AS nume ';
		$ret['spitale_sql'] .= '	, spitale.uri AS uri ';
		$ret['spitale_sql'] .= ' FROM spitale_users';
		$ret['spitale_sql'] .= ' LEFT JOIN spitale ON spitale_users.spital = spitale.id';
		$ret['spitale_sql'] .= ' WHERE spitale_users.user = '. $_SESSION['cs_users_id'];
		$ret['spitale_grid'] = cs("_cs_grid/get",array('db_sql'=>$ret['spitale_sql']));
		if (!isset($ret['spitale_grid']['success']) || ($ret['spitale_grid']['success'] != true)) {return $ret;}
	}
	if ($ret['spitale_grid']['resp']['records'] == 0){$ret['error'] = 'zero';return $ret;}
	ob_start(); 
	?> <div id="spitale_users_spitalactivselect_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Alege Spital</h4>
				</div>
				<div class="modal-body">
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>nume</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								foreach ($ret['spitale_grid']['resp']['rows'] as $spital){
							?>
							<tr class="<?php if (intval($spital->id) == $p_arr['spital_activ']) echo 'info'?>" onclick="window.location.href = setURLParameter({name:'p_spital',value:<?php echo $spital->id?>})">
								<td><?php echo $spital->id; ?></td>
								<td><?php echo $spital->nume; ?></td>
							</tr>
							<?php 	}?>
						</tbody>
					</table>
				</div>
				<div class="modal-footer">
				click to select
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal --> <script>
		$("div.modal-backdrop.fade.in").remove()
		$("#spitale_users_spitalactivselect_modal").modal('show')
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function spitale_users_spitalactivinput($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'error'=>'');
	$ret['spitale_users_spitalactivget'] = cs('spitale_users/spitalactivget');
	if (!isset($ret['spitale_users_spitalactivget']['success']) || ($ret['spitale_users_spitalactivget']['success'] != true)) {return $ret;}
	
	if ($ret['spitale_users_spitalactivget']['resp'] != null){
		ob_start(); 
		?><input type="text" class="form-control" readonly value="<?php echo $ret['spitale_users_spitalactivget']['resp']['nume']?>" onclick="cs('spitale_users/spitalactivselect',{spital_activ:'<?php echo $ret['spitale_users_spitalactivget']['resp']['id']?>'})"><?php
		$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	}
	$ret['success'] = true;
	return $ret;
}
function spitale_users_spitalactivget($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>null , 'error'=>'');
	if (!isset($_SESSION['cs_users_id'])){$ret['error'] = 'login first'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	if (isset($_SESSION['cs_users_isadmin']) && ($_SESSION['cs_users_isadmin'] == true)){
		if (isset($_REQUEST['p_spital']) && (intval($_REQUEST['p_spital'])>0)){
			$ret['spitale_get'] = cs('spitale/get',array("filters"=>array("rules"=>array(
				array("field"=>"id","op"=>"eq","data"=>$_REQUEST['p_spital']),
			))));
			$ret['resp'] = $ret['spitale_get'];
			$ret['path'] = 0;
		}else{
			$ret['spitale_get'] = cs('spitale/get');
			$ret['resp'] = $ret['spitale_get'];
			$ret['path'] = 1;
		}
	}else{
		if (isset($_REQUEST['p_spital']) && (intval($_REQUEST['p_spital'])>0)){
			$ret['spitale_sql'] = 'SELECT spitale.id AS id ';
			$ret['spitale_sql'] .= '	, spitale.nume AS nume ';
			$ret['spitale_sql'] .= '	, spitale.uri AS uri ';
			$ret['spitale_sql'] .= ' FROM spitale_users';
			$ret['spitale_sql'] .= ' LEFT JOIN spitale ON spitale_users.spital = spitale.id';
			$ret['spitale_sql'] .= ' WHERE spitale_users.user = '. $_SESSION['cs_users_id'];
			$ret['spitale_sql'] .= ' AND spitale.id = '. $GLOBALS['cs_db_conn']->real_escape_string($_REQUEST['p_spital']);
			$ret['spitale_grid'] = cs("_cs_grid/get",array('db_sql'=>$ret['spitale_sql']));
			if (!isset($ret['spitale_grid']['success']) || ($ret['spitale_grid']['success'] != true)) {return $ret;}
			if ($ret['spitale_grid']['resp']['records'] > 0){
				$ret['resp'] =  json_decode(json_encode($ret['spitale_grid']['resp']['rows'][0]),true);
			}
			$ret['path'] = 2;
		}else{
			$ret['spitale_sql'] = 'SELECT spitale.id AS id ';
			$ret['spitale_sql'] .= '	, spitale.nume AS nume ';
			$ret['spitale_sql'] .= '	, spitale.uri AS uri ';
			$ret['spitale_sql'] .= ' FROM spitale_users';
			$ret['spitale_sql'] .= ' LEFT JOIN spitale ON spitale_users.spital = spitale.id';
			$ret['spitale_sql'] .= ' WHERE spitale_users.user = '. $_SESSION['cs_users_id'];
			$ret['spitale_grid'] = cs("_cs_grid/get",array('db_sql'=>$ret['spitale_sql']));
			if (!isset($ret['spitale_grid']['success']) || ($ret['spitale_grid']['success'] != true)) {return $ret;}
			if ($ret['spitale_grid']['resp']['records'] > 0){
				$ret['resp'] = json_decode(json_encode($ret['spitale_grid']['resp']['rows'][0]),true);
			}
			$ret['path'] = 3;
		}
	}	
	$ret['success'] = true; 
	return $ret;
}
function spitale_users_adminselectGetselected($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'error'=>'');
	if (!isset($p_arr['user'])){ $ret['error'] = 'check param - user'; return $ret;}
	
	$ret['spitalids'] = array();

	$ret['users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['user'])
	))));
	if ($ret['users_get'] == null) {return $ret;}
	
	if (isset($_SESSION['cs_users_isadmin']) && ($_SESSION['cs_users_isadmin'] == true)){
		$ret['spitale_grid'] = cs('spitale/grid');
		if (!isset($ret['spitale_grid']['success']) || ($ret['spitale_grid']['success'] != true)) {return $ret;}
		foreach($ret['spitale_grid']['resp']['rows'] as $su){$ret['spitalids'][] = intval($su->id);}
	}else{
		$ret['spitale_users_grid'] = cs('spitale_users/grid',array("filters"=>array("rules"=>array(
			array("field"=>"user","op"=>"eq","data"=>$p_arr['user']),
		))));
		if (!isset($ret['spitale_users_grid']['success']) || ($ret['spitale_users_grid']['success'] != true)) {return $ret;}
		foreach($ret['spitale_users_grid']['resp']['rows'] as $su){$ret['spitalids'][] = intval($su->spital);}
	}
	if (count($ret['spitalids']) > 0){
		$spitalindex = $ret['spitalids'][0];
		if (isset($_REQUEST['p_spital'])){
			if (in_array(
				intval($_REQUEST['p_spital']),
				$ret['spitalids']
			)) $spitalindex = intval($_REQUEST['p_spital']);
		}
		$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$spitalindex)
		))));
		if ($ret['spitale_get'] == null) {return $ret;}
		$ret['resp'] = $ret['spitale_get']; 
	}
	$ret['success'] = true; 
	return $ret;
}
function spitale_users_adminselectHtml($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array('html'=>'') , 'error'=>'');
	if (!isset($p_arr['user'])){ $ret['error'] = 'check param - user'; return $ret;}
	
	$ret['spitale_users_adminselectGetselected'] = cs('spitale_users/adminselectGetselected',array("user"=>$p_arr['user']));
	if (!isset($ret['spitale_users_adminselectGetselected']['success']) || ($ret['spitale_users_adminselectGetselected']['success'] != true)) {return $ret;}
	$ret['spitalids'] = array();

	$ret['users_get'] = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['user'])
	))));
	if ($ret['users_get'] == null) {return $ret;}
	
	if (isset($_SESSION['cs_users_isadmin']) && ($_SESSION['cs_users_isadmin'] == true)){
		$ret['spitale_grid'] = cs('spitale/grid');
		if (!isset($ret['spitale_grid']['success']) || ($ret['spitale_grid']['success'] != true)) {return $ret;}
		foreach($ret['spitale_grid']['resp']['rows'] as $su){$ret['spitalids'][] = intval($su->id);}
	}else{
		$ret['spitale_users_grid'] = cs('spitale_users/grid',array("filters"=>array("rules"=>array(
			array("field"=>"user","op"=>"eq","data"=>$p_arr['user']),
		))));
		if (!isset($ret['spitale_users_grid']['success']) || ($ret['spitale_users_grid']['success'] != true)) {return $ret;}
		foreach($ret['spitale_users_grid']['resp']['rows'] as $su){$ret['spitalids'][] = intval($su->spital);}
	}
	if (count($ret['spitalids']) == 0){$ret['success'] = false; return $ret;}
	if (count($ret['spitalids']) == 1){$ret['success'] = true; return $ret;}
	ob_start(); 
	?> 
	<select id="spitale_users_adminselectHtml" class="form-control" onchange="spitale_users_adminselectHtml_onchange(this)">
		<?php foreach($ret['spitalids'] as $spitalid){
			$ret['spitale_get'] = cs('spitale/get', array("filters"=>array("rules"=>array(
				array("field"=>"id","op"=>"eq","data"=>$spitalid)
			))));
			if ($ret['spitale_get'] == null) {ob_get_contents();ob_end_clean();return $ret;}
			$selected = '';
			if ($ret['spitale_users_adminselectGetselected']['resp']['id'] == $ret['spitale_get']['id']) $selected = 'selected="selected"';
		?>
			<option value="<?php echo $ret['spitale_get']['id']?>" <?php echo $selected?> ><?php echo $ret['spitale_get']['nume']?></option>
		<?php } ?>
	</select>
	<script>
		spitale_users_adminselectHtml_onchange = function(e){
			console.log(e)
			window.location.href = setURLParameter({name:'p_spital',value:e.value})
		}
	</script>
	<?php 
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function spitale_users_unlink($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array());
	if (!isset($p_arr['uid'])){ $ret['error'] = 'check param - uid'; return $ret;}
	if (!isset($p_arr['sid'])){ $ret['error'] = 'check param - sid'; return $ret;}
	
	$ret['unlink_sql'] = "DELETE ";
	$ret['unlink_sql'] .= " FROM spitale_users";
	$ret['unlink_sql'] .=  " WHERE spitale_users.spital = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['sid']);
	$ret['unlink_sql'] .=  " AND spitale_users.user = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['uid']);

	$ret['unlink'] = cs("_cs_grid/get",array('db_sql'=>$ret['unlink_sql']));
	
	if (!isset($ret['unlink']['success']) || ($ret['unlink']['success'] == false)) {$is_success = false;}
	
	$ret['success'] = true;
	return $ret;
}
?>