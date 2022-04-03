minichat_getconversationlist = false
minichat_payload = {}
minichat_partener = {key:'',val:''}
minichat_partenerkv = {}
minichat_dispatch = function(action,payload){
	switch(action){
		case 'mesaje_received':
			if (
				window.location.pathname.startsWith('/mesaje')
			){
				if (typeof(window[action]) == 'function'){
					window[action](payload)
				}
			}else{
				console.log('d1')
				minichat_update(payload)
			}
		break
	}
}
document.addEventListener("DOMContentLoaded", function(d){
	if (localStorage.getItem("mek") !== null){
		if ((localStorage.getItem("mek") != minichat.mek) || (parseInt(localStorage.getItem("mev")) != parseInt(minichat.mev))){
			localStorage.removeItem("minichat_frame")
			localStorage.setItem('mek',minichat.mek) 
			localStorage.setItem('mev',minichat.mev) 
		}
	}else{
		localStorage.setItem('mek',minichat.mek) 
		localStorage.setItem('mev',minichat.mev) 
	}
	if (localStorage.getItem("minichat_frame") !== null) {
		if (
			window.location.pathname.startsWith('/mesaje')
		){
			localStorage.removeItem("minichat_frame")
		}else{
			$("body").append(localStorage.getItem("minichat_frame"))
			if ($('.minichat_frame1').length > 0){
				$('.minichat-mesagelist').map(function(i,e){
					console.log(i,e);
					$(e).scrollTop($(e).prop('scrollHeight') - $(e).innerHeight())
				})
				$('.minichat-start-button').css({display:'none'})
			}
		}
	}
})
minichat_show = function(payload){
	if ($('.minichat-start-button').length > 0){
		$('.minichat-start-button').css({display:'none'})
	}
	minichat_update(payload)
	$('.minichat_frame').css({display:'block'})
}
minichat_update = function(payload){
	$('.topbar_mesaje').addClass('new')
	minichat_payload = payload
	console.log('minichat_update',payload)
	minichat_getconversationlist = true
	if (typeof(minichat_payload.browser) != 'undefined') {
		minichat_partener.key =  'browser'
		minichat_partener.val =  minichat_payload.browser
	}
	if (typeof(minichat_payload.from) != 'undefined') {
		minichat_partener.key =  'from'
		minichat_partener.val =  minichat_payload.from
	}
	if (!(parseInt(minichat_partener.val)>0)){
		console.error('wrong arg, from/browser')
	}
	minichat_partenerkv = {}
	minichat_partenerkv[minichat_partener.key] = minichat_partener.val
	
	minichat_frame(payload).then(function(minichat_frame_resp){
		minichat_frame1(minichat_frame_resp).then(function(minichat_frame1_resp){
			minichat_conversationlist(minichat_frame1_resp).then(function(){
				localStorage.setItem('minichat_frame',$('.minichat_frame')[0].outerHTML)
			})
		})
	})
}
minichat_frame = function(payload){
	return new Promise(function(resolve,reject){
		if ($('.minichat_frame').length == 0){
			cs('mesaje/minichat_frame',{payload:minichat_payload,
			then:['mesaje/minichat_frame1',{payload:minichat_payload,
			then:['mesaje/conversationlist',minichat_partenerkv]}]}).then(function(minichat_frame){
				if ((typeof(minichat_frame.success) != 'undefined') && minichat_frame.success){
					$("body").append(minichat_frame.resp.html)
				}
				if (minichat_frame.then != 'undefined'){
					var minichat_frame1 = minichat_frame.then
					if ((typeof(minichat_frame1.success) != 'undefined') && minichat_frame1.success){
						$(".minichat_frame").append(minichat_frame1.resp.html)
					}
					if (minichat_frame1.then != 'undefined'){
						var conversationlist = minichat_frame1.then
						if ((typeof(conversationlist.success) != 'undefined') && conversationlist.success){
							var srcstr = '.minichat_frame1';
							if (typeof(minichat_payload.browser) != 'undefined') {srcstr += '[browser=' + minichat_payload.browser + ']';}
							if (typeof(minichat_payload.from) != 'undefined') {srcstr += '[from=' + minichat_payload.from + ']';}
							$(srcstr + ' .minichat-mesagelist').append(conversationlist.resp.html)
							.scrollTop($(srcstr + ' .minichat-mesagelist').prop('scrollHeight') - $(srcstr + ' .minichat-mesagelist').innerHeight())
							$('.minichat-start-button').css({display:'none'})
						}
					}
				}
				minichat_getconversationlist = false
				console.log('minichat_frame_promise1')
				resolve({pid:1})
			})
		}else{
			console.log('minichat_frame_promise2')
			resolve({})
		}
	})
}
minichat_frame1 = function(minichat_frame1_param){
	return new Promise(function(resolve,reject){
		var srcstr = '.minichat_frame1';
		if (typeof(minichat_payload.browser) != 'undefined') {srcstr += '[browser=' + minichat_payload.browser + ']';}
		if (typeof(minichat_payload.from) != 'undefined') {srcstr += '[from=' + minichat_payload.from + ']';}
		console.log(srcstr)
		if ($(srcstr).length == 0){
			cs('mesaje/minichat_frame1',{payload:minichat_payload,
			then:['mesaje/conversationlist',minichat_partenerkv]}).then(function(minichat_frame1){
				if ((typeof(minichat_frame1.success) != 'undefined') && minichat_frame1.success){
					$('.minichat_frame').append(minichat_frame1.resp.html)
				}
				if (minichat_frame1.then != 'undefined'){
					var conversationlist = minichat_frame1.then
					if ((typeof(conversationlist.success) != 'undefined') && conversationlist.success){
						$(srcstr + ' .minichat-mesagelist').append(conversationlist.resp.html)
						.scrollTop($(srcstr + ' .minichat-mesagelist').prop('scrollHeight') - $(srcstr + ' .minichat-mesagelist').innerHeight())
						$('.minichat-start-button').css({display:'none'})
					}
				}
				minichat_getconversationlist = false
				console.log('minichat_frame1_promise1')
				resolve({srcstr:srcstr})
			})
		}else{
			console.log('minichat_frame1_promise2')
			resolve({srcstr:srcstr,get:'get'})
		}
	})
}
minichat_conversationlist = function(minichat_conversationlist_param){
	return new Promise(function(resolve,reject){
		if (minichat_getconversationlist){
			cs('mesaje/conversationlist',minichat_partenerkv).then(function(conversationlist){
				if ((typeof(conversationlist.success) != 'undefined') && conversationlist.success){
					$(minichat_conversationlist_param.srcstr + ' .minichat-mesagelist').empty().append(conversationlist.resp.html)
					.scrollTop($(minichat_conversationlist_param.srcstr + ' .minichat-mesagelist').prop('scrollHeight') - $(minichat_conversationlist_param.srcstr + ' .minichat-mesagelist').innerHeight())
					$('.minichat-start-button').css({display:'none'})
				}
				console.log('conversationlist_promise1')
				resolve()
			})
		}else{
			console.log('conversationlist_promise2')
			resolve()
		}
	})
}
minichat_chatclose_click = function(payload){
	$('.minichat_frame1[' + payload.pk + '=' + payload.pv + ']').remove()
	localStorage.setItem('minichat_frame',$('.minichat_frame')[0].outerHTML)
	if ($('.minichat_frame1').length == 0){
		if ($('.minichat-start-button').length > 0){
			$('.minichat-start-button').css({display:'block'})
		}	
	}
}
minichat_chatminimize_click = function(payload){
	$('.minichat_frame1[' + payload.pk + '=' + payload.pv + ']').css({top:'270px'})
	localStorage.setItem('minichat_frame',$('.minichat_frame')[0].outerHTML)
}
minichat_chatrestore_click = function(payload){
	$('.minichat_frame1[' + payload.pk + '=' + payload.pv + ']').css({top:'0'})
	localStorage.setItem('minichat_frame',$('.minichat_frame')[0].outerHTML)
}
minichat_send_click = function(form){
	console.log(form)
	var formdata = new FormData(form)
	//var text = formdata.get('text')
	//var pk = formdata.get('pk')
	//var pv = formdata.get('pv')
	var text = form.querySelector('input[name=text]').value
	var pk = form.querySelector('input[name=pk]').value
	var pv = form.querySelector('input[name=pv]').value
	var payload = {}
	var p_arr = {}
	payload[pk] = pv
	p_arr[pk] = pv
	payload.text = text
	p_arr.text = text
	p_arr.then = ['mesaje/conversationlist',payload]

	if (text != ''){
		var mesagelist = $(form).parent().parent().find('.minichat-mesagelist');
		mesagelist.append(
			$('<div/>',{
				class:'row mesaje_sent',
			}).append(
				$('<div/>',{
					class:'col-xs-12',
				}).append(
					$('<span/>',{
						class:'mesaje_text',
						html:text ,
					})
				)
			)
		)
		.scrollTop(mesagelist.prop('scrollHeight') - mesagelist.innerHeight())
		cs("mesaje/send",p_arr).then(function(mesaje_send){
			console.log(mesaje_send)
			if (typeof(mesaje_send.then)!='undefined'){
				var mesaje_conversationlist = mesaje_send.then
				if ((typeof(mesaje_conversationlist.success)!='undefined') && (mesaje_conversationlist.success == true)){
					mesagelist
						.empty()
						.append(mesaje_conversationlist.resp.html)
						.scrollTop(mesagelist.prop('scrollHeight') - mesagelist.innerHeight())
					localStorage.setItem('minichat_frame',$('.minichat_frame')[0].outerHTML)
				}
			}
		})
		$(form).parent().parent().find('input[name=text]').val('')
		/*
		$('#mesaje_chat_form input[name=text]').val('')
		*/
	}

}