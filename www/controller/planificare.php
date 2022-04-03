<?php
$GLOBALS['planificare_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'doctor'			=>array('type'=>'int',),
	'spital'			=>array('type'=>'int',),
	'start'				=>array('type'=>'datetime',),
	'stop'				=>array('type'=>'datetime',),
);
$GLOBALS['planificare_path'] = 'planificari';
$GLOBALS['planificare_path_curent'] = '';
$GLOBALS['planificare_legenda'] = null;
$GLOBALS['planificare_legendatoname'] = null;

function planificare_get($p_arr = array()){
	$ret = null;
	$list = planificare_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["doctor"] = intval($list['resp']['rows'][0]->doctor);
		$ret["spital"] = intval($list['resp']['rows'][0]->spital);
		$ret["start"] = $list['resp']['rows'][0]->start;
		$ret["stop"] = $list['resp']['rows'][0]->stop;
	}
	return $ret;
}
function planificare_grid($p_arr = array()){
	global $planificare_cols;
	$p_arr['db_cols'] = $planificare_cols;
	$p_arr['db_table'] = 'planificare';
	return cs("_cs_grid/get",$p_arr);
}
function planificare_update($p_arr = array()){
	global $planificare_cols;
	$p_arr['db_cols'] = $planificare_cols;
	$p_arr['db_table'] = 'planificare';
	return cs("_cs_grid/update",$p_arr);
}
function planificare_foldercheck($p_arr = array()){
	global $planificare_path;
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $planificare_path)){ 
		mkdir(cs_path . DIRECTORY_SEPARATOR . $planificare_path); 
		chmod(cs_path . DIRECTORY_SEPARATOR . $planificare_path, 0755);
	}
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $planificare_path . DIRECTORY_SEPARATOR . '.htaccess')){
		$out			= fopen(cs_path . DIRECTORY_SEPARATOR . $planificare_path . DIRECTORY_SEPARATOR . '.htaccess', 'w+');
		fwrite($out,"deny from all\n<Files ~ '^.+\\.(xls|xlsx)$'>\norder deny,allow\nallow from all\n</Files>");
		fclose($out);			
	}
	$datetime = new DateTime();
	$timestamp = $datetime->getTimestamp();
	$dt_year = date("Y", $timestamp);
	$dt_month = date("m", $timestamp);
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $planificare_path . DIRECTORY_SEPARATOR . $dt_year)){ 
		mkdir(cs_path . DIRECTORY_SEPARATOR . $planificare_path . DIRECTORY_SEPARATOR . $dt_year); 
		chmod(cs_path . DIRECTORY_SEPARATOR . $planificare_path . DIRECTORY_SEPARATOR . $dt_year, 0755);
	}
	if (!file_exists(cs_path . DIRECTORY_SEPARATOR . $planificare_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month)){ 
		mkdir(cs_path . DIRECTORY_SEPARATOR . $planificare_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month); 
		chmod(cs_path . DIRECTORY_SEPARATOR . $planificare_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month, 0755);
	}
	$GLOBALS['planificare_path_curent'] = $planificare_path . DIRECTORY_SEPARATOR . $dt_year . DIRECTORY_SEPARATOR . $dt_month;
}
function planificare_emptyget_rs($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() , 'resptype'=>'rs' ,'error'=>'');
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	ob_start(); 
	?> 
	<style>
		.planificare_emptyget_tdy {
			padding: 0 !important;
			width:135px;
		}
		.planificare_emptyget_a{
			display: inline-block;
			text-align: center; 
			width:100%;
			padding-top:10px;
			padding-bottom:10px;
			cursor: default;
		}
		.planificare_emptyget_a.disabled{
			background-color:gray;
			color:lightgray;
		}
		.planificare_emptyget_a.disabled:hover{
			text-decoration: none;
		}
		.planificare_emptyget_a.valid:hover{
			text-decoration: none;
			background-color:green;
			color:white;
		}
	</style>
	<div id="planificare_emptyget_modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md" style="z-index:105">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-calendar" aria-hidden="true"></i> Descarca planificare goala</h4>
				</div>
				<div class="modal-body">
					<div>Alege luna si anul</div>
					<table class="table table-bordered table-striped">
						<?php for ($y = date('Y'); $y <= (date('Y') + 1); $y++){ ?>
						<tr>
							<td><?php echo $y;?></td>
							<?php for ($m = 1; $m <= 12; $m++){ 
							$lastday = strtotime(date("Y-m-t",strtotime($y.'-'.$m.'-1')));
							$status = 'valid';
							if (time() >= $lastday) {
								$status = 'disabled';
							}
							?>
							<td class="planificare_emptyget_tdy">
								<a class="planificare_emptyget_a <?php echo $status;?>" <?php 
								if ($status == 'valid') {
									?>onclick="$('#planificare_emptyget_modal').modal('hide')" target="_blank" href="<?php echo cs_url_po . '/csapi/planificare/emptyget?spital='.$p_arr['spital'].'&y='.$y.'&m='.$m;?>" <?php 
								}?>><?php echo $m;?></a>
							</td>
							<?php }?>
						</tr>
						<?php }?>
					</table>
				</div>
				<div class="modal-footer">
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal --> <script>
		$("div.modal-backdrop.fade.in").remove()
		$("#planificare_emptyget_modal").modal('show')
	</script><?php 
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;

}
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
function planificare_emptyget($p_arr = array()){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($_SESSION['cs_users_id'])){$ret['error'] = 'login first'; return $ret;}
	if (!isset($p_arr['m'])){ $ret['error'] = 'check param - m'; return $ret;}
	$p_arr['m'] = intval($p_arr['m']);
	if (!isset($p_arr['y'])){ $ret['error'] = 'check param - y'; return $ret;}
	$p_arr['y'] = intval($p_arr['y']);
	
	$ret['spitale_users_getlevel'] = cs('spitale_users/getlevel',array('spital'=>$p_arr['spital']));
	if (!isset($ret['spitale_users_getlevel']['success']) || ($ret['spitale_users_getlevel']['success'] != true)) {return $ret;}
	
	if ($ret['spitale_users_getlevel']['resp'] < user_level_doctor){$ret['error'] = 'level min doctor required'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	$numeluni = array('ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie');
	$numezile = array('LU', 'MA', 'MI', 'JO', 'VI', 'SA', 'DU');
	
	$ret['spitale_get'] = cs('spitale/get',array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['spital']),
	))));
	if ($ret['spitale_get'] == null) { $ret['error'] = 'no such'; return $ret;}
	
	if ($ret['spitale_users_getlevel']['resp'] == user_level_doctor){
		$ret['users_grid'] = cs('users/grid', array("filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
		))));
		if ($ret['users_grid'] == null) {return $ret;}
		$doctori_list = $ret['users_grid'];
	}else{
		$ret['doctori_sql'] = 'SELECT users.id AS id';
		$ret['doctori_sql'] .= '	, users.nume AS nume ';
		$ret['doctori_sql'] .= ' FROM spitale_users';
		$ret['doctori_sql'] .= ' LEFT JOIN users ON spitale_users.user = users.id';
		$ret['doctori_sql'] .= ' WHERE spitale_users.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
		$ret['doctori_sql'] .= ' AND (SELECT id FROM specializari_user_spitale WHERE (spitale_users.user = specializari_user_spitale.user) AND (spitale_users.spital = specializari_user_spitale.spital) LIMIT 0,1) IS NOT NULL';
		
		$ret['doctori'] = cs("_cs_grid/get",array('db_sql'=>$ret['doctori_sql']));
		if (!isset($ret['doctori']['success']) || ($ret['doctori']['success'] != true)) {return $ret;}
		$doctori_list = $ret['doctori'];
		
		
		//$ret['users_getspitalusers'] = cs('users/getspitalusers',array("spital"=>$p_arr['spital'],"level"=>user_level_doctor));
		//if (!isset($ret['users_getspitalusers']['success']) || ($ret['users_getspitalusers']['success'] != true)) {return $ret;}
		//$doctori_list = $ret['users_getspitalusers'];
	}

	require_once __DIR__ . '/../PhpSpreadsheet-develop/src/Bootstrap.php';	
	$spreadsheet = IOFactory::load(__DIR__ . '/../template/potemplate.xls');
	// Add some data
	$spreadsheet->setActiveSheetIndex(1)
		->setCellValue('A1', 'spital')
		->setCellValue('B1',  $ret['spitale_get']['id'])
		->setCellValue('A2', 'y')
		->setCellValue('B2',  $p_arr['y'])
		->setCellValue('A3', 'm')
		->setCellValue('B3',  $p_arr['m'])
		->setCellValue('A4', 'key')
		->setCellValue('B4',  md5($ret['spitale_get']['id'] . $p_arr['y'] . $p_arr['m'] . 'poscrt1222'))
	;
	$spreadsheet->setActiveSheetIndex(0)
		->setCellValue('A1', 'Nume unitate: ' . $ret['spitale_get']['nume'])
		->setCellValue('A2', 'Perioada planificare: ' . $numeluni[$p_arr['m']-1] . ' ' . $p_arr['y'])
		->setCellValue('A3', 'Generat pe data de: ' . date("d.m.Y"))
	;
	
	$lastday = intval(date("t",strtotime($p_arr['y'].'-'.$p_arr['m'].'-1')));
	for ($d = 1; $d <= 31; $d++){
		$ret['util_columntoletter'] =  cs('util/columntoletter',array("column"=>$d + 4));
		if (!isset($ret['util_columntoletter']['success']) || ($ret['util_columntoletter']['success'] != true)) {return $ret;}
		$col = $ret['util_columntoletter']['resp'];
		if ($lastday < $d){
			$spreadsheet->getActiveSheet()->getColumnDimension($col)->setVisible(false);			
			continue;
		}
		$n = date("N",strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$d));
		if ($n >= 6){ 
			//is weekend
			$spreadsheet->getActiveSheet()->getStyle($col.'5:'.$col.'8')->getFill()
				->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB('FFFF0000');
		}
		$spreadsheet->getActiveSheet()->setCellValue($col.'5', $numezile[$n-1]);
	}
	
	if ($doctori_list['resp']['records'] == 0){
		$ret['error'] = 'adauga doctori';
		return $ret;
	}
	if ($doctori_list['resp']['records'] > 1) $spreadsheet->getActiveSheet()->insertNewRowBefore(8, $doctori_list['resp']['records'] - 1);
	$spreadsheet->getActiveSheet()->getRowDimension('7')->setVisible(false);
	for ($di = 0; $di < $doctori_list['resp']['records']; $di++){
		$spreadsheet->getActiveSheet()->setCellValue('A'.($di + 8), $di + 1);
		$spreadsheet->getActiveSheet()->setCellValue('C'.($di + 8), $doctori_list['resp']['rows'][$di]->id);
		$spreadsheet->getActiveSheet()->setCellValue('D'.($di + 8), $doctori_list['resp']['rows'][$di]->nume);
	}
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$spreadsheet->setActiveSheetIndex(0);

	// Redirect output to a client’s web browser (Xls)
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="planificare' 
		. '_' . $p_arr['y'] 
		. '_' . str_pad($p_arr['m'], 2, '0', STR_PAD_LEFT) 
		. '.xls"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
	header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header('Pragma: public'); // HTTP/1.0

	$writer = IOFactory::createWriter($spreadsheet, 'Xls');
	$writer->save('php://output');
	exit;
}
function planificare_upload($p_arr = array()){
	planificare_foldercheck();
	global $planificare_path_curent;
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	require_once __DIR__ . '/../PhpSpreadsheet-develop/src/Bootstrap.php';
	$ret['p_arr'] = $p_arr;
	$input = file_get_contents('php://input');
	if (strlen($input) == 0) {$ret['error'] = 'empty'; return $ret;}
	$ret['resp']['fname'] = uniqid() . '.xls';
	file_put_contents($planificare_path_curent . DIRECTORY_SEPARATOR . $ret['resp']['fname'], $input);
	
	$spreadsheet = IOFactory::load($planificare_path_curent . DIRECTORY_SEPARATOR . $ret['resp']['fname']);
	$ret['planificare_parse'] =  cs('planificare/parse',array("spreadsheet"=>$spreadsheet));
	if (!isset($ret['planificare_parse']['success']) || ($ret['planificare_parse']['success'] != true)) {
		$ret['resp']['planificare_parse'] = $ret['planificare_parse'];
		return $ret;
	}
	
	$ret['success'] = true;
	return $ret;
}
function planificare_paramsfromfile($p_arr){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	planificare_foldercheck();
	global $planificare_path_curent;
	require_once __DIR__ . '/../PhpSpreadsheet-develop/src/Bootstrap.php';	

	if (!isset($p_arr['spreadsheet'])){ 
		if (!isset($p_arr['fname'])){ $ret['error'] = 'check param - fname'; return $ret;}
		$spreadsheet = IOFactory::load($planificare_path_curent . DIRECTORY_SEPARATOR . $p_arr['fname']);
	}else{
		$spreadsheet = $p_arr['spreadsheet'];
	}
	
	for ($i = 1; $i < 20; $i++){
		$ret['cv'] = $spreadsheet->setActiveSheetIndex(1)->getCell('A'.$i)->getValue();
		if ($ret['cv'] != ''){
			$ret['resp'][$ret['cv']] = $spreadsheet->setActiveSheetIndex(1)->getCell('B'.$i)->getValue();
		}else{
			break;
		}
	}
	$ret['success'] = true;
	return $ret;
}
function planificare_legendacache($p_arr){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	if (!isset($p_arr['nume'])){ $ret['error'] = 'check param - nume'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	global $planificare_legenda;
	if ($planificare_legenda == null){
		$ret['legenda_grid'] = cs('legenda/grid',array("filters"=>array("rules"=>array(
			array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		))));
		if (!isset($ret['legenda_grid']['success']) || ($ret['legenda_grid']['success'] != true)) {return $ret;}	
		$planificare_legenda = $ret['legenda_grid']['resp']['rows'];
	}
	foreach($planificare_legenda as $row){
		if ($row->nume == $p_arr['nume']){
			$ret['resp'] = $row;
			$ret['success'] = true;
			return $ret;
		} 
	}
	return $ret;
}
function planificare_legendacachetoname($p_arr){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	if (!isset($p_arr['start'])){ $ret['error'] = 'check param - start'; return $ret;}
	if (!isset($p_arr['stop'])){ $ret['error'] = 'check param - stop'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	global $planificare_legendatoname;
	if ($planificare_legendatoname == null){
		$ret['legenda_grid'] = cs('legenda/grid',array("filters"=>array("rules"=>array(
			array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		))));
		if (!isset($ret['legenda_grid']['success']) || ($ret['legenda_grid']['success'] != true)) {return $ret;}	
		$planificare_legendatoname = $ret['legenda_grid']['resp']['rows'];
	}
	foreach($planificare_legendatoname as $row){
		if (($row->start == $p_arr['start'])&&($row->stop == $p_arr['stop'])){
			$ret['resp'] = $row;
			$ret['success'] = true;
			return $ret;
		} 
	}
	return $ret;
}
function planificare_monthget($p_arr){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	
	if (!isset($p_arr['m'])){ $ret['error'] = 'check param - m'; return $ret;}
	$p_arr['m'] = intval($p_arr['m']);
	if (!isset($p_arr['y'])){ $ret['error'] = 'check param - y'; return $ret;}
	$p_arr['y'] = intval($p_arr['y']);
	$numezile = array('LU', 'MA', 'MI', 'JO', 'VI', 'SA', 'DU');
	$lastday = intval(date("t",strtotime($p_arr['y'].'-'.$p_arr['m'].'-1')));
	$ret['planificare_grid'] = cs('planificare/grid',array("rows"=>31,"filters"=>array("rules"=>array(
		array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
		array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		array("field"=>"start","op"=>"ge","data"=>date("Y-m-d 00:00:00",strtotime($p_arr['y'].'-'.$p_arr['m'].'-1'))),
		array("field"=>"stop","op"=>"le","data"=>date("Y-m-t 23:59:59",strtotime($p_arr['y'].'-'.$p_arr['m'].'-1'))),
	))));
	if (!isset($ret['planificare_grid']['success']) || ($ret['planificare_grid']['success'] != true)) {return $ret;}
	for ($d = 1; $d <= $lastday; $d++){
		$n = date("N",strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$d));
		$day = array();
		$day['n'] = $n;
		$day['nz'] = $numezile[$n-1];
		$day['start'] = '';
		$day['stop'] = '';
		$day['starth'] = '';
		$day['stoph'] = '';
		$day['legenda'] = '';
		$day['legendaid'] = '';
		foreach($ret['planificare_grid']['resp']['rows'] as $pi){
			if ((strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$d . ' 00:00:00')<=strtotime($pi->start))
				&& (strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$d . ' 23:59:59')>=strtotime($pi->start))
			){
				$day['start'] = $pi->start;
				$day['stop'] = $pi->stop;
				$day['starth'] = date("H:i:s", strtotime($pi->start));
				$day['stoph'] = date("H:i:s", strtotime($pi->stop));
				$ret['planificare_legendacachetoname'] =  cs('planificare/legendacachetoname',array(
					"spital"=>$p_arr['spital'],
					"start"=>$day['starth'],
					"stop"=>$day['stoph'],
				));
				if (isset($ret['planificare_legendacachetoname']['success']) && ($ret['planificare_legendacachetoname']['success'] == true)) {
					$day['legenda'] = $ret['planificare_legendacachetoname']['resp']->nume;
					$day['legendaid'] = $ret['planificare_legendacachetoname']['resp']->id;
				}
				break;
			}
		}
		$ret['resp'][] = $day;
	}
	$ret['success'] = true;
	return $ret;
}
function planificare_parse($p_arr){
	$ret = array('success'=>false,'resp'=>array(),'error'=>'');
	planificare_foldercheck();
	global $planificare_path_curent;
	require_once __DIR__ . '/../PhpSpreadsheet-develop/src/Bootstrap.php';	

	if (!isset($p_arr['spreadsheet'])){ 
		if (!isset($p_arr['fname'])){ $ret['error'] = 'check param - fname'; return $ret;}
		$spreadsheet = IOFactory::load($planificare_path_curent . DIRECTORY_SEPARATOR . $p_arr['fname']);
	}else{
		$spreadsheet = $p_arr['spreadsheet'];
	}
	
	$ret['planificare_paramsfromfile'] =  cs('planificare/paramsfromfile',array("spreadsheet"=>$spreadsheet));
	if (!isset($ret['planificare_paramsfromfile']['success']) || ($ret['planificare_paramsfromfile']['success'] != true)) {return $ret;}
	$ret['key'] = md5($ret['planificare_paramsfromfile']['resp']['spital'] 
		. $ret['planificare_paramsfromfile']['resp']['y'] 
		. $ret['planificare_paramsfromfile']['resp']['m'] 
		. 'poscrt1222'
	);
	if ($ret['key'] != $ret['planificare_paramsfromfile']['resp']['key']) {$ret['error'] = 'security'; return $ret;}
	
	$lastday = intval(date("t",strtotime($ret['planificare_paramsfromfile']['resp']['y'] .'-'.$ret['planificare_paramsfromfile']['resp']['m'].'-1')));
	$ret['lastday'] = $lastday;
	/*
	$ret['row'] = 0;
	$ret['doctor'] = $spreadsheet->setActiveSheetIndex(0)->getCell('C'.(8 + $ret['row']))->getValue();
	while($ret['doctor'] != ''){
		for ($d = 1; $d <= $ret['lastday']; $d++){
			$ret['util_columntoletter'] =  cs('util/columntoletter',array("column"=>$d + 4));
			if (!isset($ret['util_columntoletter']['success']) || ($ret['util_columntoletter']['success'] != true)) {return $ret;}
			$col = $ret['util_columntoletter']['resp'];
			$cv = $spreadsheet->getActiveSheet(0)->getCell($col.(8 + $ret['row']))->getValue();
			if ($cv != '') {
				//$ret['resp'][$ret['doctor'] . '_' . $d ] = $cv;
				$ret['planificare_legendacache'] =  cs('planificare/legendacache',array(
					"spital"=>$ret['planificare_paramsfromfile']['resp']['spital'],
					"nume"=>$cv
				));
				if (!isset($ret['planificare_legendacache']['success']) || ($ret['planificare_legendacache']['success'] != true)) {
						$ret['error'] = 'invalid entry ' . $cv . ' at ' . $col.(8 + $ret['row']);
						$ret['resp']['cellval'] = $cv;
						$ret['resp']['celladdr'] = $col.(8 + $ret['row']);
						return $ret;
				}
				$ret['resp'][$ret['doctor'] . '_' . $d . '_' . $cv ] = $cv;
			}
		}
		$ret['row']++;
		$ret['doctor'] = $spreadsheet->getActiveSheet(0)->getCell('C'.(8 + $ret['row']))->getValue();
	}
	
	*/
	$firstday = date("d",strtotime($ret['planificare_paramsfromfile']['resp']['y'] .'-'.$ret['planificare_paramsfromfile']['resp']['m'].'-1'));
	if (strtotime(date("Y-m-d",strtotime($ret['planificare_paramsfromfile']['resp']['y'] .'-'.$ret['planificare_paramsfromfile']['resp']['m'].'-1')))
		== strtotime(date("Y-m-1"))
	){
		$firstday = date('d');
	}
	$ret['start'] = date("Y-m-d",strtotime($ret['planificare_paramsfromfile']['resp']['y'] .'-'.$ret['planificare_paramsfromfile']['resp']['m'].'-' . $firstday));
	$ret['stop'] = date("Y-m-t 23:59:59",strtotime($ret['planificare_paramsfromfile']['resp']['y'] .'-'.$ret['planificare_paramsfromfile']['resp']['m'].'-1'));
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	$ret['row'] = 0;
	$ret['doctor'] = $spreadsheet->setActiveSheetIndex(0)->getCell('C'.(8 + $ret['row']))->getValue();
	$ret['db_creardoctormonth'] = 'DELETE'
		. ' FROM `planificare`'
		. ' WHERE'
			. ' `doctor` = ' . $ret['doctor']
			. ' AND `spital` = ' . $ret['planificare_paramsfromfile']['resp']['spital']
			. ' AND `start` >= "' . $ret['start'] . '"'
			. ' AND `stop` <= "' . $ret['stop'] . '"'
	;
	$r = $GLOBALS['cs_db_conn']->query($ret['db_creardoctormonth']);
	while($ret['doctor'] != ''){
		for ($d = 1; $d <= $ret['lastday']; $d++){
			$ret['util_columntoletter'] =  cs('util/columntoletter',array("column"=>$d + 4));
			if (!isset($ret['util_columntoletter']['success']) || ($ret['util_columntoletter']['success'] != true)) {return $ret;}
			$col = $ret['util_columntoletter']['resp'];
			$cv = $spreadsheet->getActiveSheet(0)->getCell($col.(8 + $ret['row']))->getValue();
			if ($cv != '') {
				//$ret['resp'][$ret['doctor'] . '_' . $d ] = $cv;
				$ret['planificare_legendacache'] =  cs('planificare/legendacache',array(
					"spital"=>$ret['planificare_paramsfromfile']['resp']['spital'],
					"nume"=>$cv
				));
				$start = date("Y-m-d H:i:s",strtotime(
					$ret['planificare_paramsfromfile']['resp']['y'] 
					.'-'.$ret['planificare_paramsfromfile']['resp']['m']
					.'-'.$d
					.' '.$ret['planificare_legendacache']['resp']->start
				));
				$stop = date("Y-m-d H:i:s",strtotime(
					$ret['planificare_paramsfromfile']['resp']['y'] 
					.'-'.$ret['planificare_paramsfromfile']['resp']['m']
					.'-'.$d
					.' '.$ret['planificare_legendacache']['resp']->stop
				));
				if (isset($ret['planificare_legendacache']['success']) || ($ret['planificare_legendacache']['success'] == true)) {
					$ret['planificare_update'] = planificare_update(array(
						'oper'=>'add'
						,'id'=>null
						,'doctor'=>$ret['doctor']
						,'spital'=>$ret['planificare_paramsfromfile']['resp']['spital']
						,'start'=>$start
						,'stop'=>$stop
					));
					//if (!isset($ret['planificare_update']['success'])||($ret['planificare_update']['success'] != true)){return $ret;}
				}
			}
		}
		$ret['row']++;
		$ret['doctor'] = $spreadsheet->getActiveSheet(0)->getCell('C'.(8 + $ret['row']))->getValue();
		$ret['db_creardoctormonth'] = 'DELETE'
			. ' FROM `planificare`'
			. ' WHERE'
				. ' `doctor` = ' . $ret['doctor']
				. ' AND `start` >= "' . $ret['start'] . '"'
				. ' AND `stop` <= "' . $ret['stop'] . '"'
		;
		$r = $GLOBALS['cs_db_conn']->query($ret['db_creardoctormonth']);
	}

	$ret['success'] = true;
	return $ret;
}
function planificare_tabel($p_arr){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($_SESSION['cs_users_id'])){$ret['error'] = 'login first'; return $ret;}
	if (!isset($p_arr['m'])){ $ret['error'] = 'check param - m'; return $ret;}
	$p_arr['m'] = intval($p_arr['m']);
	if (!isset($p_arr['y'])){ $ret['error'] = 'check param - y'; return $ret;}
	$p_arr['y'] = intval($p_arr['y']);
	
	$ret['spitale_users_getlevel'] = cs('spitale_users/getlevel',array('spital'=>$p_arr['spital']));
	if (!isset($ret['spitale_users_getlevel']['success']) || ($ret['spitale_users_getlevel']['success'] != true)) {return $ret;}
	
	if ($ret['spitale_users_getlevel']['resp'] < user_level_doctor){$ret['error'] = 'level min doctor required'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$numezile = array('LU', 'MA', 'MI', 'JO', 'VI', 'SA', 'DU');
	
	
	$ret['spitale_get'] = cs('spitale/get',array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['spital']),
	))));
	if ($ret['spitale_get'] == null) { $ret['error'] = 'no such'; return $ret;}
	
	if ($ret['spitale_users_getlevel']['resp'] == user_level_doctor){
		$ret['users_grid'] = cs('users/grid', array(
			"filters"=>array("rules"=>array(
				array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
			)),
			"rows"=>100,
		));
		if ($ret['users_grid'] == null) {return $ret;}
		$ret['doctori'] = $ret['users_grid'];
	}else{
		$ret['doctori_sql'] = 'SELECT users.id AS id';
		$ret['doctori_sql'] .= '	, users.nume AS nume ';
		$ret['doctori_sql'] .= ' FROM spitale_users';
		$ret['doctori_sql'] .= ' LEFT JOIN users ON spitale_users.user = users.id';
		$ret['doctori_sql'] .= ' WHERE spitale_users.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
		$ret['doctori_sql'] .= ' AND (SELECT id FROM specializari_user_spitale WHERE (spitale_users.user = specializari_user_spitale.user) AND (spitale_users.spital = specializari_user_spitale.spital) LIMIT 0,1) IS NOT NULL';
		
		$ret['doctori'] = cs("_cs_grid/get",array('db_sql'=>$ret['doctori_sql']));
		if (!isset($ret['doctori']['success']) || ($ret['doctori']['success'] != true)) {return $ret;}
					
		//$ret['users_getspitalusers'] = cs('users/getspitalusers',array("spital"=>$p_arr['spital'],"level"=>user_level_doctor));
		//if (!isset($ret['users_getspitalusers']['success']) || ($ret['users_getspitalusers']['success'] != true)) {return $ret;}
		//$doctori_list = $ret['users_getspitalusers'];
	}
	$lastday = intval(date("t",strtotime($p_arr['y'].'-'.$p_arr['m'].'-1')));
	ob_start(); 
	?> 
		<style>
			.weekend{
				background-color:yellow;
			}
			.planificare_tabel_tdd{
				width:25px;
				max-width:25px;
				padding: 0 !important;
				text-align: center;
				vertical-align: middle !important;
			}
			
		</style>
		<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>nume</th>
					<?php for ($d = 1; $d <= $lastday; $d++){
						$n = date("N",strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$d));
						$weekend = '';
						if ($n >= 6) $weekend = 'weekend';
					?>
						<th class="planificare_tabel_tdd <?php echo $weekend;?>"><?php echo $d . ' ' . $numezile[$n-1];?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php 
				$di = 0;
				foreach($ret['doctori']['resp']['rows'] as $doctor){
					$ret['planificare_monthget'] = cs('planificare/monthget',array(
						'doctor'=>$doctor->id,
						'spital'=>$p_arr['spital'],
						'm'=>$p_arr['m'],
						'y'=>$p_arr['y'],
					));
					if (!isset($ret['planificare_monthget']['success']) || ($ret['planificare_monthget']['success'] != true)) {return $ret;}
				?>
				<tr data-doctorid="<?php echo $doctor->id;?>">
					<td><?php echo $di + 1;?></td>
					<td><?php echo $doctor->nume;?></td>
					<?php 
						$d = 0;
						$nowdate = strtotime(date('Y-m-d'));
						foreach($ret['planificare_monthget']['resp'] as $doctorp){
						$d++;
						$weekend = '';
						if ($doctorp['n'] >= 6) $weekend = 'weekend';
						$cdate = strtotime($p_arr['y'].'-'.$p_arr['m'].'-'.$d);
						$active = '';
						if ($cdate >= $nowdate) $active = 'activeday'
					?>
						<td class="planificare_tabel_tdd <?php 
							echo $weekend . ' ' . $active;?>" data-legendaid="<?php 
							echo $doctorp['legendaid']?>" data-d="<?php 
							echo $d?>">
							<?php echo $doctorp['legenda']?>
						</td>
					<?php }?>
				</tr>
				<?php 
				$di++;
				}?>
			</tbody>
		</table>
	<?php
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
function planificare_save($p_arr){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	if (!isset($p_arr['m'])){ $ret['error'] = 'check param - m'; return $ret;}
	$p_arr['m'] = intval($p_arr['m']);
	if (!isset($p_arr['y'])){ $ret['error'] = 'check param - y'; return $ret;}
	$p_arr['y'] = intval($p_arr['y']);
	if (!isset($p_arr['planificare'])){ $ret['error'] = 'check param - planificare'; return $ret;}
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	
	
	$firstday = date("d",strtotime($p_arr['y'] .'-'.$p_arr['m'].'-1'));
	if (strtotime(date("Y-m-d",strtotime($p_arr['y'] .'-'.$p_arr['m'].'-1')))
		== strtotime(date("Y-m-1"))
	){
		$firstday = date('d');
	}
	$ret['start'] = date("Y-m-d",strtotime($p_arr['y'] .'-'.$p_arr['m'].'-' . $firstday));
	$ret['stop'] = date("Y-m-t 23:59:59",strtotime($p_arr['y'] .'-'.$p_arr['m'].'-1'));

	if (count($p_arr['planificare']) == 0) { $ret['error'] = 'check param - planificare count'; return $ret;}

	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	foreach ($p_arr['planificare'] as $planificare){
		$ret['db_creardoctormonth'] = 'DELETE'
			. ' FROM `planificare`'
			. ' WHERE'
				. ' `doctor` = ' . $planificare['doctorid']
				. ' AND `spital` = "' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']) . '"'
				. ' AND `start` >= "' . $ret['start'] . '"'
				. ' AND `stop` <= "' . $ret['stop'] . '"'
		;
		$r = $GLOBALS['cs_db_conn']->query($ret['db_creardoctormonth']);
		foreach ($planificare['days'] as $day){
			if ($day['legenda'] != ''){
				$ret['planificare_legendacache'] =  cs('planificare/legendacache',array(
					"spital"=>$p_arr['spital'],
					"nume"=>$day['legenda']
				));	
				$start = date("Y-m-d H:i:s",strtotime(
					$p_arr['y']
					.'-'.$p_arr['m']
					.'-'.$day['d']
					.' '.$ret['planificare_legendacache']['resp']->start
				));
				$stop = date("Y-m-d H:i:s",strtotime(
					$p_arr['y']
					.'-'.$p_arr['m']
					.'-'.$day['d']
					.' '.$ret['planificare_legendacache']['resp']->stop
				));
				$ret['planificare_update'] = planificare_update(array(
					'oper'=>'add'
					,'id'=>null
					,'doctor'=>$planificare['doctorid']
					,'start'=>$start
					,'stop'=>$stop
					,'spital'=>$p_arr['spital']
				));
				if (!isset($ret['planificare_update']['success'])||($ret['planificare_update']['success'] != true)){return $ret;}
			}
		}
	}
	
	$ret['success'] = true;
	return $ret;
}
function planificare_ymchoose($p_arr){
	$ret = array('success'=>false, 'resp'=>array() ,'error'=>'');
	if (!isset($p_arr['m'])){ $p_arr['m'] = intval(date('m'));}
	if (!isset($p_arr['y'])){ $p_arr['y'] = intval(date('Y'));}
	
	$numeluni = array('ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie');
	ob_start(); 
	?> 
	<div style="margin:20px 0">
		<button class="btn btn-primary" onclick="planificare_ymchoose_onclick(-1)"><i class="fa fa-arrow-circle-left" aria-hidden="true"></i></button> 
		<span id="planificare_ymchoose_ym" data-m="<?php echo $p_arr['m'];?>" data-y="<?php echo $p_arr['y'];?>" style="text-align: center;min-width:120px;display: inline-block;"><?php echo $numeluni[$p_arr['m'] - 1] . ' ' . $p_arr['y'];?></span>
		<button class="btn btn-primary" onclick="planificare_ymchoose_onclick(1)"><i class="fa fa-arrow-circle-right" aria-hidden="true"></i></button> 
	</div>
	<script>
		planificare_ymchoose_onclick = function(i){
			var numeluni = <?php echo json_encode($numeluni);?>;
			var e = document.querySelector('#planificare_ymchoose_ym')
			i = parseInt(i)
			var m = parseInt(document.querySelector('#planificare_ymchoose_ym').dataset.m)
			var y = parseInt(document.querySelector('#planificare_ymchoose_ym').dataset.y)
			m = m + i
			if (m > 12){m = 1; y++;}
			if (m < 1){m = 12; y--;}
			e.innerHTML = numeluni[m - 1] + ' ' + y
			e.setAttribute('data-m', m)
			e.setAttribute('data-y', y)
			<?php if (isset($p_arr['callback'])) {echo $p_arr['callback'] . '(m,y)';}?>
		}
	</script>
	<?php
	$ret['resp']['html'] = ob_get_contents();ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
?>