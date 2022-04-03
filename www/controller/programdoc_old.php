<?php

function programdoc_sortbymonth($p_arr = array()){
		$ret = array('success'=>false, 'resp'=>array('html'=>''));
		if (!isset($p_arr['an']) || $p_arr['an'] == ''){ $ret['error'] = 'check param - an'; return $ret;}
		if (!isset($p_arr['luna']) || $p_arr['luna'] == ''){ $ret['error'] = 'check param - luna'; return $ret;}
	//	if (!isset($p_arr['did']) || $p_arr['did'] == ''){ $ret['error'] = 'check param - did'; return $ret;}
		
		$ret['p_arr'] = $p_arr;
		$datastart = $p_arr['an'].'-'.$p_arr['luna'].'-01 00:00:00';
		$ret['dataend'] = $p_arr['an'].'-'.$p_arr['luna'].'-30 24:00:00';
			//	$datastart = '2018-09-01 00:00:00';
			//	$ret['dataend'] = '2018-09-30 24:00:00';

		$ret['programari_grid'] = cs('programari/grid',array(
							"filters"=>array("rules"=>array(
								array("field"=>"start","op"=>"ge","data"=>$datastart),
								array("field"=>"start","op"=>"le","data"=>$ret['dataend']),
								array("field"=>"doctor","op"=>"eq","data"=>$p_arr['did']),
							)),
							'sord'=>'asc',// sortat ascendent
							'sidx'=>'start', // sortat dupa coloana start
							'rows'=>500, // pentru luni mai pline
						));
		if(!isset($ret['programari_grid']['success']) || ($ret['programari_grid']['success'] != true)) 
		{return $ret;}

	ob_start();
			?>
				<table class="table table-hover">
					<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col">nume</th>
							<th scope="col">ora</th>
							<th scope="col">serviciu</th>
							<th scope="col">observatii</th>
							<th scope="col">...</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$data = '';
						$dataverif = '';
						$nrcrt = 0;
						if (count($ret['programari_grid']['resp']['rows'])>0){
							foreach ($ret['programari_grid']['resp']['rows'] as $programare){
							//	echo 'testin';
								$prog = explode(" ", $programare->start );
								$prog2 = explode(" ", $programare->stop);
								$data = date("d-m-Y", strtotime($prog[0]));
								$nrcrt += 1;
						//		$dataverif = $data;	/////////////////////
		
							
								$ret['specializari_get'] = cs('specializari/get',array(
									"filters"=>array("rules"=>array(
										array("field"=>"id","op"=>"eq","data"=>$programare->specializare)
									))
								));
								
								$ret['users_get'] = cs('users/get',array(
									"filters"=>array("rules"=>array(
										array("field"=>"id","op"=>"eq","data"=>$programare->user)
									))
								));
							//	if(!isset($ret['specializari_grid']['success']) || ($ret['specializari_grid']['success'] != true)) 
							//	{return $ret;}
							
							/*
								foreach ($ret['specializari_grid']['resp']['rows'] as $specializ){						
									
								if($programare->specializare == $specializ->id){
								*/	
										$progsh=explode(":", $prog[1]);
										$progsh2=explode(":", $prog2[1]);
										
									if($data!=$dataverif){
										$nrcrt=1;
									?>
									<tr ><th colspan=5 scope="col" style="background-color: #cccccc;"><?php echo $data;?></td></tr>
									<?php } ?>
									<tr class="table-warning">
										<th scope="row"><?php echo $nrcrt; ?></th>
										<td class="table-active"><?php echo $ret['users_get']['nume']; ?></td>
										<td><?php echo $progsh[0].':'.$progsh[1].' - '.$progsh2[0].':'.$progsh2[1]; ?></td>
										<td><?php echo $ret['specializari_get']['denumire']; ?></td>
										<td><?php echo htmlspecialchars($programare->observatii); ?></td>
										<td>
											<?php // echo $specializ->id; ?>
										</td>
									</tr>
								<?php /* }else
										continue;
									*/
							//		}	
									$dataverif = $data;	
								
								}?>
						<?php }?>
					</tbody>
				</table>

		<?php
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;

	}
?>