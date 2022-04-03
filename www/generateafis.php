<?php
require_once("_cs_config.php");


$id = isset($_POST['id']) ? $_POST['id'] : '';
$nume = isset($_POST['nume']) ? $_POST['nume'] : '';
$logo = isset($_POST['logo']) ? $_POST['logo'] : '';
$url = isset($_POST['url']) ? $_POST['url'] : '';

$img = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=".$url."&choe=UTF-8";

if (!isset($_SESSION['cs_users_id'])){
	header("Location: " . cs_url_scheme . '://' . cs_url_host 
		. '/csapi/users/login_html' 
		. '?urlnext=' . urldecode(cs_url_scheme . '://' . cs_url_host . $_SERVER['REQUEST_URI'])
	);
	exit;
}

$spitale_users_spitalactivinput = cs('spitale_users/spitalactivinput');
cscheck($spitale_users_spitalactivinput);
$spital_activ = $spitale_users_spitalactivinput['spitale_users_spitalactivget']['resp'];

$spitale_users_getlevel = cs('spitale_users/getlevel',array('spital'=>$id));
cscheck($spitale_users_getlevel);

if ($spitale_users_getlevel['resp'] < user_level_manager) { echo 'you are not autorized to this level..'; exit; }

$menu_get = cs('menu/get',array('level'=>$spitale_users_getlevel['resp']));
cscheck($menu_get);

$images_get = cs('images/get', array("filters"=>array("rules"=>array(
	array("field"=>"owner","op"=>"eq","data"=>$id)
))));
//cscheck($images_get);



// Include the main TCPDF library (search for installation path).
require_once('tcpdf/tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('MedList.Ro');
$pdf->SetTitle('Afis MedList.Ro');
$pdf->SetSubject('Afis MedList.Ro');
$pdf->SetKeywords('Afis, MedList.Ro');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('times', 'BI', 20);

// add a page
$pdf->AddPage();

$html = '
<style>
.titlu{
	text-align: center;	
	font-size: 200%;
}
.align{
	text-align: center;	
}
.subtitlu{
	text-align: center;	
	padding: 0;
}
</style>
<div style="border: 1px solid #ccc;">
<!--<div class="align">'.$logo.'</div>-->
<div class="titlu ">'.$nume.'</div>
<div class="subtitlu h3">foloseste pentru programari</div>
<div class="subtitlu"><img src="img/logo4.png" style="max-width: 80%; max-height: 80%; width:450px;" /></div>
<div class="subtitlu h3">Programeaza-te rapid si sigur!</div>
<div class="subtitlu h3">Scaneaza codul de mai jos!</div>
<div class="subtitlu"><img src="'.$img.'" title="" /><br />'.$url.'</div>
</div>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// reset pointer to the last page
$pdf->lastPage();

//Close and output PDF document
$pdf->Output('afismedlistro.pdf', 'I');
//============================================================+
// END OF FILE
//============================================================+

?>