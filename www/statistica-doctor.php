<?php
require_once("_cs_config.php");
$is_success = true;

if ($is_success){
	//$is_success = false;
	$cauta_initparams = cs('cauta/initparams');
	if (!isset($cauta_initparams['success']) || ($cauta_initparams['success'] == false)) {$is_success = false;}
	extract($cauta_initparams['resp']);
}
if ($is_success){
	//$is_success = false;
	$cauta_form = cs('cauta/form',array('cauta_initparams'=>$cauta_initparams));
	if (!isset($cauta_form['success']) || ($cauta_form['success'] == false)) {$is_success = false;}
}

if ($is_success){
	$is_success = false;
	$parsed = parse_url($_SERVER['REQUEST_URI']);
	$parsed = explode('/',$parsed['path']);
	$parsed1 = array();
	foreach($parsed as $item){
		if (($item != '')) $parsed1[] = $item;
	}
	if (count($parsed1) > 0) {$is_success = true;}
}

if ($is_success){
	$is_success = false;
	preg_match_all('/^([^\/]+)\.html$/m', $parsed1[count($parsed1) - 1], $matches, PREG_SET_ORDER, 0);
	if (
		(count($matches) > 0) 
		&& (count($matches[0]) > 1) 
	){$is_success = true;}

	$doctoruri = $matches[0][1];
}
if ($is_success){
	$doctor_get = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"uri","op"=>"eq","data"=>$doctoruri)
	))));
	if ($doctor_get == null) {$is_success = false;}
}
if ($is_success){
	$logs_count = cs('logs/count', array(
		'alttype'=>2,
		'altid'=>$doctor_get['id'],
	));
	if (!isset($logs_count['success']) || ($logs_count['success'] == false)) {$is_success = false;}
}
?>
<?php 
function header_ob(){ 
	global $is_success, $doctor_get;
	$ret = array('success'=>false, 'resp'=>array());
	ob_start();
	if ($is_success){
	?>
	<title>Statistica doctor <?php echo $doctor_get['nume']?> - MedList.ro</title>
	<meta name="robots" content="index, follow" />	
	<meta name="description" content="statistica vizualizari" />
	<meta name="keywords" content="statistica vizualizari" />
	<meta name="robots" content="index, follow" />	
	<?php }?>
	<style>
		#cauta{
			padding-top: 10px;
			padding-bottom:10px;
			background-color: #60b0f4;
		}

		.statisticGraph canvas
		{
			width:100%;
			height:250px;
		}

		.statisticGraph {
			width: 95%;
			float: left;
			margin: 20px 0 0 0;
			border: 0px solid #ddd;
			padding: 0;
			border-radius: 5px;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
		}

		.statisticGraph strong, .statisticGraph canvas {
			float: left;
			width: 100%!important;
		}

		.statisticGraph strong {
			font-size: 18px;
		}

		.statisticGraph canvas {
			margin-top: 10px;
		}
	</style>
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
} 
$GLOBALS['header_ob'] = header_ob();
require_once(cs_path . DIRECTORY_SEPARATOR . 'header.php' );
?>
<?php if ($is_success){?>
<?php echo $cauta_form['resp']['html']?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<h4>
				Statistica pentru doctorul: 
				<a href="<?php echo cs_url . '/profil-utilizator/' . $doctor_get['uri'] . '.html'; ?>" >
					<?php echo $doctor_get['nume']?>
				</a> 
			</h4>
		</div>
		<div class="col-xs-12">
			<i class="fa fa-eye" aria-hidden="true"></i> Vizualizări total: <?php echo $logs_count['resp']['count']; ?>
		</div>
		<div class="col-xs-12">
			<div class="statisticGraph">
				<strong>Vizualizări grafic:</strong> <canvas id="chart_views"></canvas>
			</div>
		</div>
	</div>
</div>
<?php }else{?>
oupssss.. nu am gasit ce căutați...
<?php }?>
<?php 
function footer_ob(){
	global 
		$cauta_localitati_breadcrumb
		, $cauta_specializari_breadcrumb
		, $cauta_uridecode
		, $cauta_date
		, $cauta_localitate_id
		, $cauta_specializare_id
		, $programari_date
		, $specializari_id
		, $doctor_get
		, $spitale_get
	;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<script src="<?php echo cs_url;?>/js/Chart.min.js"></script>
	<script>
		cauta_specializari_breadcrumb_parentid = 0;
		cauta_specializari_breadcrumb_parentparent = 0;
		cauta_localitati_breadcrumb_nodearr = <?php echo json_encode($cauta_localitati_breadcrumb['resp']['nodearr'])?>;
		cauta_specializari_breadcrumb_nodearr = <?php echo json_encode($cauta_specializari_breadcrumb['resp']['nodearr'])?>;
		cauta_localitate_id = <?php if ($cauta_localitate_id > 0){ echo $cauta_localitate_id; }else{echo 'null';}?>;
		cauta_specializare_id = <?php if ($cauta_specializare_id > 0){ echo $cauta_specializare_id; }else{echo 'null';}?>;

		cs('logs/graphdata',{alttype:2,altid:<?php echo $doctor_get['id']; ?>}).then(function(d){
			if (typeof(d.success) != 'undefined' && (d.success == true)){
				console.log(d.resp.viewdata)
				var viewhitlabels = [];
				var viewhitdata = [];
				$.map(d.resp.viewdata, function (val, i) {
					viewhitlabels.push(val.date);
					viewhitdata.push(val.count);
				});
				DrawViewhitsGraph(viewhitlabels, viewhitdata);
			}else{
				console.error(d)
			}
		})
        function DrawViewhitsGraph(labels, data) {
            var lineChartData = {
                labels: labels,
                datasets: [
                    {
                        label: "Vizualizari",
                        fillColor: "rgba(93,164,35,0.5)",
                        strokeColor: "rgba(93,164,35,1)",
                        pointColor: "rgba(93,164,35,1)",
                        pointStrokeColor: "#fff",
                        pointHighlightFill: "#fff",
                        pointHighlightStroke: "rgba(220,220,220,1)",
                        data: data
                    }
                ]

            }
            var ctx = document.getElementById("chart_views").getContext("2d");
            window.myLine = new Chart(ctx).Line(lineChartData, {
                responsive: true,
                pointHitDetectionRadius: 3
            });
        };
	</script>
	<script src="<?php echo cs_url;?>/js/cauta.js?timestamp=<?php echo cs_updatescript;?>"></script>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>