cauta_specializari_onclick = function(catid){
	console.log(catid)
	var opt = {
		callback:'cauta_specializari_onalege',
		active:'active',
	};
	if (cauta_localitate_id != null) opt.localitati = cauta_localitate_id
	if (cauta_specializare_id != null) opt.catid = cauta_specializare_id
	if (typeof(catid) != 'undefined') opt.catid = catid
	cs('cauta/specializari_inputchoose_rs',opt).then(function(cauta_specializari_inputchoose_rs){console.log(cauta_specializari_inputchoose_rs)})
}
cauta_localitati_onclick = function(locid){
	console.log(locid)
	var opt = {
		callback:'cauta_localitati_onalege',
		active:'active',
	};
	if (cauta_specializare_id != null) opt.specializari = cauta_specializare_id
	if (cauta_localitate_id != null) opt.locid = cauta_localitate_id
	if (typeof(locid) != 'undefined') opt.locid = locid
	cs('cauta/localitati_inputchoose_rs',opt).then(function(cauta_localitati_inputchoose_rs){console.log(cauta_localitati_inputchoose_rs)})
}
cauta_specializari_onalege = function(catid){
	cs('specializari/breadcrumb',{
		catid:catid,
		callback:'cauta_specializari_onclick',
	}).then(function(specializari_breadcrumb){
		if ((typeof(specializari_breadcrumb.success) != 'undefined') && (specializari_breadcrumb.success == true)){
			$("div.specializari_breadcrumb").html(specializari_breadcrumb.resp.html)
			cauta_specializari_breadcrumb_nodearr = specializari_breadcrumb.resp.nodearr
			cauta_specializari_breadcrumb_parentid = 0
			cauta_specializari_breadcrumb_parentparent = 0
			if (cauta_specializari_breadcrumb_nodearr.length > 0) cauta_specializari_breadcrumb_parentid = cauta_specializari_breadcrumb_nodearr[0]['id']
			if (cauta_specializari_breadcrumb_nodearr.length > 1) cauta_specializari_breadcrumb_parentparent = cauta_specializari_breadcrumb_nodearr[1]['id']
			cauta_specializare_id = cauta_specializari_breadcrumb_parentid
		}
	})
}
cauta_localitati_onalege = function(locid){
	cs('localitati/breadcrumb',{
		locid:locid,
		callback:'cauta_localitati_onclick',
	}).then(function(localitati_breadcrumb){
		if ((typeof(localitati_breadcrumb.success) != 'undefined') && (localitati_breadcrumb.success == true)){
			$("div.localitati_breadcrumb").html(localitati_breadcrumb.resp.html)
			cauta_localitati_breadcrumb_nodearr = localitati_breadcrumb.resp.nodearr
			cauta_localitati_breadcrumb_parentid = 0
			cauta_localitati_breadcrumb_parentparent = 0
			if (cauta_localitati_breadcrumb_nodearr.length > 0) cauta_localitati_breadcrumb_parentid = cauta_localitati_breadcrumb_nodearr[0]['id']
			if (cauta_localitati_breadcrumb_nodearr.length > 1) cauta_localitati_breadcrumb_parentparent = cauta_localitati_breadcrumb_nodearr[1]['id']
			cauta_localitate_id = cauta_localitati_breadcrumb_parentid
		}
	})
}
cauta_submit = function(){
	var uri_new = window.location.origin + '/doctori'
	var localitati_uri = '';
	if (cauta_localitati_breadcrumb_nodearr.length > 0){
		for (var i = cauta_localitati_breadcrumb_nodearr.length - 1; i >=0; i--){
			console.log(cauta_localitati_breadcrumb_nodearr[i].uri)
			uri_new += '/' + cauta_localitati_breadcrumb_nodearr[i].uri
		}
	}
	if (cauta_specializari_breadcrumb_nodearr.length > 0){
		for (var i = cauta_specializari_breadcrumb_nodearr.length - 1; i >=0; i--){
			console.log(cauta_specializari_breadcrumb_nodearr[i].uri)
			uri_new += '/' + cauta_specializari_breadcrumb_nodearr[i].uri
		}
	}
	//if (window.location.href != uri_new){
		//window.location.href = uri_new
		var formcauta = document.querySelector('#cauta')
		formcauta.action = uri_new
		formcauta.submit()
	//}
	
}