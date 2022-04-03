<?php
require_once("_cs_config.php");


$cauta_initparams = cs('cauta/initparams');
cscheck($cauta_initparams);
extract($cauta_initparams['resp']);
$cauta_form = cs('cauta/form',array('cauta_initparams'=>$cauta_initparams));
cscheck($cauta_form);

?><?php
function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari online medicale</title>
	<meta name="description" content="Programari online la doctor. Gaseste simplu si rapid cel mai bun doctor de langa tine! Programari la doctor. Programari la clinici private." />
	<meta name="keywords" content="MedList, Programari online la medic" />
	<meta name="robots" content="index, follow" />	
	<link href='https://fonts.googleapis.com/css?family=Fira Sans Condensed' rel='stylesheet'>
	<?php 
		$ogimage = cs_url."/csapi/images/logo_640x246.png";
	?>
	<meta property="og:title" content="MedList - Programari online la medic" />
	<meta property="og:image" content="<?php echo $ogimage;?>" />
	<style>
		#cauta button{
			color: white !important;
			background-color: #60b0f4 !important;
			border-color: #acd4f6 !important;
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
<div class="index_mainimage" style="height: 450px;">
	<div class="overlay">
		<div style="width:100%; text-align:center; margin-top:-80px">
			<h1 style="color:#ffffff; font-size: 34px; text-shadow: #000 2px 2px 15px;">Programari online la doctor</h1><h3> Gaseste simplu si rapid cel mai bun doctor de langa tine! </h3> <br />
			<?php echo $cauta_form['resp']['html']?>
		</div>
	</div>
	<img class="index_mainimage_img" src="/images/firstpage.jpg">
</div>

<!--3 step start-->

<section>
  <div class="container">
    <div class="row">
      <div class="col-lg-4 col-md-12">
        <div class="featured-step"> <span><h2>01</h2></span>
			<h5 class="mb-3" style="font-weight: bold;">Alegi doctorul preferat</h5>
          <div class="featured-desc">
            <p>Alegi doctorul preferat din lista si iti faci programarea simplu si rapid!<br /><br /></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-12 md-mt-3">
        <div class="featured-step"> <span><h2>02</h2></span>
          <h5 class="mb-3" style="font-weight: bold;">Te programezi simplu si rapid</h5>
          <div class="featured-desc">
            <p>Alegi clinica sau cabinetul din lista, apoi specialitatea si data la care vrei sa te programezi!<br /><br /></p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-12 md-mt-3">
        <div class="featured-step"> <span><h2>03</h2></span>
          <h5 class="mb-3" style="font-weight: bold;">Disponibil 24 x 7</h5>
          <div class="featured-desc">
            <p>Serviciul este disponibil 24 de ore din 24, 7 zile din 7, astfel te poti programa online la doctor oricand doresti, si nu te costa nimic - totul este gratis si la indemana ta!</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!--3 step end-->
<!--about us start-->
<section class="pt-0 pb-lg-0">
  <div class="container" style="text-align: right;">
    <div class="row align-items-center">
      <div class="col-lg-5 col-md-12">
        <img class="img-fluid" src="images/01.png" alt="">
      </div>
      <div class="col-lg-7 col-md-12 md-mt-5" style="padding-top: 30px;">
        <div class="section-title mb-2">
          <h6 style="text-transform: capitalize; font-size: 18px; ">Despre noi</h6>
          <h2 class="title">Bun venit la <span>MedList</span></h2>
        </div>
        <p class="text-black mb-3 lead font-w-5" style="text-align: justify; font-size: 18px;"><span style="color: #60b0f4; font-weight: bold;">MedList</span> este aplicaţia care te ajută în fiecare moment să ajungi la doctorul sau terapeutul tău facând foarte uşor procesul de programare la data şi ora pe care ţi-o doresti, indiferent de momentul in care vrei să realizezi acest lucru.</p>
		<p class="text-black mb-3 lead font-w-5" style="font-size: 18px;">
			Suntem alături de tine 24 de ore din 24, 7 zile pe săptamână!
		</p>
		<p class="text-black mb-3 lead font-w-5" style="font-size: 18px;">
			Nimic nu este mai simplu!
		</p>        
      </div>
    </div>
  </div>
</section>

<!--about us end-->
<?php
	$userisadmin = false;
	if (isset($_SESSION['cs_users_id']) && ($_SESSION['cs_users_id'] == 1)) $userisadmin = true;
?>
<!-- <?php if (!$userisadmin){?>
<div class="usersstatus minichat-start-button <?php 
		$admin_get = cs('users/get', array("filters"=>array("rules"=>array(
			array("field"=>"id","op"=>"eq","data"=>1)
		))));
		if ($admin_get == null) {$is_success = false;}
		$adminstatus = 'offline';
		if ((strtotime(date('Y-m-d H:i:s')) - strtotime($admin_get['date'])) > usersstatus_maxidle){
			$adminstatus = 'offline';
		}else{
			$adminstatus = 'online';
		}
		if (intval($admin_get['status']) == 0){$adminstatus = 'offline';}
		echo $adminstatus;
	?> " users="1">
	<div class="usersstatus_custom_online" >
		<button class="btn btn-success btn-lg" type="button" onclick='<?php 
			$payload = array();
			$mek = ($minichat['mek']=='browser'?$minichat['mek']:'to');
			$payload[$mek] = intval($minichat['mev']);
			$payload['from'] = 1;
			if ($payload[$mek] != $payload['from']){
				echo 'minichat_show(' . json_encode($payload) . ')';														
			}else{
				echo 'javascript:void(0)';
			}
			?>'><i class="fa fa-comment-o" aria-hidden="true"></i> chat online <span class="minichat_count"></span></button>
	</div>
</div>
<?php }?> -->
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