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
			<h1 style="color:#ffffff; font-size: 44px; text-shadow: #000 2px 2px 15px;">Programari online la doctor</h1><h3> Gaseste simplu si rapid cel mai bun doctor de langa tine! </h3> <br />
			<?php echo $cauta_form['resp']['html']?>
		</div>
	</div>
	<img class="index_mainimage_img" src="/images/firstpage.jpg">
</div>

<br />
<div class="row" style="margin: 15px;">
    <div class="col-md-6 col-md-push-6">
        <h3>De ce pacient pe MedList?</h3>
		<div style="text-align: justify;">
		&nbsp;&nbsp;Cu siguranță te-ai întâlnit cu situația în care ai nevoie de un medic sau terapeut și nu știai unde sa-l găsești, ai întrebat prietenii, vecinii, cunoștințe și tot nu ai fost lămurit de răspunsul primit.<br /><br />
		&nbsp;&nbsp;Acum noi venim cu soluția cu ajutorul MedList poți face o programare online la orice ora, pentru orice data la care iți dorești să ai o întâlnire cu un anumit medic sau terapeut.<br /><br />
		&nbsp;&nbsp;Tot ce trebuie sa faci este sa selectezi din meniul de mai sus localitatea, specialitatea dorita, precum și data când vrei sa ai programarea online și iți va apărea o lista cu tot personalul disponibil din categoria aleasa. <br /><br />
		&nbsp;&nbsp;<b>Simplu și eficient!</b><br /><br />
		&nbsp;&nbsp;În plus <b>NU TREBUIE</b> să instalezi nicio aplicație, totul se poate realiza doar cu o conexiune la internet și un browser web (recomandam Chrome), și se poate utiliza orice dispozitiv fix (desktop) sau mobil (telefon, tableta).<br /><br />
		În plus, prin intermediul <i>chat-ului platformei</i>, poți contacta oricând medicul sau terapeutul tău.<br /><br />
		<a href="/doctori" ><i class="fa fa-plus" aria-hidden="true"></i>  Caută medicul / terapeutul tău</a><br /><br />
		</div>
	</div>
<!---->	<div class="col-md-6 col-md-pull-6">
		<img src="/images/medlist1.jpg" style="width:95%; height: 95%;">
	  </div>
</div>
<div class="row" style="margin: 15px;">	
	<div class="col-md-6 col-md-push-6">
		<h3>De ce unitate medicală pe MedList?</h3>
		<div style="text-align: justify;">
		&nbsp;&nbsp;In ceea ce privește unitățile medicale, soluția oferita de noi este simpla și eficienta deoarece puteți vedea toate programările online efectuate de către pacienți, puteți lua legătura cu ei (telefonic, email sau chiar prin chatul pus la dispoziție de platforma noastră), va puteți manageria programul de lucru si mult mai multe facilitați pe care le aveți în contul dumneavoastră special creat.<br /><br />Si mai mult! <br />Aveam si cele mai bune tarife, incepand de la 0,43 euro!<br /><br />
		<a href="/adaugaspital" ><i class="fa fa-plus" aria-hidden="true"></i>  Adaugă unitate medicală</a>
		</div>		
	 </div>
	 <div class="col-md-6 col-md-pull-6">
		<img src="/images/medlist2.jpg" style="width:95%; height: 95%;">
	 </div>
</div><br />
<?php
	$userisadmin = false;
	if (isset($_SESSION['cs_users_id']) && ($_SESSION['cs_users_id'] == 1)) $userisadmin = true;
?>
<?php if (!$userisadmin){?>
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
			?>'><i class="fa fa-comment-o" aria-hidden="true"></i> admin <span class="minichat_count"></span></button>
	</div>
</div>
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