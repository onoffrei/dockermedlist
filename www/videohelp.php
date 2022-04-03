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

?>
<?php 
function header_ob(){ 
	global $is_success, $doctor_get;
	$ret = array('success'=>false, 'resp'=>array());
	ob_start();
	if ($is_success){
	?>
	<title>Video Help - MedList.ro</title>
	<meta name="robots" content="index, follow" />	
	<meta name="description" content="Video Help" />
	<meta name="keywords" content="Video Help" />
	<meta name="robots" content="index, follow" />	
	<?php }?>
	<style>
		#cauta{
			padding-top: 10px;
			padding-bottom:10px;
			background-color: #60b0f4;
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
	<input type="button" onclick="mysearch()" value="search">
	<input type="button" onclick="mysearch1()" value="list">
	<div class="row">
		<div class="col-xs-12 col-sm-8 col-md-8 col-lg-9">
			<div id="results">foo</div>
		</div>
		<div class="col-xs-12 col-sm-4 col-md-4 col-lg-3">
		bar
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
	<script>
		cauta_specializari_breadcrumb_parentid = 0;
		cauta_specializari_breadcrumb_parentparent = 0;
		cauta_localitati_breadcrumb_nodearr = <?php echo json_encode($cauta_localitati_breadcrumb['resp']['nodearr'])?>;
		cauta_specializari_breadcrumb_nodearr = <?php echo json_encode($cauta_specializari_breadcrumb['resp']['nodearr'])?>;
		cauta_localitate_id = <?php if ($cauta_localitate_id > 0){ echo $cauta_localitate_id; }else{echo 'null';}?>;
		cauta_specializare_id = <?php if ($cauta_specializare_id > 0){ echo $cauta_specializare_id; }else{echo 'null';}?>;
		
		$(window).on("resize", resetVideoHeight);
		function tplawesome(e,t){res=e;for(var n=0;n<t.length;n++){res=res.replace(/\{\{(.*?)\}\}/g,function(e,r){return t[n][r]})}return res}
		function yinit() {
			gapi.client.setApiKey("AIzaSyCey8YBK7ktEa6IMCmNNZ2RNKalem7DfuY");
			gapi.client.load("youtube", "v3", function() {
			// yt api is ready
			});
		}
		function resetVideoHeight() {
			//$(".video").css("height", $("#results").width() * 9/16);
			$(".video").css("width", $("#results").width());
		}
		function mysearch(){
		   var request = gapi.client.youtube.search.list({
				part: "snippet",
				type: "video",
				q: encodeURIComponent("medlist.ro").replace(/%20/g, "+"),
				maxResults: 3,
				order: "viewCount",
				publishedAfter: "2018-01-01T00:00:00Z"
		   }); 
		   // execute the request
		   request.execute(function(response) {
			  console.log(response)
			  var results = response.result;
			  $("#results").html("");
			  $.each(results.items, function(index, item) {
				$.get("tpl/item.html", function(data) {
					$("#results").append(tplawesome(data, [{"title":item.snippet.title, "videoid":item.id.videoId}]));
				});
			  });
			  resetVideoHeight();
		   });
			
		}
	</script>
	<script src="<?php echo cs_url;?>/js/cauta.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script src="https://apis.google.com/js/client.js?onload=yinit"></script>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>