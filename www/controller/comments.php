<?php
$GLOBALS['comments_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'browser'			=>array('type'=>'int',),
	'user'				=>array('type'=>'int',),
	'alttype'			=>array('type'=>'int',), //0 type plainurl 1 typespital 2 typedoctor
	'altid'				=>array('type'=>'int',),
	'rate'				=>array('type'=>'int',),
	'text'				=>array('type'=>'text',),
	'date'				=>array('type'=>'datetime',),
	'email'				=>array('type'=>'text',),
	'nume'				=>array('type'=>'text',),
	'parent'				=>array('type'=>'int',),
);
function comments_get($p_arr = array()){
	$ret = null;
	$list = comments_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["browser"] = intval($list['resp']['rows'][0]->browser);
		$ret["user"] = intval($list['resp']['rows'][0]->user);
		$ret["alttype"] = intval($list['resp']['rows'][0]->alttype);
		$ret["altid"] = intval($list['resp']['rows'][0]->altid);
		$ret["rate"] = intval($list['resp']['rows'][0]->rate);
		$ret["text"] = $list['resp']['rows'][0]->text;
		$ret["date"] = $list['resp']['rows'][0]->date;
		$ret["email"] = $list['resp']['rows'][0]->email;
		$ret["nume"] = $list['resp']['rows'][0]->nume;
		$ret["parent"] = intval($list['resp']['rows'][0]->parent);
	}
	return $ret;
}
function comments_grid($p_arr = array()){
	global $comments_cols;
	$p_arr['db_cols'] = $comments_cols;
	$p_arr['db_table'] = 'comments';
	return cs("_cs_grid/get",$p_arr);
}
function comments_update($p_arr = array()){
	global $comments_cols;
	$p_arr['db_cols'] = $comments_cols;
	$p_arr['db_table'] = 'comments';
	return cs("_cs_grid/update",$p_arr);
}
function comments_listhtml_imbricate($p_arr = array()){
	$ret = array('success'=>false,'resp'=>array('html'=>'','data'=>array()));
	if (!isset($p_arr['alttype'])) {$ret['error'] = 'check parameter - alttype'; return $ret;};
	if (!isset($p_arr['altid'])) {$ret['error'] = 'check parameter - altid'; return $ret;};
	if (!isset($p_arr['parent'])){$ret['error'] = 'param parent??';return $ret;}
	$comment = $p_arr['parent'];
	ob_start(); 
	?> 
	<div class="comments_item">
		<div><?php echo $comment['nume'] . ' - ' . date('d.m.Y',strtotime($comment['date']))?></div>
		<?php if (intval($comment['rate']) > 0){?>
		<div>
			<div class="comments_listhtml_rate" data-rate-value="<?php echo $comment['rate']?>"></div>
		</div>
		<?php }?>
		<?php if ($comment['text'] != ''){?>
		<div>
			<?php echo htmlspecialchars( $comment['text'])?>
		</div>
		<?php }?>
		<div><a href="javascript:void(0)" onclick="comments_listhtml_reply(<?php echo $comment['id']?>)"><i class="fa fa-comment" aria-hidden="true"></i> Răspunde</a></div>
		<div id="comments_item_<?php echo $comment['id']?>"></div>
		<?php 
			$ret['comments_grid'] = cs('comments/grid',array(
				"filters"=>array("rules"=>array(
					array("field"=>"alttype","op"=>"eq","data"=>$p_arr['alttype']),
					array("field"=>"altid","op"=>"eq","data"=>$p_arr['altid']),
					array("field"=>"parent","op"=>"eq","data"=>$comment['id']),
				)),
				'sord'=>'desc',
				'sidx'=>'id',
			));
			if (!isset($ret['comments_grid']['success']) || ($ret['comments_grid']['success'] != true)) {$ret['error'] = 'comments_grid??'; return $ret;}
			foreach($ret['comments_grid']['resp']['rows'] as $citem){
				$row = json_decode(json_encode($citem),true);
				$ret['comments_listhtml_imbricate'] = cs('comments/listhtml_imbricate',array(
					'parent'=>$row,
					'altid'=>$p_arr['altid'],
					'alttype'=>$p_arr['alttype'],
				));
				if (!isset($ret['comments_listhtml_imbricate']['success'])||($ret['comments_listhtml_imbricate']['success'] != true)){$ret['error'] = 'comments_listhtml_imbricate??'; return $ret;}
				echo $ret['comments_listhtml_imbricate']['resp']['html'];
			}
		?>
	</div>
	<?php 
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function comments_notaget($p_arr = array()){
	$ret = array('success'=>false,'resp'=>array('count'=>0,'sum'=>0,'html'=>'na'));
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	if (!isset($p_arr['alttype'])) {$ret['error'] = 'check parameter - alttype'; return $ret;};
	if (!isset($p_arr['altid'])) {$ret['error'] = 'check parameter - altid'; return $ret;};
	$ret['p_arr'] = $p_arr;
	
	$ret['cauta_sql'] = " SELECT count(*) as count";
	$ret['cauta_sql'] .= " 	, sum(rate) as sum";
	$ret['cauta_sql'] .= " FROM comments ";
	$ret['cauta_sql'] .= " WHERE altid = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['altid']);
	$ret['cauta_sql'] .= " AND alttype = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['alttype']);
	$ret['cauta_sql'] .= " AND rate > 0 ";
	$ret['cauta_sql'] .= " LIMIT 0, 1 ";
	$ret['cauta'] = cs("_cs_grid/get",array('db_sql'=>$ret['cauta_sql']));
	if (!isset($ret['cauta']['success'])||($ret['cauta']['success'] != true)){return $ret;}
	if ($ret['cauta']['resp']['records'] == 1){
		$ret['resp']['count'] = intval($ret['cauta']['resp']['rows'][0]->count);
		$ret['resp']['sum'] = intval($ret['cauta']['resp']['rows'][0]->sum);
		if ($ret['resp']['sum'] > 0){
			$ret['resp']['html'] = number_format($ret['resp']['sum']/$ret['resp']['count'],1);
		}
	}
	
	$ret['success'] = true;
	return $ret;
}
function comments_count($p_arr = array()){
	$ret = array('success'=>false,'resp'=>array('count'=>0));
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	if (!isset($p_arr['alttype'])) {$ret['error'] = 'check parameter - alttype'; return $ret;};
	if (!isset($p_arr['altid'])) {$ret['error'] = 'check parameter - altid'; return $ret;};
	$ret['p_arr'] = $p_arr;
	
	$ret['cauta_sql'] = " SELECT count(*) as count";
	$ret['cauta_sql'] .= " FROM comments ";
	$ret['cauta_sql'] .= " WHERE altid = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['altid']);
	$ret['cauta_sql'] .= " AND alttype = " . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['alttype']);
	$ret['cauta_sql'] .= " LIMIT 0, 1 ";
	$ret['cauta'] = cs("_cs_grid/get",array('db_sql'=>$ret['cauta_sql']));
	if (!isset($ret['cauta']['success'])||($ret['cauta']['success'] != true)){return $ret;}
	if ($ret['cauta']['resp']['records'] == 1){
		$ret['resp']['count'] = intval($ret['cauta']['resp']['rows'][0]->count);
	}	
	$ret['success'] = true;
	return $ret;
}
function comments_listhtml($p_arr = array()){
	$ret = array('success'=>false,'resp'=>array('html'=>'','data'=>array()));
	if (!isset($p_arr['alttype'])) {$ret['error'] = 'check parameter - alttype'; return $ret;};
	if (!isset($p_arr['altid'])) {$ret['error'] = 'check parameter - altid'; return $ret;};
	
	$ret['comments_grid'] = cs('comments/grid',array(
		"filters"=>array("rules"=>array(
			array("field"=>"alttype","op"=>"eq","data"=>$p_arr['alttype']),
			array("field"=>"altid","op"=>"eq","data"=>$p_arr['altid']),
			array("field"=>"parent","op"=>"eq","data"=>0),
		)),
		'sord'=>'desc',
		'sidx'=>'id',
	));
	if (!isset($ret['comments_grid']['success']) || ($ret['comments_grid']['success'] != true)) {$ret['error'] = 'comments_grid??'; return $ret;}
	
	if ($ret['comments_grid']['resp']['records'] > 0){
		ob_start(); 
		?>
		<style>
			.comments_item{
				background-color: white;
				margin: 10px 5px;
				padding: 5px;
				border: 1px solid #ddd;
				border-radius: 5px;
				color:#8599a2;
			}
			.comments_listhtml_rate{
				font-size: 20px;
				margin: 0;
			}
			.comments_listhtml_rate .rate-hover-layer{
				color: pink;
			}
			.comments_listhtml_rate .rate-select-layer{
				color: red;
			}
		</style>
		<?php	
		$ret['resp']['html'] = ob_get_contents();ob_end_clean();
		foreach($ret['comments_grid']['resp']['rows'] as $citem){
			$row = json_decode(json_encode($citem),true);
			$ret['comments_listhtml_imbricate'] = cs('comments/listhtml_imbricate',array(
				'parent'=>$row,
				'altid'=>$p_arr['altid'],
				'alttype'=>$p_arr['alttype'],
			));
			if (!isset($ret['comments_listhtml_imbricate']['success'])||($ret['comments_listhtml_imbricate']['success'] != true)){$ret['error'] = 'comments_listhtml_imbricate??'; return $ret;}
			$ret['resp']['html'] .= $ret['comments_listhtml_imbricate']['resp']['html'];
		}
		ob_start(); 
		?>
		<script>
			comments_listhtml_init = function(){
				$(".comments_listhtml_rate").rate({max_value: 10,step_size: 1,readonly:true,selected_symbol_type:'fontawesome_star'});
			}
			if (typeof($) != 'undefined'){
				comments_listhtml_init()			
			}else{
				document.addEventListener("DOMContentLoaded", function(){
					comments_listhtml_init()
				})
			}
			comments_listhtml_reply = function(cid){
				cs('comments/addhtml',{
					altid:<?php echo $p_arr['altid']?>,
					alttype:<?php echo $p_arr['alttype']?>,
					parent:cid,
				}).then(function(addhtml){
					console.log(cid,addhtml,'#comments_item_' + cid)
					if ((typeof(addhtml.success) != 'undefined') && (addhtml.success == true)){
						$('#comments_addhtml_form').remove()
						$('.comments_remove').remove()
						var remove = '<a class="comments_remove" href="javascript:void(0)" onclick="comments_listhtml_reply(0)"><i class="fa fa-times" aria-hidden="true"></i> Anuleaza raspuns</a>'
						if (cid == 0) remove = ''
						$('#comments_item_' + cid).html(remove+addhtml.resp.html)
						
					}
				})
			}
		</script>
		<?php
		$ret['resp']['html'] .= ob_get_contents();ob_end_clean();
	}
	
	$ret['success'] = true;
	return $ret;
}
function comments_addhtml($p_arr = array()){
	$ret = array('success'=>false,'resp'=>array('html'=>''));
	if (!isset($p_arr['alttype'])) {$ret['error'] = 'check parameter - alttype'; return $ret;};
	if (!isset($p_arr['altid'])) {$ret['error'] = 'check parameter - altid'; return $ret;};
	if (!isset($p_arr['parent'])) {$p_arr['parent'] = 0;};
	
	if (isset($_SESSION['cs_users_id'])){
		$ret['users_get'] = cs('users/get',array("filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id']),
		))));
		if ($ret['users_get'] == null){$ret['error'] = 'users_get??'; return $ret;}
	}
	
	ob_start(); 
	?>
		<form class="" action="javascript:void(0)" onsubmit="comments_addhtml_action(this)" id="comments_addhtml_form" >
			<input type="hidden" name="alttype" value='<?php echo $p_arr['alttype'];?>'>
			<input type="hidden" name="altid" value='<?php echo $p_arr['altid'];?>'>
			<input type="hidden" name="parent" value='<?php echo $p_arr['parent'];?>'>
			<?php if (isset($_SESSION['cs_users_id'])){?>
			<input type="hidden" name="nume" value='<?php echo $ret['users_get']['nume'];?>'>
			<input type="hidden" name="email" value='<?php echo $ret['users_get']['email'];?>'>
			<input type="hidden" name="user" value='<?php echo $ret['users_get']['id'];?>'>
			<?php }else{?>
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-user" aria-hidden="true"></i></span>
					<input type="text" name="nume" required class="form-control" placeholder="Nume">
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-envelope" aria-hidden="true"></i></span>
					<input type="text" name="email" required class="form-control" placeholder="Email">
				</div>
			</div>
			<?php }?>
			<div class="form-group">
				<textarea name="text" class="form-control" rows="3" placeholder="Comentariul tau aici"></textarea>
			</div>
			<div class="form-group">
				<style>
					#comments_addhtml_rate{
						font-size: 20px;
						margin: 0;
					}
					#comments_addhtml_rate .rate-hover-layer{
						color: pink;
					}
					#comments_addhtml_rate .rate-select-layer{
						color: red;
					}
				</style>
				<div>Acordă o notă</div>
				<div id="comments_addhtml_rate" data-rate-value=0></div>
				<input type="hidden" name="rate" value="">
			</div>

			<div class="form-group">
				<button type="submit" class="btn btn-warning ">Trimite comentariu</button>
			</div>
			<script>
				comments_addhtml_init = function(){
					$("#comments_addhtml_rate").rate({max_value: 10,step_size: 1,selected_symbol_type:'fontawesome_star'});
					$("#comments_addhtml_rate").on("change", function(ev, data){
						$('#comments_addhtml_form input[name=rate]').val(data.to)
					});
				}
				if (typeof($) != 'undefined'){
					comments_addhtml_init()			
				}else{
					document.addEventListener("DOMContentLoaded", function(){
						comments_addhtml_init()
					})
				}
				comments_addhtml_action = function(form){
					cs('comments/add',new FormData(form)).then(function(add){
						//console.log(add)
						if (typeof(add.success) == 'undefined' || add.success != true) {
							alert('salvare nereusita')
							return
						}
						window.location.reload()
					})
				}
			</script>
		</form>
	<?php 
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function comments_add($p_arr = array()){
	$ret = array('success'=>false,'error'=>'');
	if (!isset($p_arr['nume']) || $p_arr['nume'] == ''){ $ret['error'] = 'check param - nume'; return $ret;}
	if (!isset($p_arr['email']) || $p_arr['email'] == ''){ $ret['error'] = 'check param - email'; return $ret;}
	if (!isset($p_arr['browser'])) {
		if (isset($_SESSION['browser_id'])) {$p_arr['browser'] = $_SESSION['browser_id'];}
	};
	if (!isset($p_arr['user'])) {
		if (isset($_SESSION['cs_users_id'])) {$p_arr['user'] = $_SESSION['cs_users_id'];}
	};
	if (!isset($p_arr['date']))  $p_arr['date'] = date('Y-m-d H:i:s');
	$p_arr['oper'] = 'add';
	$p_arr['id'] = 'null';
	$ret['comments_update'] = comments_update($p_arr);
	if (!isset($ret['comments_update']['success'])||($ret['comments_update']['success'] != true)){return $ret;}
	$ret['success'] = true;
	return $ret;
}
?>