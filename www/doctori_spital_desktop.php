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
		.secondcontact{
			top: 50px;
			position: fixed;
			width: 300px;
			z-index: 50;
			display:none;
		}
		.secondcontact.active{
			display:block;
		}
		@media all and (max-width: 768px) {
			.secondcontact {display: none!important;}
		}
		#cauta{
			padding-top: 10px;
			padding-bottom:10px;
			background-color: #60b0f4;
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
		#gallery {
			border-top: 1px solid #ddd;
			border-bottom: 1px solid #ddd;
			overflow: hidden;
			padding: 15px 0;
			float: left;
			width: 100%;
			margin-top: 15px;
			text-align: center;
		}
		.imgZone {
			text-align: center;
			position: relative;
		}
		.detailViewImg {
			cursor: -webkit-zoom-in;
			vertical-align: middle
		}
		#detailViewImg a:hover>#zoomerGlass {
			display: block
		}
		.ad-photos {
			background: rgba(0,0,0,.8);
			border-radius: 2px;
			bottom: 0;
			color: #fff;
			font-size: .875rem;
			text-transform: uppercase;
			padding: 5px;
			position: absolute;
			left: 0;
			line-height: 1;
			opacity: .7;
		}
		.imgArrayL, .imgArrayR {
			display: block;
			position: absolute;
			width: 55px;
			height: 55px;
			cursor: pointer;
			background: url(<?php echo cs_url ?>/images/a02.png) no-repeat;
			overflow: hidden;
		}
		.imgArrayL {
			background-position: -243px -33px;
			left: 8px;
		}
		.imgArrayR {
			background-position: -303px -33px;
			right: 8px;
		}
		.zoomerGlass {
			display: none;
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			margin: auto!important;
			width: 55px;
			height: 55px;
			color: #fff;
			font-size: 55px;
			text-align: center;
			text-shadow: 1px 1px 2px #000;
			opacity: .7;
			cursor: -webkit-zoom-in;
			pointer-events: none;
		}
		.detailViewCountImages {
			font-size: 1.4375rem
		}
		#detail-gallery {
			width: 100%;
			display: inline-flex;
			padding-bottom: 10px;
			border-bottom: 1px solid #ddd;
			height: 111px
		}

		.nav-controls-prev {
			width: auto!important;
			float: left
		}

		.nav-controls-next {
			width: auto!important;
			float: right
		}

		.ChangeImagePrev {
			background: url('../images/prev_image.png') no-repeat scroll left center transparent;
			border: 0 none;
			cursor: pointer;
			display: inline-block;
			height: 27px;
			width: 25px;
			float: left;
			padding: 55px 10px 55px 0;
			opacity: .4;
			filter: alpha(opacity=40)
		}

		.ChangeImageNext {
			background: url('../images/next_image.png') no-repeat scroll right center transparent;
			border: 0 none;
			cursor: pointer;
			display: inline;
			height: 27px;
			width: 25px;
			float: left;
			opacity: .4;
			filter: alpha(opacity=40);
			padding: 55px 0 55px 10px
		}

		.ChangeImagePrev:hover,.ChangeImageNext:hover {
			opacity: .6;
			filter: alpha(opacity=60)
		}

		.detailthumbs li {
			height: 110px;
			width: 140px;
			display: inline-block;
			border-color: #fff;
			overflow: hidden;
			position: relative;
			vertical-align: middle;
			line-height: 88px;
			border: 10px solid #fff
		}

		.detailthumbs img {
			max-width: 120px;
			max-height: 90px;
			cursor: pointer
		}

		.detailthumbs li:first-child {
			border-color: #ddd
		}

		.thumbZone {
			height: 110px;
			overflow: hidden;
			float: left;
			width: 100%;
			text-align: center;
			margin: 0
		}

		#detail-right {
			width: 300px;
			float: right
		}

		.detail-pager {
			display: none;
			background: #fff;
			float: left;
			margin-top: 22px;
			width: 300px;
			padding: 14px 0 14px 0;
			background: #fff;
			position: relative;
			text-align: center;
			z-index: 100;
			margin-top: -45px;
			top: 21px
		}

		.detail-pager.secondary {
			top: 65px;
			position: fixed;
			margin-top: 0
		}

		.action-buttons.secondary {
			top: 62px;
			box-shadow: 0 10px 10px rgba(255,255,255,.7);
			position: fixed;
			margin-top: 0;
			z-index: 50;
			background: #eef7fd;
			width: 300px;
			padding: 20px;
			border: 1px solid #bde1f9;
			margin-left: -21px;
			border-top: 0;
			-webkit-border-bottom-right-radius: 5px;
			-webkit-border-bottom-left-radius: 5px;
			-moz-border-radius-bottomright: 5px;
			-moz-border-radius-bottomleft: 5px;
			border-bottom-right-radius: 5px;
			border-bottom-left-radius: 5px
		}

		.detail-pager span {
			margin: 0
		}

		.detail-pager a {
			float: none!important;
			font-size: 1rem!important;
			color: #999;
			font-weight: normal
		}

		.detail-pager a.prev {
			float: left!important
		}

		.detail-pager a.prev span {
			margin-right: 8px
		}

		.detail-pager a.next {
			float: right!important
		}

		.detail-pager a.next span {
			margin-left: 8px
		}

		.detail-pager a.inactive {
			color: #ccc;
			text-decoration: none;
			cursor: default!important
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
<?php 
function view_contact(){
	global $manager, $spitale_get, $minichat, $localitate_denumire;
	$ret = array('success'=>false, 'resp'=>array());
	ob_start();
	?>
	<?php if ($spitale_get['telefon'] != ''){?>
	<div class="row">
		<div class="col-xs-12">
			<a class="btn btn-success btn-block">
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
					<a class="btn btn-default btn-block" onclick='<?php 
						$payload = array();
						$mek = ($minichat['mek']=='browser'?$minichat['mek']:'to');
						$payload[$mek] = intval($minichat['mev']);
						$payload['from'] = intval($manager['id']);
						if ($payload[$mek] != $payload['from']){
							echo 'minichat_update(' . json_encode($payload) . ')';														
						}else{
							echo 'javascript:void(0)';
						}
						?>' href="javascript:void(0)" style="border: 1px solid lime;">
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
	<?php
	$ret['resp']['html'] = ob_get_contents();
	ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$view_contact = view_contact();
cscheck($view_contact);
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
						$images_base64_ob = cs('images/base64_ob',array(
							'id' => $spitale_images_grid['resp']['rows'][0]->image,
							//'thumb' => 0,
							
						)); 
						if (!isset($images_base64_ob['success']) || ($images_base64_ob['success'] != true)){
							$imageok = false;
						}
					}
				?>
				<?php if ((count($spitale_images_grid['resp']['rows']) > 0) && $imageok){ ?>
				<div class="row" style="">
					<div class="col-sm-12">
						<div id="gallery">
							<div itemscope itemtype="https://schema.org/ImageGallery">
								<div class="imgZone">
									<span class="ad-photos detailViewCountImages"><span class="fa fa-camera"></span> <span id="imageViewNumber">1</span> / <?php echo count($spitale_images_grid['resp']['rows']); ?> </span>
									<figure style="margin:0 !important;" itemprop="associatedMedia" itemscope itemtype="https://schema.org/ImageObject">
										<img src="<?php echo $images_base64_ob['resp'];?>" class="detailViewImg " itemprop="image" currentpic="0" alt="<?php 
											echo htmlentities($spitale_get['nume']); ?>" title="<?php 
											echo htmlentities($spitale_get['nume']); ?>" />
									</figure>
										<span class="imgArrayL" onclick="GoToPicture(parseInt($('.detailViewImg').attr('currentpic')) - 1)"></span>
										<span class="imgArrayR" onclick="GoToPicture(parseInt($('.detailViewImg').attr('currentpic')) + 1)"></span>
									<span class="fa fa-search zoomerGlass"></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<script>
					var imageList = [];
					var maxImgHeight = 0;
					var maxImgHeightImgWidth = 0;
				</script>
				<div id="detail-gallery" style="<?php if (count($spitale_images_grid['resp']['rows']) == 1){echo 'display:none';} ?>">
					<div class="nav-controls-prev">
						<a class="ChangeImagePrev"></a>
					</div>
					<ul class="thumbZone detailthumbs">
						<?php foreach($spitale_images_grid_rows as $imgitem){ ?> 
						<script>
							if (<?php echo $imgitem['images_get']['h']; ?> > maxImgHeight){maxImgHeight = <?php echo $imgitem['images_get']['h']; ?>; maxImgHeightImgWidth = <?php echo $imgitem['images_get']['w']; ?>;}
							imageList.push({ src: '<?php echo cs_url . '/csapi/images/view/?id=' . $imgitem['images_get']['id']; ?>', w: <?php echo $imgitem['images_get']['w']; ?>, h: <?php echo $imgitem['images_get']['h']; ?> });
						</script>
						<li>
							<img title="<?php 
								echo htmlentities($spitale_get['nume']); ?>" alt="<?php 
								echo htmlentities($spitale_get['nume']); ?>" src="<?php
								echo cs_url . '/csapi/images/view/?thumb=0&amp;id=' . $imgitem['image_row']->image
								?>">
						</li>
						<?php } ?>
					</ul>
					<div class="nav-controls-next">
						<a class="ChangeImageNext"></a>
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
									<div class="usersstatus <?php 
										$drstatus = 'offline';
										if ((strtotime(date('Y-m-d H:i:s')) - strtotime($doctor->date)) > usersstatus_maxidle){
											$drstatus = 'offline';
										}else{
											$drstatus = 'online';
										}
										if (intval($doctor->status) == 0){$drstatus = 'offline';}
										echo $drstatus;
										?>" users="<?php echo $doctor->id?>">
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
				<?php 
				//$pag_maxrows = 1;
				if ($users_getspitalusers['resp']['records'] > $pag_maxrows){?>
				<div class="row">
					<div class="col-xs-12 text-center">
						<nav aria-label="...">
							<ul class="pager">
								<li><a href="javascript:void(0)" onclick="pagination_button_click(-1)">Previous</a></li>
								<li><a href="javascript:void(0)" onclick="pagination_button_click(1)">Next</a></li>
							</ul>
						</nav>
						pagina <?php echo $users_getspitalusers['resp']['page']?> din <?php echo $users_getspitalusers['resp']['total']?>
					</div>
				</div>
				<script>
					pagination_button_click = function(next){
						var p_page = <?php echo $p_page;?>;
						var totalpages = <?php echo $users_getspitalusers['resp']['total'];?>;
						var nextpage = p_page + next;
						if ((nextpage > 0) && (nextpage <=totalpages) && (nextpage != p_page)){
							window.location.href = setURLParameter({name:'p_page',value:nextpage})
						}
						
					}
				</script>
				<?php }?>
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
						<div class="panel panel-default secondcontact" style="">
							<div class="panel-body">
								<?php echo $view_contact['resp']['html']; ?>
							</div>
						</div>
						<div class="panel panel-default" style="">
							<div class="panel-body">
								<?php echo $view_contact['resp']['html']; ?>
							</div>
						</div>
						<div class="panel panel-default" style="">
							<div class="panel-body">
								<div class="row" style="">
									<div class="col-xs-12 text-center" style="">
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
		
		document.addEventListener("DOMContentLoaded", function(d) {
			var secondcontactmaxtop = 60;
			var scroll = getCurrentScroll();
			if (scroll >= secondcontactmaxtop) {
				$('.secondcontact').addClass('active');
			} else {
				$('.secondcontact').removeClass('active');
			}
			$(window).scroll(function() {
				var scroll = getCurrentScroll();
				if (scroll >= secondcontactmaxtop) {
					$('.secondcontact').addClass('active');
				} else {
					$('.secondcontact').removeClass('active');
				}
			});
		})
		function getCurrentScroll() {
			return window.pageYOffset || document.documentElement.scrollTop;
		}
	</script>
	<script src="<?php echo cs_url;?>/js/auxiliary-rater-0831401/rater.min.js"></script>
	<script src="<?php echo cs_url;?>/js/cauta.js?timestamp=<?php echo cs_updatescript;?>"></script>
	<script src="<?php echo cs_url;?>/js/photoswipe/photoswipe.min.js"></script>
	<script src="<?php echo cs_url;?>/js/photoswipe/photoswipe-ui-default.js"></script>
	<script src="<?php echo cs_url;?>/js/doctori_spital.js?timestamp=<?php echo cs_updatescript;?>"></script>
<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>
