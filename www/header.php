<?php if (!isset($_SESSION['browser_browser']) || ($_SESSION['browser_browser'] == '')){
//header("Location: " . cs_url_scheme . '://' . cs_url_host . '/' );
//exit; 
?>
<script>
	// window.location.reload();
</script>
<?php }?>
<?php
$GLOBALS['minichat'] = array();
if (isset($_SESSION['cs_users_id']) && isset($_SESSION['browser_browser'])){ 
	$GLOBALS['minichat']['mek'] = 'from';
	$GLOBALS['minichat']['mev'] = $_SESSION['cs_users_id'];
}else{
	// $GLOBALS['minichat']['mek'] = 'browser';
	// $GLOBALS['minichat']['mev'] = $_SESSION['browser_id'];
}
if (isset($_SESSION['cs_users_id'])){
	$users_get = cs('users/get', array("filters"=>array("rules"=>array(
		array("field"=>"id","op"=>"eq","data"=>$_SESSION['cs_users_id'])
	))));
	cscheck(array('success'=>$users_get!=null));

	$spitale_users_spitalactivinput = cs('spitale_users/spitalactivinput');
	cscheck($spitale_users_spitalactivinput);
	$spital_activ = $spitale_users_spitalactivinput['spitale_users_spitalactivget']['resp'];

	$spitale_users_getlevel = cs('spitale_users/getlevel',array('spital'=>$spital_activ['id']));
	cscheck($spitale_users_getlevel);
	$GLOBALS['spitale_users_getlevel'] = $spitale_users_getlevel;

	if ($spitale_users_getlevel['resp'] < user_level_pacient) { echo 'you are not autorized to this level..'; exit; }

	$menu_get = cs('menu/get',array('level'=>$spitale_users_getlevel['resp']));
	cscheck($menu_get);
}
$GLOBALS['mobile_ismobile'] = cs('mobile/ismobile');
?><!DOCTYPE html>
<html lang="ro-RO">
    <head>
		<?php if ($GLOBALS['mobile_ismobile']){?>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="mobile-web-app-capable" content="yes">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link href="<?php echo cs_url;?>/css/style.css?timestamp=<?php echo cs_updatescript;?>" type="text/css" rel="stylesheet"/>
        <link href="<?php echo cs_url;?>/css/stylemobile.css?timestamp=<?php echo cs_updatescript;?>" type="text/css" rel="stylesheet"/>
		<?php }else{?>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="<?php echo cs_url;?>/css/style.css?timestamp=<?php echo cs_updatescript;?>" type="text/css" rel="stylesheet"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<?php }?>
		<link rel="shortcut icon" href="<?php echo cs_url; ?>/icon2.ico" />
		<link href="<?php echo cs_url;?>/css/jquery-ui.css" type="text/css" rel="stylesheet"/>
		<link href="<?php echo cs_url;?>/css/bootstrap.min.css" type="text/css" rel="stylesheet"/>
		<link href="<?php echo cs_url;?>/css/font-awesome-4.7.0/css/font-awesome.css" type="text/css" rel="stylesheet"/>
		
	
		
		
		<?php if (isset($GLOBALS['header_ob'])) {
			cscheck($GLOBALS['header_ob']);
			echo $GLOBALS['header_ob']['resp']['html'];
		}?>
		<link href='https://fonts.googleapis.com/css?family=Fira Sans Condensed' rel='stylesheet'>
	</head>
	<body>
		<nav class="navbar navbar-default" id="navbar_container">
			 <div class="container-fluid">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<ul class="nav navbar-nav  navbar-right visible-xs extra-icon">
						<?php if (isset($_SESSION['cs_users_id'])){?>
						<li class="topbar_mesaje" style="">
							<a class="" href="/mesaje">
								<i class="fa fa-envelope-o topbar_mesaje_empty" aria-hidden="true"></i>
								<i class="fa fa-envelope-o topbar_mesaje_new" aria-hidden="true" style="color:red"><span class="badge" style="margin-left: -12px;margin-top: -16px;">*</span></i>
							</a>
						</li>
						<?php }?>
						<?php if (isset($GLOBALS['spitale_users_getlevel']) &&($GLOBALS['spitale_users_getlevel']['resp']>=user_level_doctor)){?>
						<li class="topbar_pacienti" style="">
							<a class="" href="/programdoc">
								<i class="fa fa-newspaper-o topbar_empty" aria-hidden="true"></i>
								<i class="fa fa-newspaper-o topbar_new" aria-hidden="true" style="color:red"><span class="badge" style="margin-left: -12px;margin-top: -16px;">*</span></i>
							</a>
						</li>
						<?php }?>
					</ul>
					<a class="navbar-brand" href="/" ><img src="/img/logo4.png" style="height:20px !important;" /></a>
				</div>
				<div class="collapse navbar-collapse" id="navbar" style="height:30px;">
					<ul class="nav navbar-nav  navbar-right">
						<li><a href="/adaugaspital" ><i class="fa fa-plus" aria-hidden="true"></i><span class="hidden-sm"> Adaugă unitate medicala</span></a></li> 
						<?php if (isset($_SESSION['cs_users_id'])){?>
						<li class="topbar_mesaje hidden-xs" style="">
							<a href="/mesaje" >
								<i class="fa fa-envelope-o topbar_mesaje_empty" aria-hidden="true"></i>
								<i class="fa fa-envelope-o topbar_mesaje_new" aria-hidden="true" style="color:red"><span class="badge" style="margin-left: -12px;margin-top: -16px;font-size: 7px;background-color: red;">*</span></i>
								<span class="hidden-sm"> Mesaje</span>
							</a>
						</li> 
						<?php }?>
						<?php if (isset($GLOBALS['spitale_users_getlevel']) &&($GLOBALS['spitale_users_getlevel']['resp']>=user_level_doctor)){?>
						<li class="topbar_pacienti hidden-xs" style="">
							<a href="/programdoc" >
								<i class="fa fa-newspaper-o topbar_empty" aria-hidden="true"></i>
								<i class="fa fa-newspaper-o topbar_new" aria-hidden="true" style="color:red"><span class="badge" style="margin-left: -12px;margin-top: -16px;font-size: 7px;background-color: red;">*</span></i>
								<span class="hidden-sm"> Pacienti</span>
							</a>
						</li> 
						<?php }?>
						<?php if(!isset($_SESSION['cs_users_id'])){?>
						<li><a href="javascript:void(0)" onclick="cs('users/login_rs',{})"><i class="fa fa-user" aria-hidden="true"></i> Intră in cont</a></li> 
						<?php }else{?>
						<li class="dropdown activedropdown hidden-xs">
							<a href="javascript:void(0)" class="dropdown-toggle"  role="button" aria-expanded="false">
								<img src="<?php 
									if ($users_get['image'] > 0){
										echo cs_url."/csapi/images/view/?thumb=0&id=" . $users_get['image'];
									}else{
										echo cs_url."/images/default_avatar.jpg";
									}
								?>" class="navbar_avatar" alt="User Image">
								<?php echo $users_get['nume']?> 
							</a>
							<ul class="dropdown-menu">
								<?php foreach($menu_get['resp']['menu_items'] as $menu_item){?>
									<li><a href="<?php echo $menu_item['href']?>" onclick="<?php echo $menu_item['onclick']?>"><?php echo $menu_item['icon']?> <?php echo $menu_item['text']?></a></li>
								<?php }?>
							</ul>
						</li>
						<li class="hidden-xs">
							<a href="javascript:void(0)" class="navbar-link" onclick="cs('users/logout_rs',{callback:'window.location.reload'})">
								<i class="fa fa-sign-out" aria-hidden="true"></i>
							</a>
						</li>
						<?php if (($GLOBALS['mobile_ismobile'] == true) && isset($GLOBALS['spitale_users_spitalactivinput'])){?>
						<li class="visible-xs" style="display:none" spitalealege="">
							<?php echo $GLOBALS['spitale_users_spitalactivinput']['resp']['html'];?>
						</li>
						<?php }?>
						<?php foreach($menu_get['resp']['menu_items'] as $menu_item){?>
							<li class="visible-xs"><a href="<?php 
								$href = $menu_item['href'];
								if ($_SERVER['QUERY_STRING'] != '') {
									$href .= '?' . $_SERVER['QUERY_STRING'];
								}
								echo $href;
								?>" onclick="<?php echo $menu_item['onclick']?>"><?php echo $menu_item['icon']?> <?php echo $menu_item['text']?></a></li>
						<?php }?>
						<li class="visible-xs">
							<a href="javascript:void(0)" class="navbar-link active" onclick="cs('users/logout_rs',{callback:'window.location.reload'})">
								<i class="fa fa-sign-out" aria-hidden="true"></i> Deconectare
							</a>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</nav>
		