<?php
class Cs{
	public function __construct($p = array()){
		$this->init_cookie_session_firebase();
		//unset($_COOKIE['ping']);
		//setcookie('ping', null, -1, '/',ini_get('session.cookie_domain'));
	}
	public function init_cookie_session_firebase(){
		// return
		if (!isset($_SESSION)) { session_start(); }
		if (isset($_SESSION['cs_users_date']) && defined('usersstatus_maxidle')){
			if ((strtotime(date('Y-m-d H:i:s')) - strtotime($_SESSION['cs_users_date'])) > (usersstatus_maxidle - 20)){
				require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_grid.php');
				require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  'users.php');
				_cs_grid_update(array(
					'oper'=>'edit',
					'id'=>$_SESSION['cs_users_id'],
					'db_table'=>'users',
					'db_cols'=> $GLOBALS['user_cols'],
					'date'=>date('Y-m-d H:i:s'),
					'status'=>1,
				));
				$_SESSION['cs_users_date'] = date('Y-m-d H:i:s');
			}
		}
		if (!isset($_COOKIE['browserid'])) {
			$browserid = uniqid() .'.'. session_id();
			$firebaseid = '';
			setcookie(
				"browserid", 
				$browserid,
				time()+155520000,/*5 year*/
				'/',
				ini_get('session.cookie_domain')
			);
		}else{
			require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_grid.php');
			require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  'browser.php');
			if (!isset($_SESSION['browser_id'])){
				$browser_get = _cs_grid_get(array(
					'db_table'=>'browser',
					'db_cols'=> $GLOBALS['browser_cols'],
					'filters'=>array("groupOp"=>"AND","rules"=>array(
						array("field"=>"browser","op"=>"eq","data"=>$_COOKIE['browserid'])
					))
				));
				//$_SESSION['browser_get'] = json_encode($browser_get);
				if (isset($browser_get['success']) && $browser_get['success'] && ($browser_get['resp']['records'] > 0)){
					$_SESSION['browser_id'] = $browser_get['resp']['rows'][0]->id;
					$_SESSION['browser_date'] = $browser_get['resp']['rows'][0]->date;
					$_SESSION['browser_browser'] = $browser_get['resp']['rows'][0]->browser;
					$_SESSION['firebase_browserid'] = $browser_get['resp']['rows'][0]->firebase;
					$_SESSION['firebase_path'] = 'browser';
					$_SESSION['firebase_activeid'] = $browser_get['resp']['rows'][0]->firebase;
				}else{
					if (defined('cs_firebase_db_name')) {
						require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  'firebase.php');
						$firebase_register = firebase_register(array(
							'path'=>'browser',
						));
						//$_SESSION['firebase_register'] = json_encode($firebase_register);
						if (isset($firebase_register['success']) && ($firebase_register['success'] == true)){
							$firebaseid = $firebase_register['resp']['name'];
							$_SESSION['firebase_browserid'] = $firebaseid;
							$_SESSION['firebase_path'] = 'browser';
							$_SESSION['firebase_activeid'] = $firebaseid;
						}
					}
					require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  '_cs_grid.php');
					require_once(cs_path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  'browser.php');
					$_cs_grid_update_param = array(
						'oper'=>'add',
						'id'=>'null',
						'db_table'=>'browser',
						'db_cols'=> $GLOBALS['browser_cols'],
						'browser'=>$_COOKIE['browserid'],
						'firebase'=>$firebaseid,
						'date'=>date('Y-m-d H:i:s'),
					);
					//$_SESSION['_cs_grid_update_param'] = json_encode($_cs_grid_update_param);
					$_cs_grid_update = _cs_grid_update($_cs_grid_update_param);
					if (isset($_cs_grid_update['success']) && ($_cs_grid_update['success'] == true)){
						$_SESSION['browser_browser'] = $_COOKIE['browserid'];
						$_SESSION['browser_id'] = $_cs_grid_update['resp']['id'];
						$_SESSION['browser_date'] = date('Y-m-d H:i:s');
					}
					return;
				}
			}
			if (date('Y-m-d',strtotime($_SESSION['browser_date'])) != date('Y-m-d')){
				_cs_grid_update(array(
					'oper'=>'edit',
					'id'=>$_SESSION['browser_id'],
					'db_table'=>'browser',
					'db_cols'=> $GLOBALS['browser_cols'],
					'date'=>date('Y-m-d H:i:s'),
				));
			}
		}
	}
	public function controllerCall($p_controller = '',$p_params = array()){
		if (isset($p_params['then'])){
			$thencontroller = '';
			$thenparams = array();
			if (isset($p_params['then'][0])) $thencontroller = $p_params['then'][0];
			if (isset($p_params['then'][1])) $thenparams = $p_params['then'][1];
			unset($p_params['then']);
			$ret = $this->controllerCall($p_controller, $p_params);
			$ret['then'] = $this->controllerCall($thencontroller,$thenparams);
			return $ret;
		}
		if ($p_controller == '') return array('error'=>'api empty query');
		$syntaxanalyse = $this->syntaxanalyse($p_controller, $p_params);
		return $this->_controllerCall($syntaxanalyse);
	}
	private function syntaxanalyse($p_str, $p_arr){
		$ret = array('controller'=>array('path'=>array(),'lib'=>'','method'=>''),'param'=>array());
		$path = parse_url($p_str, PHP_URL_PATH);
		$query = parse_url($p_str, PHP_URL_QUERY);
		$path = preg_match_all("/([^\/]+)/",$path,$matches);
		if (count($matches)>1){
			$pi_count = count($matches[1]);
			if ($pi_count > 1){
				$ret['controller']['method'] = $matches[1][$pi_count-1];
				$ret['controller']['lib'] = $matches[1][$pi_count-2];
			}
			if ($pi_count > 2 ){
				for ($pi = 0; $pi < ($pi_count - 2); $pi++ ){
					$ret['controller']['path'][] = $matches[1][$pi];
				}
			}
		}
		if (!is_null($query)){ parse_str($query,$ret['param']);}
		if (count($p_arr)>0){ foreach ($p_arr as $k => $v){$ret['param'][$k] = $v;}}
		return $ret;
	}
	private function _controllerCall($p_arr){
		if ((!isset($p_arr['controller']))||(!isset($p_arr['controller']['method']))||($p_arr['controller']['method']=='')){
			return array('success'=>false,'error'=>'api _methodcall empty method');
		}
		if ((!isset($p_arr['controller']['lib']))||($p_arr['controller']['lib']=='')){
			return array('success'=>false,'error'=>'api _methodcall empty lib');
		}
		$path = cs_path;
		if ((isset($p_arr['controller']['path']))&&(count($p_arr['controller']['path'])>0)){
			foreach($p_arr['controller']['path'] as $pi){
				$path .= DIRECTORY_SEPARATOR . $pi;
			}
		}
		if (!file_exists($path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR .  $p_arr['controller']['lib'] . '.php')){ 
			return array('success'=>false,'error'=>'api lib not found: ' . $path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR . $p_arr['controller']['lib'] . '.php');
		}
		require_once($path . DIRECTORY_SEPARATOR . cs_controller_dir . DIRECTORY_SEPARATOR . $p_arr['controller']['lib'] . '.php');
		$method = $p_arr['controller']['lib'] . '_' . $p_arr['controller']['method'];
		if (!function_exists($method)){
			return array('success'=>false,'error'=>'api method not found: ' . $method);
		}
		$param = array(); if (isset($p_arr['param'])){$param = $p_arr['param'];}
		return $method($param);
	}
	public function	httpApi($p = null){
		$requestUri = substr($_SERVER["REQUEST_URI"],strlen(cs_controller_uri) + 2); // "/api/"
		$params = array();
		foreach($_REQUEST as $rqn=>$rqv){
			$params[$rqn] = $rqv;
		}
		if (isset($_SERVER['CONTENT_TYPE'])) {
			if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/json') !== false) {
				$input = file_get_contents("php://input");
				if (substr($input,0,1) === '{'){
					$input = json_decode($input, true);
					foreach($input as $in=>$iv){
						$params[$in] = $iv;
					}
				}
			}
		}
		header("Expires: Mon, 17 Jul 1989 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		try{
			if (isset($_SERVER['REQUEST_METHOD']) && (in_array(strtolower($_SERVER['REQUEST_METHOD']),['get','post']))){
				$resp = $this->controllerCall($requestUri,$params);				
			}else{
				$resp = null;
			}
		}catch (Exception $e) {
		//}catch (\Throwable | \Warrning | \Error | \Exception $e) {
			header('Content-Type: application/json; charset=utf-8');
			if (defined('cs_debug') && cs_debug){
				echo json_encode(array('success'=>false,'error'=>$e->getMessage(),'dbg'=>array(
					'getMessage'=>$e->getMessage(),
					'getFile'=>$e->getFile(),
					'getLine'=>$e->getLine(),
					'getCode'=>$e->getCode(),
					'getTrace'=>$e->getTrace(),
				)));
			}else{
				echo json_encode(array('success'=>false));
			}
			exit;
			//echo '{'success'=>false,"error":"Caught exception: ',  $e->getMessage(), '}"';
		}
		switch(gettype($resp)){
			case 'string':
				header('Content-Type: text/html; charset=utf-8');
				echo $resp;
			break;
			case 'array':
				header('Content-Type: application/json; charset=utf-8');
				if (defined('cs_debug') && cs_debug){
					echo json_encode($resp);
				}else{
					$stripedresp = array();
					if (isset($resp['success'])) $stripedresp['success'] = $resp['success'];
					if (isset($resp['error'])) $stripedresp['error'] = $resp['error'];
					if (isset($resp['resp'])) $stripedresp['resp'] = $resp['resp'];
					if (isset($resp['then'])) $stripedresp['then'] = $resp['then'];
					if (isset($resp['resptype'])) $stripedresp['resptype'] = $resp['resptype'];
					echo json_encode($stripedresp);
				}
				
			break;
		}
	}
}
if (realpath($_SERVER["SCRIPT_FILENAME"]) === realpath(__FILE__)){
	require_once('_cs_config.php');
	$GLOBALS['cs']->httpApi();
}
?>