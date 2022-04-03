<?php
$GLOBALS['planifauto_cols'] = array(
	'id'				=>array('type'=>'int',	),
	'spital'			=>array('type'=>'int',),
	'doctor'			=>array('type'=>'int',),
	'dow'				=>array('type'=>'int',),
	'start'				=>array('type'=>'time',),
	'stop'				=>array('type'=>'time',),
);
function planifauto_get($p_arr = array()){
	$ret = null;
	$list = planifauto_grid($p_arr);
	if (!isset($list['success']) || ($list['success'] != true)) return null;
	if (count($list['resp']['rows'])>0){
		$ret["id"] = intval($list['resp']['rows'][0]->id);
		$ret["spital"] = intval($list['resp']['rows'][0]->spital);
		$ret["doctor"] = intval($list['resp']['rows'][0]->doctor);
		$ret["dow"] = intval($list['resp']['rows'][0]->dow);
		$ret["start"] = $list['resp']['rows'][0]->start;
		$ret["stop"] = $list['resp']['rows'][0]->stop;

	}
	return $ret;
}
function planifauto_grid($p_arr = array()){
	global $planifauto_cols;
	$p_arr['db_cols'] = $planifauto_cols;
	$p_arr['db_table'] = 'planifauto';
	return cs("_cs_grid/get",$p_arr);
}
function planifauto_update($p_arr = array()){
	global $planifauto_cols;
	$p_arr['db_cols'] = $planifauto_cols;
	$p_arr['db_table'] = 'planifauto';
	return cs("_cs_grid/update",$p_arr);
}
function planifauto_autotable($p_arr){
	$ret = array('success'=>false, 'resp'=>array());
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($_SESSION['cs_users_id'])){$ret['error'] = 'login first'; return $ret;}
	$ret['spitale_users_getlevel'] = cs('spitale_users/getlevel',array('spital'=>$p_arr['spital']));
	if (!isset($ret['spitale_users_getlevel']['success']) || ($ret['spitale_users_getlevel']['success'] != true)) {return $ret;}
	
	if ($ret['spitale_users_getlevel']['resp'] < user_level_doctor){$ret['error'] = 'level min doctor required'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	
	$ret['spitale_get'] = cs('spitale/get',array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$p_arr['spital']),
	))));
	if ($ret['spitale_get'] == null) { $ret['error'] = 'no such'; return $ret;}
	
	if ($ret['spitale_users_getlevel']['resp'] == user_level_doctor){
		$ret['doctori_sql'] = 'SELECT users.id AS id';
		$ret['doctori_sql'] .= '	, users.nume AS nume ';
		$ret['doctori_sql'] .= '	, spitale_users.autoplanificare AS autoplanificare ';
		$ret['doctori_sql'] .= ' FROM spitale_users';
		$ret['doctori_sql'] .= ' LEFT JOIN users ON spitale_users.user = users.id';
		$ret['doctori_sql'] .= ' WHERE spitale_users.user = '. $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
		$ret['doctori_sql'] .= ' AND (SELECT id FROM specializari_user_spitale WHERE (spitale_users.user = specializari_user_spitale.user) AND (spitale_users.spital = specializari_user_spitale.spital) LIMIT 0,1) IS NOT NULL';
		
		$ret['doctori'] = cs("_cs_grid/get",array('db_sql'=>$ret['doctori_sql']));
		if (!isset($ret['doctori']['success']) || ($ret['doctori']['success'] != true)) {return $ret;}

		
	}else{
		$ret['doctori_sql'] = 'SELECT users.id AS id';
		$ret['doctori_sql'] .= '	, users.nume AS nume ';
		$ret['doctori_sql'] .= '	, spitale_users.autoplanificare AS autoplanificare ';
		$ret['doctori_sql'] .= ' FROM spitale_users';
		$ret['doctori_sql'] .= ' LEFT JOIN users ON spitale_users.user = users.id';
		$ret['doctori_sql'] .= ' WHERE spitale_users.spital = '. $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
		$ret['doctori_sql'] .= ' AND (SELECT id FROM specializari_user_spitale WHERE (spitale_users.user = specializari_user_spitale.user) AND (spitale_users.spital = specializari_user_spitale.spital) LIMIT 0,1) IS NOT NULL';
		
		$ret['doctori'] = cs("_cs_grid/get",array('db_sql'=>$ret['doctori_sql']));
		if (!isset($ret['doctori']['success']) || ($ret['doctori']['success'] != true)) {return $ret;}
	}
	ob_start(); 
	?> 
		<style>
			.auto_table_zisapt{
				white-space: nowrap;
				text-align:center
			}
			.select-edit{
				-webkit-appearance: none;
				height:30px;
				text-align-last: center;
			}
		</style>
		<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>nume</th>
					<th>activ</th>
					<th>luni</th>
					<th>marti</th>
					<th>miercuri</th>
					<th>joi</th>
					<th>vineri</th>
					<th>sambata</th>
					<th>duminica</th>
				</tr>
			</thead>
			<tbody>
				<?php 
				$di = 0;
				$ret['weekdays'] = array();
				foreach($ret['doctori']['resp']['rows'] as $doctor){
				?>
				<tr data-doctorid="<?php echo $doctor->id;?>" data-spital="<?php echo $p_arr['spital'];?>">
					<td><?php echo $di + 1;?></td>
					<td><?php echo $doctor->nume;?></td>
					<td style="text-align:center"><input type="checkbox" name="activ" <?php if (intval($doctor->autoplanificare)>0) echo "checked='true'" ?> ></td>
					<?php
					for ($zisapt = 1; $zisapt <=7; $zisapt++){
						$weekday = array();
						$weekday['start'] = '00:00:00';
						$weekday['stop'] = '00:00:00';
						$weekday['detectday'] = cs('planifauto/auto_detectday',array(
							'spital'=>$p_arr['spital'],
							'doctor'=>$doctor->id,
							'dow'=>$zisapt,
						));
						if (isset($weekday['detectday']['success']) && ($weekday['detectday']['success'] == true)) {
							$weekday['start'] = $weekday['detectday']['start'];
							$weekday['stop'] = $weekday['detectday']['stop'];
						}
					?>
					<td style="padding:8px 0">
						<div class="auto_table_zisapt">
							<select name="start_<?php echo $zisapt;?>" class="select-edit">
								<option value="">-</option>
								<?php 
								$stime = strtotime("2000-01-01 07:00:00");
								$etime = strtotime("2000-01-01 22:00:00");
								$ctime = $stime;
								while($ctime <= $etime){
									?>
									<option value="<?php echo date('H:i',$ctime);?>" <?php if (strtotime('2000-01-01 ' . $weekday['start']) == $ctime) echo "selected='selected'"?>>
										<?php echo date('H:i',$ctime);?>
									</option>
									<?php
									$ctime = strtotime("+30 minutes", $ctime);
								}
								?>
							</select>
							<select name="stop_<?php echo $zisapt;?>" class="select-edit">
								<option value="">-</option>
								<?php 
								$stime = strtotime("2000-01-01 07:00:00");
								$etime = strtotime("2000-01-01 22:00:00");
								$ctime = $stime;
								while($ctime <= $etime){
									?>
									<option value="<?php echo date('H:i',$ctime);?>" <?php if (strtotime('2000-01-01 ' . $weekday['stop']) == $ctime) echo "selected='selected'"?>>
										<?php echo date('H:i',$ctime);?>
									</option>
									<?php
									$ctime = strtotime("+30 minutes", $ctime);
								}
								?>
							</select>
						</div>
					</td>
					<?php 
					$ret['weekdays'][] = $weekday;
					}
					?>
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
function planifauto_generate($p_arr = array()){
	/*
	{personal[
		{spital,doctor},
		{spital,doctor},
		..
	]}
	*/
	$ret = array('success'=>false, 'resp'=>array());
	if (!isset($p_arr['personal'])){ $ret['error'] = 'check param - personal'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	$ret['maxday'] = date('Y-m-d 00:00:00', strtotime('+ ' . autoplanif_cronWeeks . ' weeks'));
	foreach ($p_arr['personal'] as $personal){
		$ret['spitale_users_get'] = cs('spitale_users/get',array("filters"=>array("rules"=>array(
			array("field"=>"spital","op"=>"eq","data"=>$personal['spital']),
			array("field"=>"user","op"=>"eq","data"=>$personal['doctor']),
		))));
		if ($ret['spitale_users_get'] == null) { $ret['error'] = 'null spitale_users_get'; return $ret;}
		$ret['autoplanificare'] = intval($ret['spitale_users_get']['autoplanificare']);
		if ($ret['autoplanificare'] == 0) {continue;}
		$ret['croncontinue'] = date('Y-m-d 00:00:00');
		if (strtotime(date('Y-m-d 00:00:00')) < strtotime($ret['spitale_users_get']['croncontinue'])){
			$ret['croncontinue'] = date('Y-m-d 00:00:00', strtotime($ret['spitale_users_get']['croncontinue']));
		}

		$ret['delplanificare_sql'] = 'DELETE'
			. ' FROM `planificare`'
			. ' WHERE'
				. ' `doctor` = ' . $GLOBALS['cs_db_conn']->real_escape_string($personal['doctor'])
				. ' AND `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($personal['spital'])
				. ' AND `start` >= "' . $ret['croncontinue'] . '"'
		;
		$ret['delplanificare'] = $GLOBALS['cs_db_conn']->query($ret['delplanificare_sql']);
		if ($ret['delplanificare'] == false){ $ret['error'] = 'error delplanificare '; return $ret;}

		$ret['planifauto_grid'] = cs('planifauto/grid',array("rows"=>7,"filters"=>array("rules"=>array(
			array("field"=>"spital","op"=>"eq","data"=>$personal['spital']),
			array("field"=>"doctor","op"=>"eq","data"=>$personal['doctor']),
		))));
		/**/
		if (!isset($ret['planifauto_grid']['success']) || ($ret['planifauto_grid']['success'] != true)) {$ret['error'] = 'error planifauto_grid '; return $ret;}
		$weekdaynames = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
		$ret['planifdata'] = array();
		if ($ret['planifauto_grid']['resp']['records'] > 0) foreach($ret['planifauto_grid']['resp']['rows'] as $autoday){
			$dataitem = array();
			$dataitem['days'] = array();
			$dataitem['autoday'] = $autoday;
			
			$dataitem['legenda_adaugainterval'] = cs('legenda/adaugainterval',array(
				'spital'=>$personal['spital'],
				'start'=>$autoday->start,
				'stop'=>$autoday->stop,
			));
			
			if (date('N',strtotime($ret['croncontinue'])) == $autoday->dow){
				$dataitem['days'][] = $ret['croncontinue'];
			}
			$dataitem['nextday'] = date('Y-m-d 00:00:00', strtotime('next ' . $weekdaynames[$autoday->dow - 1], strtotime($ret['croncontinue'])));
			//$dataitem['planif_sql'] = 
			$dataitem['planif_sql'] = array();
			while( strtotime($dataitem['nextday']) <= strtotime($ret['maxday']) ){
				$dataitem['days'][] = $dataitem['nextday'];
				$dataitem['nextday'] = date('Y-m-d 00:00:00', strtotime('next ' . $weekdaynames[$autoday->dow - 1], strtotime($dataitem['nextday'])));
			}
			foreach($dataitem['days'] as $day){
				$dataitem['planif_sql'][] = '(' 
					. $personal['spital'] 
					. ', ' . $personal['doctor'] 
					. ', "' . date('Y-m-d', strtotime($day)) . ' ' . $autoday->start . '"'
					. ', "' . date('Y-m-d', strtotime($day)) . ' ' . $autoday->stop . '"'
				. ')';
			}
			if (count($dataitem['planif_sql']) > 0){
				$ret['planif_sql'] = 'INSERT INTO planificare (spital, doctor, start, stop) VALUES '
					. implode(', ', $dataitem['planif_sql'])
				;
				$ret['planif'] = $GLOBALS['cs_db_conn']->query($ret['planif_sql']);
				if ($ret['planif'] == false){ $ret['error'] = 'error planif '; return $ret;}
			}
			$ret['planifdata'][] = $dataitem;
		}
		$ret['updatespitale_users_sql'] = 'UPDATE spitale_users SET '
			. ' cronnext = "' . date('Y-m-d 00:00:00', strtotime('+ ' . autoplanif_cronDaysInterval . ' days', strtotime(date('Y-m-d 00:00:00')))) . '"'
			. ', croncontinue = "' . date('Y-m-d 00:00:00', strtotime('+ ' . autoplanif_cronWeeks . ' weeks')) . '"'
			. ' WHERE'
				. ' `user` = ' . $GLOBALS['cs_db_conn']->real_escape_string($personal['doctor'])
				. ' AND `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($personal['spital'])
		;
		$ret['updatespitale_users'] = $GLOBALS['cs_db_conn']->query($ret['updatespitale_users_sql']);
		if ($ret['updatespitale_users'] == false){ $ret['error'] = 'error updatespitale_users '; return $ret;}
		/**/
	}
	$ret['success'] = true;
	return $ret;
}
function planifauto_set($p_arr = array()){
	/*
	{personal[
		{spital,doctor,activ,week[{dow,start,stop},{dow,start,stop}..]},
		{spital,doctor,activ,week[{dow,start,stop},{dow,start,stop}..]},
		..
	]}
	*/
	$ret = array('success'=>false, 'resp'=>array());
	if (!isset($p_arr['personal'])){ $ret['error'] = 'check param - personal'; return $ret;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');
	foreach ($p_arr['personal'] as $personal){
		$ret['delplanifauto_sql'] = 'DELETE'
			. ' FROM `planifauto`'
			. ' WHERE'
				. ' `doctor` = ' . $GLOBALS['cs_db_conn']->real_escape_string($personal['doctor'])
				. ' AND `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($personal['spital'])
		;
		$ret['delplanifauto'] = $GLOBALS['cs_db_conn']->query($ret['delplanifauto_sql']);
		if ($ret['delplanifauto'] == false){ $ret['error'] = 'error delplanifauto '; return $ret;}
		
		$ret['autoplanificare'] = 0;
		$ret['cronnext'] = '0000-00-00 00:00:00';
		$ret['cronstart'] = '0000-00-00 00:00:00';
		$ret['croncontinue'] = '0000-00-00 00:00:00';
		if ($personal['activ'] && (count($personal['week']) > 0)){
			$ret['autoplanificare'] = 1;
			$ret['cronnext'] = date('Y-m-d 00:00:00', strtotime('+ ' . autoplanif_cronDaysInterval . ' days', strtotime(date('Y-m-d 00:00:00'))));
		}
		
		$ret['updatespitale_users_sql'] = 'UPDATE spitale_users SET '
			. ' autoplanificare = ' . $ret['autoplanificare']
			. ', cronnext = "' . $ret['cronnext'] . '"'
			. ', cronstart = "' . $ret['cronstart'] . '"'
			. ', croncontinue = "' . $ret['croncontinue'] . '"'
			. ' WHERE'
				. ' `user` = ' . $GLOBALS['cs_db_conn']->real_escape_string($personal['doctor'])
				. ' AND `spital` = ' . $GLOBALS['cs_db_conn']->real_escape_string($personal['spital'])
		;
		$ret['updatespitale_users'] = $GLOBALS['cs_db_conn']->query($ret['updatespitale_users_sql']);
		if ($ret['updatespitale_users'] == false){ $ret['error'] = 'error updatespitale_users '; return $ret;}
		
		if ($personal['activ'] && (count($personal['week']) > 0)){
			foreach($personal['week'] as $day){
				$ret['planifauto_update'] = planifauto_update(array(
					'oper'=>'add'
					,'id'=>null
					,'spital'=>$personal['spital']
					,'doctor'=>$personal['doctor']
					,'start'=>$day['start']
					,'stop'=>$day['stop']
					,'dow'=>$day['dow']
				));
				if (!isset($ret['planifauto_update']['success'])||($ret['planifauto_update']['success'] != true)){$ret['error'] = 'error planifauto_update '; return $ret;}
			}
		}
		
	}
	$ret['success'] = true;
	return $ret;
}
function planifauto_auto_detectday($p_arr){
	$ret = array('success'=>false, 'resp'=>array(), 'p_arr'=>$p_arr);
	if (!isset($p_arr['spital'])){ $ret['error'] = 'check param - spital'; return $ret;}
	if (!isset($p_arr['doctor'])){ $ret['error'] = 'check param - doctor'; return $ret;}
	if (!isset($p_arr['dow'])){ $ret['error'] = 'check param - dow'; return $ret;}
	$ret['dow'] = intval($p_arr['dow']);
	$ret['dow']++;
	if ($ret['dow'] > 7) {$ret['dow'] = 1;}
	require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_db.php');

	$ret['planifauto'] = cs('planifauto/get', array(
		"filters"=>array("rules"=>array(
			array("field"=>"dow","op"=>"eq","data"=>$p_arr['dow']),
			array("field"=>"spital","op"=>"eq","data"=>$p_arr['spital']),
			array("field"=>"doctor","op"=>"eq","data"=>$p_arr['doctor']),
		)),
	));
	if ($ret['planifauto'] != null) {
		$ret['start'] = $ret['planifauto']['start'];
		$ret['stop'] = $ret['planifauto']['stop'];
		$ret['success'] = true;
	}else{
		$ret['planificare_sql'] = 'SELECT * ';
		$ret['planificare_sql'] .= ' FROM planificare';
		$ret['planificare_sql'] .= ' WHERE planificare.doctor = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['doctor']);
		$ret['planificare_sql'] .= ' AND planificare.spital = ' . $GLOBALS['cs_db_conn']->real_escape_string($p_arr['spital']);
		$ret['planificare_sql'] .= ' AND DAYOFWEEK(planificare.start) = ' . $GLOBALS['cs_db_conn']->real_escape_string($ret['dow']);
		$ret['planificare_sql'] .= ' ORDER BY planificare.start DESC';
		$ret['planificare_sql'] .= ' LIMIT 0,1';
		
		$ret['planificare'] = cs("_cs_grid/get",array('db_sql'=>$ret['planificare_sql']));
		if (!isset($ret['planificare']['success']) || ($ret['planificare']['success'] != true)) {return $ret;}
		if ($ret['planificare']['resp']['records'] > 0){
			$ret['start'] = date('H:i:s',strtotime($ret['planificare']['resp']['rows'][0]->start));
			$ret['stop'] = date('H:i:s',strtotime($ret['planificare']['resp']['rows'][0]->stop));
			$ret['success'] = true;
		}
	}	
	return $ret;

}

?>