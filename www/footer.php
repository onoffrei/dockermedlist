		<div class="footer_main">
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-3 <?php if ($GLOBALS['mobile_ismobile']){echo 'text-center';}?>">
						<h4>Info util</h4>
						<ul>
							<li><a href="<?php echo cs_url?>/contact"><i class="fa fa-address-card" aria-hidden="true"></i> Contact</a></li>
							<li><a href="<?php echo cs_url?>/termenisiconditii"><i class="fa fa-info" aria-hidden="true"></i> Termeni si conditii</a></li>
							<li><a href="<?php echo cs_url?>/politicadeconfidentialitate"><i class="fa fa-lock" aria-hidden="true"></i> Politica de confidentialitate</a></li>
						</ul>
					</div>
					<div class="col-sm-3 <?php if ($GLOBALS['mobile_ismobile']){echo 'text-center';}?>">
						<h4>Cabinete/Clinici</h4>
						<ul>
							<li><a href="<?php echo cs_url?>/adaugaspital"><i class="fa fa-plus" aria-hidden="true"></i> Adauga cabinet/clinica</a></li>
							<li></li>
							<li></li>
						</ul>
					</div>
					<div class="col-sm-3 <?php if ($GLOBALS['mobile_ismobile']){echo 'text-center';}?>">
						<h4>Pacienti</h4>
							<ul>
								<li><a href="javascript:void(0)" onclick="cs('users/login_rs',{callback:'login_callback'})"><i class="fa fa-user" aria-hidden="true"></i> Contul meu</a></li>
								<li><a href="<?php echo cs_url?>/doctori"><i class="fa fa-search" aria-hidden="true"></i> Cauta doctor</a></li>
								<li></li>
							</ul>
					</div>
					<div class="col-sm-3 text-right  <?php if ($GLOBALS['mobile_ismobile']){echo 'text-center';}?>">
						<ul>
							<li>
								<?php if ($GLOBALS['mobile_ismobile']){?>
								<a href="tel:0744969875">
								<?php }?>
								<i class="fa fa-phone-square" aria-hidden="true"></i> 0744969875
								<?php if ($GLOBALS['mobile_ismobile']){?>
								</a>
								<?php }?>
							</li>
							<li>
								<a href="https://www.facebook.com/MedList-2173666836285437" target="_blank"><i class="fa fa-facebook-official" aria-hidden="true"></i> Facebook</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="footer_copyright">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xs-12">
						<p>© medlist.ro 2019 - Programari online la doctor</p>
					</div>
				</div>
			</div>
		</div>
		<script>
			window.cs_url = "<?php echo cs_url;?>/";
			window.cs_url_po = "<?php echo cs_url_po;?>/";
			window.server_time = "<?php echo date('Y-m-d H:i:s');?>";
			window.mobile_ismobile = <?php echo $GLOBALS['mobile_ismobile']?'true':'false';?>;
			<?php if (defined('usersstatus_maxidle')){?>
			window.usersstatus_maxidle = <?php echo intval(usersstatus_maxidle)?>;
			<?php }?>
			<?php if (defined('cs_push_publickey')){?>
			window.cs_push_publickey = '<?php echo cs_push_publickey?>';
			<?php }?>
			<?php if (isset($_SESSION['cs_users_id'])){
				echo 'cs_users_id = ' . $_SESSION['cs_users_id'] . ';';
			}?>;
			minichat = <?php echo json_encode($GLOBALS['minichat']);?>;
			<?php if (!isset($_SESSION['browser_browser']) || ($_SESSION['browser_browser'] == '')){?>
			// window.location.reload();
			<?php }?>
			<?php 
			if (defined('cs_debug') && cs_debug){ $new_GLOBALS_array = array(); foreach($GLOBALS as $gn => $gv){ if ($gn != 'GLOBALS') $new_GLOBALS_array[$gn] = $gv; }?>
			console.info('globals',<?php echo json_encode($new_GLOBALS_array);?>);
			<?php } ?>
		</script>
		<script src="<?php echo cs_url;?>/js/_cs.js?timestamp=<?php echo cs_updatescript;?>"></script>
		<script src="<?php echo cs_url;?>/js/jquery.js?timestamp=<?php echo cs_updatescript;?>"></script>
		<script src="<?php echo cs_url;?>/js/jquery.cookie.js"></script>
		<script src="<?php echo cs_url;?>/js/bootstrap.min.js"></script>
		<?php if ($GLOBALS['mobile_ismobile']){?>
		<script src="<?php echo cs_url_po;?>/js/minichatmobile.js?timestamp=<?php echo cs_updatescript;?>"></script>
		<?php }else{?>
		<script src="<?php echo cs_url_po;?>/js/minichat.js?timestamp=<?php echo cs_updatescript;?>"></script>
		<?php }?>
		<?php if (defined('cs_push_publickey')){?>
		<script src="<?php echo cs_url;?>/js/push.js?timestamp=<?php echo cs_updatescript;?>"></script>
		<?php }?>
		<script>
			$('#navbar .activedropdown').hover(
				function(){$(this).toggleClass('open');}
			);
			$('#navbar .activedropdown').on('click',function(e){console.log(e)});
		</script>
		<?php if (defined('usersstatus_maxidle')){?>
		<script src="<?php echo cs_url_po;?>/js/usersstatus.js?timestamp=<?php echo cs_updatescript;?>"></script>
		<?php }?>
		<?php if (isset($_SESSION['firebase_activeid']) && ($_SESSION['firebase_activeid'] != '')){ ?>
		<script src="https://www.gstatic.com/firebasejs/5.5.8/firebase-app.js"></script>
		<script src="https://www.gstatic.com/firebasejs/5.5.8/firebase-database.js"></script>
		<script>
			login_callback = function(login){
				$("div.modal-backdrop.fade.in").remove()
				$('#users_register_htmlmodal').modal('hide');
				$('#users_login_htmlmodal').modal('hide');
				if ((typeof(login.success) == 'undefined')||(login.success != true)){
					if ((typeof(login.error) != 'undefined') && (login.error != '')){
						alert(login.error)
					}else{
						alert('something went wrong')
					}
					return
				}
				// window.location.reload()
			}
			firebase_dbname = '<?php echo cs_firebase_db_name;?>';
			firebase_config = {
				databaseURL: "https://<?php echo cs_firebase_db_name;?>.firebaseio.com",
			};
			firebase_lastsnapshot = {timestamp:'<?php echo date('Y-m-d H:i:s')?>'}
			firebase.initializeApp(firebase_config);
			firebase.database().ref('/<?php echo $_SESSION['firebase_path'];?>/<?php echo $_SESSION['firebase_activeid'];?>/updates').on('value', function(snapshot) {
			  console.log('snapshot',snapshot)
			  console.log('snapshot.val',snapshot.val())
			  var resp = snapshot.val()
			  if (resp != null && typeof(resp.timestamp)!='undefined'){
				  var newdate = sqlToJsDate(resp.timestamp);
				  var olddate = sqlToJsDate(firebase_lastsnapshot.timestamp);
				  console.log(resp.timestamp)
				  console.log(newdate,olddate)
				  if (newdate.getTime() > olddate.getTime()){
					  if ((typeof(resp.action) != 'undefined') && (typeof(resp.action.name) != 'undefined')){
						  var param = {}
						  if (typeof(resp.action.param) != 'undefined') param = resp.action.param
						  firebase_dispatch(resp.action.name,param)
					  }					  
				  }
				  firebase_lastsnapshot = resp
			  }
			});
			firebase_dispatch = function(action,payload){
				console.log('action',action)
				switch(action){
					case 'mesaje_received':
						if (typeof(window.minichat_dispatch) == 'function'){
							console.log('d1')
							minichat_dispatch(action,payload)
						}else{
							if (window.location.pathname.startsWith('/mesaje')){
								if (typeof(window[action]) == 'function'){
									window[action](payload)
								}
							}
						}
					break
					<?php if (defined('usersstatus_maxidle')){?>
					case 'usersstatus_userchange':
						if (typeof(window.usersstatus_dispatch) == 'function'){
							usersstatus_dispatch(action,payload)
						}
					break;
						<?php if (isset($_SESSION['cs_users_id'])){?>
						case 'ping':
							if (typeof(window.usersstatus_dispatch) == 'function'){
								usersstatus_dispatch(action,payload)
							}
						break;
						<?php }?>
					<?php }?>
				}
			}
		</script>
		<?php }?>
		<?php if (isset($GLOBALS['footer_ob'])) {
			echo $GLOBALS['footer_ob']['resp']['html'];
		}?>
		
		<?php if (cs_url_host == 'medlist.ro'){?>
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-128878660-1"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', 'UA-128878660-1');
		</script>
		<?php }?>
		
		
	</body>
</html>