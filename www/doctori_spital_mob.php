<?php 
function header_ob(){ 
	global $spitale_get, $manager;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title><?php echo htmlspecialchars($spitale_get['nume']);?> - MedList.ro</title>
	<meta name="description" content="<?php echo substr(htmlspecialchars(strip_tags($spitale_get['descriere'])),0,100) . ' - ' . substr(htmlspecialchars($spitale_get['adresa']),0,30) ;?> - MedList.ro" />
	<meta name="keywords" content="<?php echo htmlspecialchars($spitale_get['nume']);?> - MedList.ro" />
	<meta name="robots" content="index, follow" />	
	<?php 
		$ogimage = cs_url."/images/logo_640x246.png";
		if ($spitale_get['logo'] > 0){
			$ogimage = cs_url."/csapi/images/view/?thumb=0&id=" . $spitale_get['logo'];
		}else if ($manager['image'] > 0){
			$ogimage = cs_url."/csapi/images/view/?thumb=0&id=" . $manager['image'];
		}
	?>
	<meta property="og:title" content="<?php echo htmlspecialchars($spitale_get['nume']);?> - MedList.ro" />
	<meta property="og:image" content="<?php echo $ogimage;?>" />
	<style>
		.specializari_breadcrumb {
			cursor: default;
			text-align: left;
		}
		#cauta .specializari_breadcrumb .breadcrumb{
			margin-bottom: 0px!important;
			border: 1px solid #ccc;
			padding: 6px 15px;
			background-color: white;
		}
		#cauta{
			width:100%;
			text-align:center;
			margin:0;
			padding-top: 10px;
			padding-bottom:10px;
			background-color: #60b0f4;
		}
		#cauta button{
			width:150px;
		}
		@media all and (max-width: 768px) {
			#cauta button{
				width:100%;
			}
		}
		.spital_logo {
			width: 200px;
			height: 80px;
		}
		.doctor_avatar{
			height:50px;
			border-radius: 5px;
		}
		.doctor_listitem{
			border-bottom: 1px solid #acd4f6;
			border-radius: 12px;
			margin-top: 5px;
			margin-bottom: 5px;
		}
		.imgZone {
			text-align: center;
			position: relative;
		}
		#gallery,#gallery .imgZone,#gallery figure {
			border: 0!important;
			overflow: hidden!important;
			background: #222;
			display: table-cell!important;
			vertical-align: middle!important;
			height: 300px!important;
			max-height: 300px!important;
			min-height: 300px!important;
			overflow: hidden;
			float: left;
			width: 100%;
			max-width: 100%!important;
			min-width: 100%!important;
			margin-top: 0;
			text-align: center;
			position: relative;
			margin-top: 0
		}
		#gallery .imgZone .ad-photos {
			z-index: 30
		}

		#gallery a {
			width: 100%;
			height: 300px;
			overflow: hidden
		}

		#gallery a ul {
			float: left;
			width: 300%;
			height: 300px;
			display: inline-block;
			margin: 0;
			padding: 0
		}

		#gallery a li {
			display: inline-block;
			width: 33.33%;
			background-size: cover!important;
			list-style: none;
			float: left;
			height: 300px
		}

		#gallery .fa-star {
			font-size: 1.2rem;
			height: 34px;
			padding: 8px 8px;
			background: rgba(255,255,255,.8);
			border-radius: 50%;
			-moz-border-radius: 50%;
			-webkit-border-radius: 50%;
			-moz-box-shadow: 0 0 5px rgba(0,0,0,.5);
			-webkit-box-shadow: 0 0 5px rgba(0,0,0,.5);
			box-shadow: 0 0 5px rgba(0,0,0,.5)
		}

		#gallery ul.ad-detail-options,#gallery ul.ad-detail-options li {
			padding: 0!important;
			margin: 0!important;
			float: right;
			width: auto!important
		}

		#gallery ul.ad-detail-options li {
			position: relative;
			margin-top: -50px!important;
			right: 7px
		}
		.ad-photos {
			background: rgba(0,0,0,.8);
			border-radius: 2px;
			-webkit-border-radius: 2px;
			-moz-border-radius: 2px;
			bottom: 0;
			color: #fff;
			font-size: 15px;
			text-transform: uppercase;
			padding: 5px;
			position: absolute;
			left: 0;
			line-height: 1;
			opacity: .7
		}
		.imgArrayL,.imgArrayR {
			display: block;
			position: absolute;
			width: 55px;
			height: 55px;
			cursor: pointer;
			overflow: hidden;
			top: 122px!important;
			color: #fff;
			font-size: 20px
		}

		.imgArrayL {
			left: 0;
			display: none
		}

		.imgArrayR {
			right: 0
		}

		.imgArrayL .fa,.imgArrayR .fa {
			margin-right: 0!important;
			margin-top: 17px;
			text-shadow: 1px 1px 2px rgba(0,0,0,.5)
		}
		
		.contact-form{
			position: fixed!important;
			width: 100%!important;
			bottom: 0!important;
			z-index: 30;
		}
		.pswp__ui {
			-webkit-font-smoothing: auto;
			visibility: visible;
			opacity: 1;
			z-index: 1550
		}

		.pswp__top-bar {
			position: absolute;
			left: 0;
			top: 0;
			height: 44px;
			width: 100%
		}

		.pswp__caption,.pswp__top-bar,.pswp--has_mouse .pswp__button--arrow--left,.pswp--has_mouse .pswp__button--arrow--right {
			-webkit-backface-visibility: hidden;
			will-change: opacity;
			-webkit-transition: opacity 333ms cubic-bezier(.4,0,.22,1);
			transition: opacity 333ms cubic-bezier(.4,0,.22,1)
		}

		.pswp--has_mouse .pswp__button--arrow--left,.pswp--has_mouse .pswp__button--arrow--right {
			visibility: visible
		}

		.pswp__top-bar,.pswp__caption {
			background-color: rgba(0,0,0,.5)
		}

		.pswp__ui--fit .pswp__top-bar,.pswp__ui--fit .pswp__caption {
			background-color: rgba(0,0,0,.3)
		}

		.pswp__ui--idle .pswp__top-bar {
			opacity: .75
		}

		.pswp__ui--idle .pswp__button--arrow--left,.pswp__ui--idle .pswp__button--arrow--right {
			opacity: 0
		}

		.pswp__ui--hidden .pswp__top-bar,.pswp__ui--hidden .pswp__caption,.pswp__ui--hidden .pswp__button--arrow--left,.pswp__ui--hidden .pswp__button--arrow--right {
			opacity: .001
		}

		.pswp__ui--one-slide .pswp__button--arrow--left,.pswp__ui--one-slide .pswp__button--arrow--right,.pswp__ui--one-slide .pswp__counter {
			display: none
		}

		.pswp__element--disabled {
			display: none!important
		}

		.pswp--minimal--dark .pswp__top-bar {
			background: none
		}
	</style>
	<link href="<?php echo cs_url;?>/css/photoswipe/photoswipe.css" rel="stylesheet" />
	<link href="<?php echo cs_url;?>/css/photoswipe/default-skin.css" rel="stylesheet" /> 
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
<?php echo $cauta_form['resp']['html']?>
<?php if ($is_success){?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="detail-left view_content" style="">
				<div class="row">
					<div class="col-xs-12">
						<div style="width:250px;text-align:center;display:inline-block;float: left;">
							<?php if ($spitale_get['logo'] > 0){?>
								<img id="spital_logo" src="<?php echo cs_url."/csapi/images/view/?thumb=0&id=" . $spitale_get['logo'];?>" class="spital_logo" alt="Spital Logo">
							<?php }?>
						</div>
						<div style="display:inline-block;">
							<h2 style="margin:3px; font-size: 18px;"><?php echo htmlentities($spitale_get['nume'])?></h2>
							<p style="margin:3px;">
								<?php
								foreach($localitate_denumire as $locitem){?>
								<a href="<?php echo cs_url . '/doctori/' . $locitem['uri']?>">
									<?php echo $locitem['denumire']?>
								</a>
								<?php }?>
							</p>
						</div>
					</div>
				</div>
				<?php
					$imageok = true;
					$spitale_images_grid_rows = array();
					if (count($spitale_images_grid['resp']['rows']) > 0){
						foreach($spitale_images_grid['resp']['rows'] as $image_row){
							$images_get = cs('images/get',array("filters"=>array("groupOp"=>"AND","rules"=>array(array("field"=>"id","op"=>"eq","data"=>$image_row->image)))));
							if ($images_get == null) {$imageok = false; break;}
							$spitale_images_grid_rows[] = array(
								'images_get' => $images_get,
								'image_row' => $image_row,
							);
						}
					}
				?>
				<?php if ((count($spitale_images_grid['resp']['rows']) > 0) && $imageok){ ?>
				<script>
				var imageList = [];
				var speed = 150;
				var IMG_WIDTH;
				var maxImgHeight = 0;
				var maxImgHeightImgWidth = 0;
				</script>
				<div class="row" style="">
					<div class="col-sm-12">
						<div id="gallery">
							<div itemscope itemtype="https://schema.org/ImageGallery">
								<div class="imgZone">
									<span class="ad-photos detailViewCountImages"><span class="fa fa-camera"></span> <span id="imageViewNumber">1</span> / <?php echo count($spitale_images_grid['resp']['rows']); ?> </span>
									<a style="margin:0 !important;" itemprop="associatedMedia" itemscope itemtype="https://schema.org/ImageObject">
										<ul>
											<?php foreach($spitale_images_grid_rows as $imgitem){ ?> 
											<li style="background:url('<?php echo cs_url . '/csapi/images/view/?thumb=0&amp;id=' . $imgitem['images_get']['id']; ?>') no-repeat center;" class="detailViewImg" itemprop="image"></li>
											<script>
												imageList.push({ src: '<?php echo cs_url . '/csapi/images/view/?id=' . $imgitem['images_get']['id']; ?>', w: <?php echo $imgitem['images_get']['w']; ?>, h: <?php echo $imgitem['images_get']['h']; ?> });
											</script>
											<?php } ?>
										</ul>
									</a>
								</div>
								<span class="imgArrayL" onclick="previousImage()"><span class="fa fa-chevron-left"></span></span>
								<span class="imgArrayR" onclick="nextImage()"><span class="fa fa-chevron-right"></span></span>
							</div>
						</div>
					</div>
				</div>
				
				<?php } ?>
				<div class="row">
					<div class="col-xs-12">
						<h3>Descriere</h3>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 ql-editor" style="text-align: justify;">
						<?php echo ($spitale_get['descriere'])?>
					</div>
				</div>
				<?php if (($users_getspitalusers != null) &&($users_getspitalusers['resp']['records']>0)){?>
				<div class="row">
					<div class="col-xs-12">
						<h3>Personal medical</h3>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<?php foreach($users_getspitalusers['resp']['rows'] as $doctor){
							$doctor_uri = cs_url . '/doctori' . $cauta_uridecode['uri'] . '/' . $doctor->uri;
						?>
						<div class="row doctor_listitem">
							<div class="col-xs-4 text-center">
								<a href="<?php echo $doctor_uri?>">
									<div class="usersstatus <?php if (intval($doctor->status) == 1){echo 'online';}else{echo 'offline';}?>" users="<?php echo $doctor->id?>">
										<div class="usersstatus_circle_online"></div>
										<div class="usersstatus_circle_offline"></div>
										<img src="<?php 
											if (intval($doctor->image) > 0){
												echo cs_url."/csapi/images/view/?thumb=0&id=" . $doctor->image;
											}else{
												echo cs_url."/images/default_avatar.jpg";
											}
										?>" class="doctor_avatar" style="" alt="User Image">
									</div>
								</a>
							</div>
							<div class="col-xs-8">
								<div class="row">
									<div class="col-xs-12">		
										<h4><a href="<?php echo $doctor_uri?>"><?php echo $doctor->nume?></a></h4>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-12">		
										<b><?php echo $doctor->specializari_denumire?></b>
									</div>
								</div>
							</div>
						</div>
						<?php }?>
					</div>
				</div>
				<?php }?>
				<div class="row" style="border-bottom: 1px solid #ddd;margin-bottom:5px;padding-bottom:5px;background-color:#f2f4f5">
					<?php 
						$comments_listhtml = cs('comments/listhtml',array(
							'altid'=>$spitale_get['id'],
							'alttype'=>1,
						));
						cscheck($comments_listhtml);
						$comments_notaget = cs('comments/notaget',array(
							'altid'=>$spitale_get['id'],
							'alttype'=>1,
						));
						cscheck($comments_notaget);
						$comments_count = cs('comments/count',array(
							'altid'=>$spitale_get['id'],
							'alttype'=>1,
						));
						cscheck($comments_count);
					?>
					<div class="col-xs-12">
						<h3 style="font-size:14px">
							<div>Comentarii (<?php echo $comments_count['resp']['count']?>)</div>
							<div>Nota (<?php echo $comments_notaget['resp']['html']?>)</div>
						</h3>
					</div>
					<div class="col-xs-12" id="comments_item_0">
					<?php 
						$comments_addhtml = cs('comments/addhtml',array(
							'altid'=>$spitale_get['id'],
							'alttype'=>1,
							'parent'=>0,
						));
						cscheck($comments_addhtml);
						echo $comments_addhtml['resp']['html'];
					?> 
					</div>
					<div class="col-xs-12">
					<?php 
						echo $comments_listhtml['resp']['html'];
					?>
					</div>
				</div>
			</div>
			<div class="menu-right" style="">
				<div class="row">
					<div class="col-xs-12">
						<div class="panel panel-default" style="">
							<div class="panel-body">
								<?php if ($spitale_get['telefon'] != ''){?>
								<div class="row">
									<div class="col-xs-12">
										<a class="btn btn-success btn-block" href="<?php echo 'tel:' . $spitale_get['telefon']?>">
											<i class="fa fa-phone" aria-hidden="true"></i> <?php echo $spitale_get['telefon']?>
										</a>
									</div>
								</div>
								<?php }?>
								<div class="row" style="margin-top:7px">
									<div class="col-xs-12">
										<div class="usersstatus <?php 
											$partstatus = 'offline';
											if ((strtotime(date('Y-m-d H:i:s')) - strtotime($manager['date'])) > usersstatus_maxidle){
												$partstatus = 'offline';
											}else{
												$partstatus = 'online';
											}
											if (intval($manager['status']) == 0){$partstatus = 'offline';}
											echo $partstatus;
											?>" users="<?php echo $manager['id']
											?>" style="display:block">
											<div class="usersstatus_custom_online" >
												<a class="btn btn-default btn-block" href='<?php 
													$payload = array();
													$mek = ($minichat['mek']=='browser'?$minichat['mek']:'to');
													$payload[$mek] = intval($minichat['mev']);
													$payload['from'] = intval($manager['id']);
													if ($payload[$mek] != $payload['from']){
														echo cs_url . '/mesaje/' .  $manager['uri'];													
													}else{
														echo 'javascript:void(0)';
													}
													?>' style="border: 1px solid lime;">
													<i class="fa fa-commenting-o" aria-hidden="true"></i> ONLINE - start chat
												</a>
											</div>
											<div class="usersstatus_custom_offline">
												<a class="btn btn-default btn-block" onclick="<?php 
													if (isset($_SESSION['cs_users_id'])){
														echo 'cs(\'mesaje/create_rs\',{to:' . $manager['id'] . '})';
													}else{
														echo 'cs(\'users/login_rs\')';
													}
													?>" href="javascript:void(0)" >
													<i class="fa fa-envelope" aria-hidden="true"></i> Mesaj
												</a>
											</div>
										</div>
									</div>
								</div>
								<div class="row" style="border-top: 1px solid #ddd;margin-top:5px;padding:5px 0;">
									<div class="col-xs-12" >
										Adresa: 
									</div>
									<div class="col-xs-12">
										<?php
										foreach($localitate_denumire as $locitem){?>
										<a href="<?php echo cs_url . '/doctori/' . $locitem['uri']?>">
											<?php echo $locitem['denumire']?>
										</a>
										<?php }?>
									</div>
									<?php if ($spitale_get['adresa'] != ''){?>
									<div class="col-xs-12">
										<p><?php echo htmlentities($spitale_get['adresa'])?></p>
									</div>
									<?php }?>
								</div>
								<div class="row" style="">
									<div class="col-xs-12 text-center" style="border-top: 1px solid #ddd;margin-bottom:5px;padding:5px 0;">
										<a href="<?php echo cs_url . '/statistica-unitate/' . $spitale_get['uri'] . '.html'; ?>" >
											<i class="fa fa-eye" aria-hidden="true"></i> Vizualizări: <?php echo $logs_count['resp']['count']?>
										</a> 
									</div>
								</div>
								<div class="row" style="">
									<div class="col-xs-12 text-center" style="border-top: 1px solid #ddd;margin-bottom:5px;padding:5px 0;">
										<h3 style="margin:0;font-size:15px">Distribuie anuntul pe</h3>
									</div>
									<div class="col-xs-12 text-center" style="">
										<a rel="nofollow" title="Distribuie pe Facebook" href="javascript:void(0)" onclick="window.open('https://www.facebook.com/sharer.php?u='+encodeURIComponent(location.href)+'&amp;t='+encodeURIComponent(document.title),'sharer','toolbar=0,status=0,width=626,height=436')" class="fa fa-mylogin fa-facebook" style="padding: 18px;font-size: 18px;width: 18px;margin: 1px 2px;"></a>
										<a rel="nofollow" title="Distribuie pe Google+" href="javascript:void(0)" onclick="window.open('https://plus.google.com/share?url='+encodeURIComponent(location.href)+'','', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600')" class="fa fa-mylogin fa-google" style="padding: 18px;font-size: 18px;width: 18px;margin: 1px 2px;"></a>
										<a rel="nofollow" title="Distribuie pe whatsapp" href="javascript:void(0)" onclick="window.open('whatsapp://send?text=' + encodeURIComponent(location.href))" class="fa fa-mylogin fa-whatsapp" style="padding: 18px;font-size: 18px;width: 18px;margin: 1px 2px;"></a>
										<a rel="nofollow" title="Distribuie pe viber" href="javascript:void(0)" onclick="window.open('viber://forward?text=' + encodeURIComponent(location.href))" class="fa fa-mylogin fa-phone" style="padding: 18px;font-size: 18px;width: 18px;margin: 1px 2px; background: #59267c; color:white"></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
	$userissame = false;
	if (isset($_SESSION['cs_users_id']) && ($_SESSION['cs_users_id'] == $manager['id'])) $userissame = true;
?>
<?php if (!$userissame){?>
<div class="usersstatus minichat-start-button <?php 
		$partstatus = 'offline';
		if ((strtotime(date('Y-m-d H:i:s')) - strtotime($manager['date'])) > usersstatus_maxidle){
			$partstatus = 'offline';
		}else{
			$partstatus = 'online';
		}
		if (intval($manager['status']) == 0){$partstatus = 'offline';}
		echo $partstatus;
	?> " users="<?php echo $manager['id'] ?>">
	<div class="usersstatus_custom_online" >
		<button class="btn btn-success btn-lg" type="button" onclick='<?php 
			$payload = array();
			$mek = ($minichat['mek']=='browser'?$minichat['mek']:'to');
			$payload[$mek] = intval($minichat['mev']);
			$payload['from'] = $manager['id'];
			if ($payload[$mek] != $payload['from']){
				echo 'minichat_show(' . json_encode($payload) . ')';														
			}else{
				echo 'javascript:void(0)';
			}
			?>'><i class="fa fa-comment-o" aria-hidden="true"></i> <?php echo $manager['nume'] ?> <span class="minichat_count"></span></button>
	</div>
</div>
<?php }?>

<?php }else{?>
oupssss.. nu am gasit spitalul cautat...
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
		
		,$spitale_get
	;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
?>
	<!-- Root element of PhotoSwipe. Must have class pswp. -->
	<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
		<!-- Background of PhotoSwipe.
			 It's a separate element as animating opacity is faster than rgba(). -->
		<div class="pswp__bg"></div>
		<!-- Slides wrapper with overflow:hidden. -->
		<div class="pswp__scroll-wrap">
			<!-- Container that holds slides.
				PhotoSwipe keeps only 3 of them in the DOM to save memory.
				Don't modify these 3 pswp__item elements, data is added later on. -->
			<div class="pswp__container">
				<div class="pswp__item"></div>
				<div class="pswp__item"></div>
				<div class="pswp__item"></div>
			</div>
			<!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
			<div class="pswp__ui pswp__ui--hidden">
				<div class="pswp__top-bar">
					<!--  Controls are self-explanatory. Order can be changed. -->
					<div class="pswp__counter"></div>
					<button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
					<button class="pswp__button pswp__button--share" title="Share"></button>
					<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
					<button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
					<!-- Preloader demo https://codepen.io/dimsemenov/pen/yyBWoR -->
					<!-- element will get class pswp__preloader--active when preloader is running -->
					<div class="pswp__preloader">
						<div class="pswp__preloader__icn">
							<div class="pswp__preloader__cut">
								<div class="pswp__preloader__donut"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
					<div class="pswp__share-tooltip"></div>
				</div>
				<button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
				<button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
				<div class="pswp__caption">
					<div class="pswp__caption__center"></div>
				</div>
			</div>
		</div>
	</div>

	<script>
		cauta_specializari_breadcrumb_parentid = 0;
		cauta_specializari_breadcrumb_parentparent = 0;
		cauta_localitati_breadcrumb_nodearr = <?php echo json_encode($cauta_localitati_breadcrumb['resp']['nodearr'])?>;
		cauta_specializari_breadcrumb_nodearr = <?php echo json_encode($cauta_specializari_breadcrumb['resp']['nodearr'])?>;
		cauta_localitate_id = <?php if ($cauta_localitate_id > 0){ echo $cauta_localitate_id; }else{echo 'null';}?>;
		cauta_specializare_id = <?php if ($cauta_specializare_id > 0){ echo $cauta_specializare_id; }else{echo 'null';}?>;
		
		usersstatus_onload_logs = {alttype:1,altid:<?php echo $spitale_get['id'];?>};
		
	</script>
	<script src="<?php echo cs_url;?>/js/auxiliary-rater-0831401/rater.min.js"></script>
	<script src="<?php echo cs_url;?>/js/jquery.touchSwipe.min.js"></script>
	<script src="<?php echo cs_url;?>/js/cauta.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script src="<?php echo cs_url;?>/js/photoswipe/photoswipe.min.js"></script>
	<script src="<?php echo cs_url;?>/js/photoswipe/photoswipe-ui-default.js"></script>
	<script src="<?php echo cs_url;?>/js/doctori_spital_mob.js?timestamp=<?php echo cs_updatescript;?>"></script>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>
