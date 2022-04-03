<?php
function response_success($p_arr = array()){
	if (isset($p_arr['message'])) {
		$_SESSION['cs_response_message'] = $p_arr['message'];
	}else{
		unset($_SESSION['cs_response_message']);
	}
	header("Location: " . cs_url . "/csapi/response/success_html");
	exit;
}
function response_success_html($p_arr = array()){
	$path = cs_path;
	require_once($path . DIRECTORY_SEPARATOR . 'header.php' ); 
?>
<span style="font-size: xx-large;color: greenyellow;" class="glyphicon glyphicon-ok" aria-hidden="true"></span>
<?php
	if (isset($_SESSION['cs_response_message'])) echo $_SESSION['cs_response_message'];
	require_once($path . DIRECTORY_SEPARATOR . 'footer.php' );
}
function response_error($p_arr = array()){
	if (isset($p_arr['message'])) {
		$_SESSION['cs_response_message'] = $p_arr['message'];
	}else{
		unset($_SESSION['cs_response_message']);
	}
	header("Location: " . cs_url . "/csapi/response/error_html");
	exit;
}
function response_error_html($p_arr = array()){
	$path = cs_path;
	require_once($path . DIRECTORY_SEPARATOR . 'header.php' );
	if (isset($_SESSION['cs_response_message'])) echo $_SESSION['cs_response_message'];
	require_once($path . DIRECTORY_SEPARATOR . 'footer.php' );
}
?>