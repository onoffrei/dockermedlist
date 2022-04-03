<?php
define('cs_debug',	true);
if (defined('cs_debug')){
	if (cs_debug){
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);		
	}
}
ini_set('max_execution_time',		0);
ini_set('default_charset',			'utf-8');
ini_set('date.timezone',			'Europe/Bucharest');
ini_set('safe_mode',				'off');

define('cs_path', 					realpath(dirname(__FILE__)));
define('cs_email',					'micutu@micutu.ro');
define('cs_controller_dir',			'controller');
define('cs_controller_uri',			'csapi');
define('cs_url_scheme',				(isset($_SERVER['HTTPS'])||isset($_SERVER['HTTP_X_FORWARDED_PROTO'])?'https':'http'));
define('cs_url_host', 				$_SERVER['HTTP_HOST']);//$_SERVER['HTTP_HOST']);
// ini_set('session.cookie_domain',	'micutu.ro'); //coment for localhost
define('cs_url_po',					cs_url_scheme . '://' . cs_url_host);
define('cs_url',					cs_url_scheme . '://' . cs_url_host);

define('cs_db_host', 				'db');
define('cs_db_user',				'user');
define('cs_db_password',			'test');
define('cs_db_name',				'programareonline');
define('cs_db_charset',				'utf8');
define('cs_updatescript','2018');
define('usersstatus_maxidle',10);
define('mek',10);
define('mev',10);

define('user_level_vizitator',	0);
define('user_level_pacient',	1);
define('user_level_doctor',		2);
define('user_level_manager',	3);
define('user_level_admin',		4);

require_once(realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_cs.php');
$GLOBALS['cs'] = new Cs();
function cs($controller = '',$params = array()){return $GLOBALS['cs']->controllerCall($controller, $params);}
function cscheck($p_arr = array()){
	if ($p_arr === null || (!isset($p_arr['success'])) || ($p_arr['success'] != true)){
		if (defined('cs_debug')){
			if (cs_debug){
				$debug_backtrace = debug_backtrace();
				foreach($debug_backtrace as $backtrace){
					echo "Error trapped on function " . $backtrace['function'] . "\r\n<br>";
					echo "with args " . json_encode($backtrace['args']) . "\r\n<br>";
					echo "on line " . $backtrace['line'] . "\r\n<br>";
					echo "in file " . $backtrace['file'] . "\r\n<br>";
					echo "-------------------------------------------------------" . "\r\n<br>";
				}
				echo "arg " . "\r\n<br>";
				var_dump($p_arr);
				echo "\r\n<br>" . "-------------------------------------------------------" . "\r\n<br>";
				echo "globals " . "\r\n<br>";
				var_dump($GLOBALS);
				echo "\r\n<br>" . "-------------------------------------------------------" . "\r\n<br>";
				?>
				<script>
					console.error('tested arg', <?php if ($p_arr === null){echo 'NULL';} else {echo json_encode($p_arr);};?>);
					<?php 
					if (defined('cs_debug') && cs_debug){ $new_GLOBALS_array = array(); foreach($GLOBALS as $gn => $gv){ if ($gn != 'GLOBALS') $new_GLOBALS_array[$gn] = $gv;}?>
					console.info('globals',<?php echo json_encode($new_GLOBALS_array);?>);
					<?php } ?>
				</script>
				<?php
			}else{
				cs('response/error',array('message'=>'Sorry, something failed'));
			}
		}
		exit;
	}
	return true;
}
?>