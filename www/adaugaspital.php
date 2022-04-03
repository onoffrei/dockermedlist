<?php
require_once("_cs_config.php");
$is_success = true;

?>
<?php 
function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>Listeaza-ti clinica/cabinetul - MedList</title>
	<meta name="robots" content="index, follow" />	
	<style>
		div.autocomplete {
		  /*the container must be positioned relative:*/
		  position: relative;
		  display: inline-block;
		}
		div.autocomplete input {
		  border: 1px solid transparent;
		  background-color: #f1f1f1;
		  padding: 10px;
		  font-size: 16px;
		}
		div.autocomplete input[type=text] {
		  background-color: #f1f1f1;
		  width: 100%;
		}
		div.autocomplete input[type=submit] {
		  background-color: DodgerBlue;
		  color: #fff;
		  cursor: pointer;
		}
		.autocomplete-items {
		  position: absolute;
		  border: 1px solid #d4d4d4;
		  border-bottom: none;
		  border-top: none;
		  z-index: 99;
		  /*position the autocomplete items to be the same width as the container:*/
		  top: 100%;
		  left: 0;
		  right: 0;
		  overflow-y: scroll;
		  max-height:300px
		}
		.autocomplete-items div {
		  padding: 10px;
		  cursor: pointer;
		  background-color: #fff; 
		  border-bottom: 1px solid #d4d4d4; 
		}
		.autocomplete-items div:hover {
		  /*when hovering an item:*/
		  background-color: #e9e9e9; 
		}
		.autocomplete-active {
		  /*when navigating through the items using the arrow keys:*/
		  background-color: DodgerBlue !important; 
		  color: #ffffff; 
		}

	</style>
	<link href="<?php echo cs_url;?>/css/bootstrapValidator.min.css" type="text/css" rel="stylesheet"/>
	<link href="<?php echo cs_url;?>/js/quill/quill.snow.css" type="text/css" rel="stylesheet"/>
	<link href="<?php echo cs_url;?>/js/quill/quill.core.css" type="text/css" rel="stylesheet"/>
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
} 
$GLOBALS['header_ob'] = header_ob();
require_once(cs_path . DIRECTORY_SEPARATOR . 'header.php' );
?>
<?php if ($is_success){?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="panel panel-info" style="padding:0; margin-top:5px">
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-plus" aria-hidden="true"></i> Inregistreaza spitalul tau pe care il manageriezi</h3></div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<form class="form-horizontal" action="javascript:spital_submit(this)" id="spital_form" onsubmit="javascript:void(0)" data-toggle="postValidator" role="form" method="POST">
								<div class="form-group has-feedback">
									<label class="col-sm-4 control-label">Denumire unitate medicala</label>
									<div class="col-sm-8">
										<input type="text" class="form-control" name="nume" placeholder="Denumire unitate medicala" required autocomplete="off">
									</div>
								</div>
								<div class="autocomplete form-group has-feedback" style="display:block">
									<label class="col-sm-4 control-label">Localitate</label>
									<div class="col-sm-8" style="">
										<input type="text" class="form-control" name="localitate_name" placeholder="localitate" value="" autocomplete="off">
										<input type="hidden" class="form-control" name="localitate" value="">
										<span name="localitate_span" style="position:absolute;"></span>
									</div>
								</div>
								<div class="form-group has-feedback">
									<label class="col-sm-4 control-label">Adresa</label>
									<div class="col-sm-8">
										<input type="text" class="form-control" name="adresa" placeholder="adresa" required autocomplete="off">
									</div>
								</div>
								<div class="form-group has-feedback">
									<label class="col-sm-4 control-label">Telefon</label>
									<div class="col-sm-8">
										<input class="form-control" type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="telefon" placeholder="telefon" required autocomplete="off">
									</div>
								</div>
								<div class="form-group has-feedback">
									<label class="col-sm-4 control-label">Descriere</label>
									<div class="col-sm-8">
										<div id="mytextarea"></div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label"></label>
									<div class="col-sm-8">
										<button type="submit" class="btn btn-success spital_submit_button">Adaugare</button>
									</div>
								</div>
							</form>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php }else{?>
oupssss.. 
<?php }?>
<?php 
function footer_ob(){
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<script>
	</script>
	<script src="<?php echo cs_url;?>/js/adaugaspital.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script src="<?php echo cs_url;?>/js/autocomplete.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script src="<?php echo cs_url;?>/js/bootstrapValidator.min.js"></script>
	<script src="<?php echo cs_url;?>/js/quill/quill.min.js"></script>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>
