document.addEventListener("DOMContentLoaded", function(){
	usersstatus_init()
})

usersstatus_timerrunning = false

usersstatus_dispatch = function(action,payload){
	switch(action){
		case 'usersstatus_userchange':
			usersstatus_payload = payload
			usersstatus_drawstatus(payload.user,payload.status)
		break
		case 'ping':
			usersstatus_payload = payload
			cs('usersstatus/pong')
		break
	}
}
usersstatus_init = function(){
	console.log('userstatus start')
	var list = [];
	$('.usersstatus').map(function(i,e){
		var $e = $(e)
		if ((parseInt($e.attr('users'))>0) && (!list.includes(parseInt($e.attr('users'))))){
			console.log(i, parseInt($e.attr('users')),list)
			list[list.length] = parseInt($e.attr('users'))
		}
	}).get()
	
	var watchlist_add_param = {list:list,pathname:window.location.pathname}
	if (typeof(usersstatus_onload_logs) != 'undefined'){
		watchlist_add_param['logs'] = usersstatus_onload_logs
		delete usersstatus_onload_logs
	}
	
	if (typeof(push_init) != 'undefined'){
		push_init.then(function(push){
			if ((typeof(push) != 'undefined') && (typeof(push.pushSubscription) != 'undefined')){
				watchlist_add_param.pushSubscription = push.pushSubscription;
			}
			cs('usersstatus/watchlist_add',watchlist_add_param).then(function(watchlist_add){
				if ((typeof(watchlist_add.success) != 'undefined') && watchlist_add.success == true){
					if (watchlist_add.resp.records > 0){
						var olderdate = sqlToJsDate(watchlist_add.resp.rows[0].date)
						var curentdate = sqlToJsDate(watchlist_add.resp.date)
						var aftersec = parseInt(((olderdate.getTime() + (window.usersstatus_maxidle * 1000)) - curentdate.getTime())/1000)
						if (aftersec == 0) {aftersec = window.usersstatus_maxidle}
						//console.log('aftersec',aftersec)
						if ((typeof(watchlist_add.resp.timer) != 'undefined') && (aftersec > 0)){
							usersstatus_timerfunc(aftersec)
						}
						watchlist_add.resp.rows.map(function(ud,ui){
							usersstatus_drawstatus(ud.pray,ud.status)
						})
					}
					if ((typeof(watchlist_add.resp.mesaje_countnew) != 'undefined') && (watchlist_add.resp.mesaje_countnew > 0)){
						$('.topbar_mesaje').addClass('new')
					}else{
						$('.topbar_mesaje').removeClass('new')
					}
					if ((typeof(watchlist_add.resp.programari_countnew) != 'undefined') && (watchlist_add.resp.programari_countnew > 0)){
						$('.topbar_pacienti').addClass('new')
					}else{
						$('.topbar_pacienti').removeClass('new')
					}
				}
			})
		})
	}
}
usersstatus_timerfunc = function(aftersec){
	console.log('timercall')
	if (usersstatus_timerrunning == true) return;
	usersstatus_timerrunning = true
	console.log('timerstarted',aftersec)
	setTimeout(function(){
		usersstatus_timerrunning = false
		usersstatus_init()
	},parseInt(aftersec * 1000))
	//},3000)
}
usersstatus_drawstatus = function(user,status){
	//console.log('usersstatus_drawstatus',user,status)
	var classnew = 'offline'
	var classold = 'online'
	if (parseInt(status) == 1){
		var classnew = 'online'
		var classold = 'offline'
	}
	$('.usersstatus[users=' + user + ']').removeClass(classold)
	$('.usersstatus[users=' + user + ']').addClass(classnew)
}