<?php
cscheck(array('success'=>isset($GLOBALS['cauta_uridecode'])));
$cauta_uridecode = $GLOBALS['cauta_uridecode'];

$cauta_initparams = cs('cauta/initparams',array('cauta_uridecode'=>$cauta_uridecode));
cscheck($cauta_initparams);
extract($cauta_initparams['resp']);
$cauta_form = cs('cauta/form',array('cauta_initparams'=>$cauta_initparams));
cscheck($cauta_form);

$cauta_list_params = array(
	'specializare' => $cauta_specializare_id,
	'localitate' => $cauta_localitate_id,
	'date' => $cauta_date,
);

parse_str($_SERVER['QUERY_STRING'],$paramremove);
$querystring = http_build_query(array_diff_key($paramremove,array("p_page"=>"")));

$cauta_side_ob = cs('cauta/side_ob',array(
	'nodes' => $cauta_uridecode['items'],
	'QUERY_STRING' => $querystring,
));
cscheck($cauta_side_ob);
?>

<?php 
function header_ob(){ 
	global $cauta_uridecode;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	$mytitle = array();
	$mytitletxt = '-';
	if (count($cauta_uridecode['items']['localitati']) > 0){
		foreach($cauta_uridecode['items']['localitati'] as $localitate){
			$mytitle[] = $localitate['denumire'];
		}
	}
	if (count($cauta_uridecode['items']['specializari']) > 0){
		foreach($cauta_uridecode['items']['specializari'] as $specializare){
			$mytitle[] = $specializare['denumire'];
		}
	}
	if (count($mytitle) > 0){
		$mytitletxt = ' - ' . implode(' - ', $mytitle) . ' - ';
	}
	ob_start();
	?>
	<title>Cauta programare online medic <?php echo $mytitletxt;?> MedList.ro</title>
	<meta name="description" content="Programari online doctor. Gaseste simplu si rapid cel mai convenabil doctor din orasul tau." />
	<meta name="keywords" content="programari online doctor" />
	<meta name="robots" content="index, follow" />	
	<?php 
		$ogimage = cs_url."/csapi/images/logo_640x246.png";
	?>
	<meta property="og:title" content="Doctori <?php echo $mytitletxt;?> MedList.ro" />
	<meta property="og:image" content="<?php echo $ogimage;?>" />
	<style>
		#cauta{
			padding-top: 10px;
			padding-bottom:10px;
			background-color: #60b0f4;
		}
		.listitem{
			border-bottom: 1px solid #acd4f6;
			border-radius: 12px;
			margin-top: 5px;
			margin-bottom: 5px;
		}
		.interval_item{
			display: inline-block;
			background-color: #e4fdc7;
			color: black;
			padding: 7px;
			border-radius: 14px;
			margin-bottom:3px;
			margin-left: 3px;
			min-width: 53px !important;
		}
		.interval_item:hover{
			text-decoration: none;
			background-color: chartreuse;
		}
		.interval_item.status_epuizat{
			background-color: lightgray;
			text-decoration: line-through;
			color: unset;
		}
		.cauta_imagespital{
			height:35px;
		}
		.cauta_imageaccount {
			height: 100px;
			width: 75px;
			border-radius: 5px;
		}
		.cauta_item_pic{
			width: 100px;
			min-height: 100px;
			float: left;
			text-align:center;
		}
		.cauta_item_info{
			width: calc(100% - 100px);
			width: -moz-calc(100% - 100px);
			width: -webkit-calc(100% - 100px);
			float: right;
			margin-top: 0;
		}
		@media all and (max-width: 768px) {

		}
		.cauta_side a{
			display:block;
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
<?php echo $cauta_form['resp']['html']?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="menu-left">
				<?php echo $cauta_side_ob['resp']['html']?>
			</div>
			<div class="detail-right" style="margin-top:5px">
				<h1 style="font-size:25px;padding:0;margin:0; font-weight: bold; text-align: center;">Lista doctori</h1>
			</div>
			<div class="detail-right" style="margin-top:5px">
				<?php 
				$pag_maxrows = 10;
				$p_page = 1;
				if (isset($_GET['p_page']) && (intval($_GET['p_page'])>0)){$p_page = intval($_GET['p_page']);}
				$cauta_list_params['page'] = $p_page;
				$cauta_list_params['rows'] = $pag_maxrows;
				$cauta_listhtmlwrapper = cs('cauta/listhtmlwrapper',$cauta_list_params);
				cscheck($cauta_listhtmlwrapper);
				if ($cauta_listhtmlwrapper['resp']['cauta_list']['resp']['records'] == 0){
					echo 'Nu au fost gasite rezultate...';
				}else{
					echo $cauta_listhtmlwrapper['resp']['html'];
				}
				?>
				<?php 
				//$pag_maxrows = 1;
				if ($cauta_listhtmlwrapper['resp']['cauta_list']['resp']['records'] > $pag_maxrows){?>
				<div class="row">
					<div class="col-xs-12 text-center">
						<nav aria-label="...">
							<ul class="pager">
								<li><a href="javascript:void(0)" onclick="pagination_button_click(-1)">Previous</a></li>
								<li><a href="javascript:void(0)" onclick="pagination_button_click(1)">Next</a></li>
							</ul>
						</nav>
						pagina <?php echo $cauta_listhtmlwrapper['resp']['cauta_list']['resp']['page']?> din <?php echo $cauta_listhtmlwrapper['resp']['cauta_list']['resp']['total']?>
					</div>
				</div>
				<script>
					pagination_button_click = function(next){
						var p_page = <?php echo $p_page;?>;
						var totalpages = <?php echo $cauta_listhtmlwrapper['resp']['cauta_list']['resp']['total'];?>;
						var nextpage = p_page + next;
						if ((nextpage > 0) && (nextpage <=totalpages) && (nextpage != p_page)){
							window.location.href = setURLParameter({name:'p_page',value:nextpage})
						}
						
					}
				</script>
				<?php }?>
			</div>
		</div>
	</div>
</div>
<?php 
function footer_ob(){
	global 
		$cauta_localitati_breadcrumb
		, $cauta_specializari_breadcrumb
		, $cauta_uridecode
		, $cauta_date
		, $cauta_localitate_id
		, $cauta_specializare_id
	;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<script>
		cauta_specializari_breadcrumb_parentid = 0;
		cauta_specializari_breadcrumb_parentparent = 0;
		cauta_localitati_breadcrumb_nodearr = <?php echo json_encode($cauta_localitati_breadcrumb['resp']['nodearr'])?>;
		cauta_specializari_breadcrumb_nodearr = <?php echo json_encode($cauta_specializari_breadcrumb['resp']['nodearr'])?>;
		cauta_localitate_id = <?php if ($cauta_localitate_id > 0){ echo $cauta_localitate_id; }else{echo 'null';}?>;
		cauta_specializare_id = <?php if ($cauta_specializare_id > 0){ echo $cauta_specializare_id; }else{echo 'null';}?>;

		finalizaezaprogramare = function(p_arr){
			console.log(p_arr)
			var form = document.createElement("form");
			form.style.display = "none";
			form.method = "POST";
			form.action = "/finalizareprogramare"; 
			$(form).append(Object.keys(p_arr).map(function(key, index) {
				return $('<input/>',{
					name:key,
					value:p_arr[key],
				})
			}))
			document.body.appendChild(form);
			form.submit();
		}
		programari = function(path,p_arr){
			console.log(p_arr)
			var form = document.createElement("form");
			form.style.display = "none";
			form.method = "POST";
			form.action = "<?php echo cs_url . '/doctori/' ?>" + path; 
			$(form).append(Object.keys(p_arr).map(function(key, index) {
				return $('<input/>',{
					name:key,
					value:p_arr[key],
				})
			}))
			document.body.appendChild(form);
			form.submit();
		}
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