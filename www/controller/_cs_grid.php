<?php
function _cs_grid_paraminit_filterGroup($p_arr = array(),$p_cols= array()){
	$op_pattern = array(
		"eq"=>'_field = "_data"',
		"gt"=>'_field > "_data"',
		"lt"=>'_field < "_data"',
		"le"=>'_field <= "_data"',
		"ge"=>'_field >= "_data"',
		"ne"=>'_field <> "_data"',
		"bw"=>'_field LIKE "_data%"',
		"bn"=>'_field NOT LIKE "_data%"',
		"ew"=>'_field LIKE "%_data"',
		"en"=>'_field NOT LIKE "%_data"',
		"cn"=>'_field LIKE "%_data%"',
		"nc"=>'_field NOT LIKE "%_data%"',
		"nu"=>'_field IS NULL',
		"nn"=>'_field ISNOT NULL',
		"in"=>'_field IN (_data)',
		"ni"=>'_field NOT IN (_data)',
	); 
	$sql_filters = "";
	if ((isset($p_arr['groups']))&&(count($p_arr['groups'])>0)) foreach ($p_arr['groups'] as $p_arr_){
		$group = _cs_grid_paraminit_filterGroup($p_arr_,$p_cols);
		if ($group != ""){
			if ($sql_filters != "") $sql_filters .= " " . $p_arr['groupOp'] . " ";
			$sql_filters .=  $group;
		}
	}
	$rules_a = array();
	if ((isset($p_arr['rules']))&&(count($p_arr['rules'])>0)) foreach ($p_arr['rules'] as $rule){
		$key = explode("_",$rule['field']); if ((count($key)>1)&&($key[1]=="nume")) {$rule['field'] = $key[0];}
		$tfield = $rule['field'];
		$tfield1 = explode(".",$rule['field']); if ((count($tfield1)>1)) {$tfield = $tfield1[1];}
		$txtfield = '`' . implode("`.`",explode(".",$rule['field'])) . '`';
		if (gettype($rule["data"])=='array'){$rule["data"] = implode(", ", $rule["data"]);} 
		if ($p_cols[$tfield]['type'] == 'date') {$rule["data"] = date('Y-m-d', strtotime($rule["data"]));}
		if ($p_cols[$tfield]['type'] == 'datetime') {$rule["data"] = date('Y-m-d H:i:s', strtotime($rule["data"]));}
		if ($rule['data'] == null) {$rule["data"] = 'null';}
		$rules_a[] = str_replace("_data", $rule["data"], str_replace("_field",$txtfield,$op_pattern[$rule["op"]]));
	}
	if (!isset($p_arr['groupOp'])) $p_arr['groupOp'] = "AND";
	if (count($rules_a)>0){
		if ($sql_filters != "") $sql_filters .= " " . $p_arr['groupOp'] . " ";
		$sql_filters .= implode(" " . $p_arr['groupOp'] . " ", $rules_a);
	}
	if ($sql_filters != "") $sql_filters = "(" . $sql_filters . ")";
	return $sql_filters;
}
function _cs_grid_paraminit($p_arr = array()){
	$p_page = 1; $p_rows = 20; $p_sidx = ''; $p_sord = ''; $p_filters = array('groupOp'=>'AND','rules'=>array(), 'groups'=>array());
	$sql_order = ''; $sql_limit = ''; $sql_filters = "";

	if (isset($p_arr['http_mask'])){
		if (isset($_REQUEST[$p_arr['http_mask'] . '_page'])) 		$p_page 	= $_REQUEST[$p_arr['http_mask'] . '_page'];
		if (isset($_REQUEST[$p_arr['http_mask'] . '_rows'])) 		$p_rows 	= $_REQUEST[$p_arr['http_mask'] . '_rows'];
		if (isset($_REQUEST[$p_arr['http_mask'] . '_sidx'])) 		$p_sidx 	= $_REQUEST[$p_arr['http_mask'] . '_sidx'];
		if (isset($_REQUEST[$p_arr['http_mask'] . '_sord'])) 		$p_sord 	= $_REQUEST[$p_arr['http_mask'] . '_sord'];
		if (isset($_REQUEST[$p_arr['http_mask'] . '_filters'])) 	$p_filters 	= $_REQUEST[$p_arr['http_mask'] . '_filters'];
	}
	if (isset($p_arr['page'])) $p_page = $p_arr['page'];
	if (isset($p_arr['rows'])) $p_rows = $p_arr['rows'];
	if (isset($p_arr['sidx'])) $p_sidx = $p_arr['sidx'];
	if (isset($p_arr['sord'])) $p_sord = $p_arr['sord'];
	if (isset($p_arr['filters'])) $p_filters = $p_arr['filters'];

	$p_start = $p_rows * $p_page - $p_rows; // do not put $limit*($page - 1) 
	if ($p_start < 0) $p_start = 0;

	if (($p_sidx != '')&&($p_sord != '')){ $sql_order = " ORDER BY `" . $p_sidx . "` " . $p_sord . " ";}
	$sql_limit = " LIMIT " . $p_start . ", " . $p_rows . " ";
	if ((gettype($p_filters) == "string")&&($p_filters != "")) { $p_filters = json_decode($p_filters,true);}
	if ((isset($p_filters['rules'])&&(count($p_filters['rules'])>0))
		||(isset($p_filters['groups'])&&(count($p_filters['groups'])>0))){ $sql_filters = _cs_grid_paraminit_filterGroup($p_filters,$p_arr['db_cols']);}
	//var_dump($sql_filters);
	$wheretxt = " WHERE ";
	if (isset($p_arr['having'])) $wheretxt = " HAVING ";
	if ($sql_filters != ""){ $sql_filters = $wheretxt . $sql_filters . " ";}
	return array('p_page'=>intval($p_page),'p_rows'=>intval($p_rows)
		,'p_sidx'=>$p_sidx
		,'p_sord'=>$p_sord
		,'p_filters'=>$p_filters,
		'p_start'=>$p_start,
		//'p_cols'=>$p_cols,
		'sql_order'=>$sql_order,
		'sql_limit'=>$sql_limit,
		'sql_filters'=>$sql_filters,
	);
}
function _cs_grid_get($p_arr){
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	$ret['p_arr'] = $p_arr;
	extract (_cs_grid_paraminit($p_arr));
	if (!isset($p_arr['db_sql'])){
		$p_arr['db_sql'] = "SELECT SQL_CALC_FOUND_ROWS ";
		$colnames = array();
		foreach($p_arr['db_cols'] as $cn=>$cp){
			$colnames[] = $cn;
		}
		$p_arr['db_sql'] .= "`" . implode("`, `", $colnames) . "`";
		$p_arr['db_sql'] .= " FROM `" . $p_arr['db_table'] . "`";
		$p_arr['db_sql'] .= $sql_filters . $sql_order . $sql_limit;
	}else{
		$p_arr['db_sql'] = str_replace(
			array("__filters__",	"__order__",	"__limit__"),
			array($sql_filters,		$sql_order,		$sql_limit),
			$p_arr['db_sql']
		);
	}
	$r = $GLOBALS['cs_db_conn']->query($p_arr['db_sql']);
	$r1 = $GLOBALS['cs_db_conn']->query('SELECT FOUND_ROWS()');
	if($r === false) {$ret['sql'] = $p_arr['db_sql']; return $ret;}
	$ret['success'] = true;
	unset($ret['error']);
	$row1 = $r1->fetch_object();
	if (isset($r->num_rows)&&($r->num_rows>0)){
		$ret['resp']['records'] = intval($row1->{'FOUND_ROWS()'});
		$ret['resp']['total'] = intval(ceil($ret['resp']['records']/$p_rows)); 
		$ret['resp']['page'] = $p_page; if ($p_page > $ret['resp']['total']){$ret['resp']['page'] = $ret['resp']['total'];}
		$ret['resp']['rows'] = array();
		while($row = $r->fetch_object()){
			$ret['resp']['rows'][] = $row;
		}
	}else{
		$ret['resp']['records'] = 0;
		$ret['resp']['total'] = 0; 
		$ret['resp']['page'] = 0;
		$ret['resp']['rows'] = array();
	}
	$ret['p_filters'] = $p_filters;
	$ret['sql_filters'] = $sql_filters;
	$ret['sql'] = $p_arr['db_sql'];
	return $ret;
}
function _cs_grid_update($p_arr = array()){
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	//require_once(api_inc_path . DIRECTORY_SEPARATOR . 'topalert.php');
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	if (!isset($p_arr['oper']) || ($p_arr['oper'] == '')) {$ret['error'] = "oper not found"; $ret['arr'] = $p_arr; return $ret;}
	if (($p_arr['oper'] == 'add') && !isset($p_arr['id'])) $p_arr['id'] = 'null';
	if (!isset($p_arr['id'])) {$ret['error'] = "id not found"; $ret['arr'] = $p_arr; return $ret;}
	$p_id = $GLOBALS['cs_db_conn']->real_escape_string($p_arr['id']);
	$p_oper = $GLOBALS['cs_db_conn']->real_escape_string($p_arr['oper']);
	switch($p_oper){
		case "edit":
			$updatecols = array();
			foreach ($p_arr as $colname => $colval){
				if (($colname != 'id')&&($colname != 'oper')) {
					$key = explode("_",$colname); if ((count($key)>1)&&($key[1]=="nume")) {$colname = $key[0];}
					if (isset($p_arr['db_cols'][$colname])){
						if ($p_arr['db_cols'][$colname]['type'] == 'date') {$colval = date('Y-m-d', strtotime($colval));}
						if ($p_arr['db_cols'][$colname]['type'] == 'datetime') {$colval = date('Y-m-d H:i:s', strtotime($colval));}
						if ($p_arr['db_cols'][$colname]['type'] == 'password') {$colval = md5($colval);}
						$updatecols[$colname] = $colval;
					}
				}
			}
			if (count($updatecols)>0){
				$colnames = array();
				foreach($updatecols as $colname=>$colval){
					$colnames[] = "`" . $GLOBALS['cs_db_conn']->real_escape_string($colname) . "` = '" . $GLOBALS['cs_db_conn']->real_escape_string($colval) . "'";
				}
				$sql = "UPDATE `" . $p_arr['db_table'] . "` SET " . implode(", ", $colnames) . " WHERE `id` = " . $p_id;
				$r = $GLOBALS['cs_db_conn']->query($sql);
				if($r !== false) {
					$ret['success'] = true;
					unset($ret['error']);
					//topalert_push(array('code'=>0,'message'=>'Utilizator modificat cu succes'));
				}else{
					//topalert_push(array('code'=>1,'message'=>'erare la modificarea Utilizatorului'));
				}
			}
		break;
		case "add":
			$colnames = array();
			$colvalues = array();
			foreach ($p_arr as $colname => $colval){
				if (($colname != 'id')&&($colname != 'oper')) {
					$key = explode("_",$colname); if ((count($key)>1)&&($key[1]=="nume")) {$colname = $key[0];}
					if (isset($p_arr['db_cols'][$colname])){
						if ($p_arr['db_cols'][$colname]['type'] == 'date') {$colval = date('Y-m-d', strtotime($colval));}
						if ($p_arr['db_cols'][$colname]['type'] == 'datetime') {$colval = date('Y-m-d H:i:s', strtotime($colval));}
						if ($p_arr['db_cols'][$colname]['type'] == 'password') {$colval = md5($colval);}
						$colnames[] = "`" . $GLOBALS['cs_db_conn']->real_escape_string($colname) . "`";
						//var_dump($colval);
						$colvalues[] = "'" . $GLOBALS['cs_db_conn']->real_escape_string($colval) . "'";
					}
				}
			}
			if (count($colnames)>0){
				$sql = "INSERT INTO `" . $p_arr['db_table'] . "` (" . implode(", ", $colnames) . ") VALUES (" . implode(", ", $colvalues) .")";
				//var_dump($sql);
				$r = $GLOBALS['cs_db_conn']->query($sql);
				if($r !== false) {
					$ret['success'] = true;
					unset($ret['error']);
					$ret['resp']['id'] = $GLOBALS['cs_db_conn']->insert_id;
				}else{
				}
			}
		break;
		case "del":
			$sql = "DELETE FROM `" . $p_arr['db_table'] . "` WHERE `id` in (" . $p_id . ")";
			$r = $GLOBALS['cs_db_conn']->query($sql);
			if($r !== false) {$ret['success'] = true;unset($ret['error']);}
		break;
	}
	//$ret['updatecols'] = $updatecols;
	//$ret['p_cols'] = $p_arr['db_cols'];
	$ret['sql'] = $sql;
	return $ret;
}
?>