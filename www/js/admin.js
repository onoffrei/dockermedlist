function admin_upload_onchange(finput){
    if (('files' in finput) && (finput.files.length > 0)) {
		cs('planificare/upload',finput.files[0]).then(function(d){
			console.log(d)
			window.location.reload()
		})
		finput.value = ''
    }
}
function admin_planificare_edit_onclick(){
	admin_planificare_savebuttonsshow(false,true,true)
	$("#planificare_tabel_place .activeday").map(function(i,e){
		var eselect = $('<select/>',{
			class:'select-edit',
		})
		$(eselect).append($('<option/>',{
			value:'',
			text:'',
		}))
		$(eselect).append(legenda_grid.map(function(v){
			var option = {
				value:v.id,
				text:v.nume,
			}
			if (e.dataset.legendaid == v.id) option.selected = 'selected'
			return $('<option/>',option)
		}))
		$(e).html(eselect)
	})
}
function admin_planificare_cancel_onclick(){
	admin_planificare_savebuttonsshow(true,false,false)
	var dataset = $('#planificare_ymchoose_ym')[0].dataset
	update_planificare_tabel(dataset.m,dataset.y)
}
function admin_planificare_save_onclick(){
	var planificare = $('#planificare_tabel_place tr[data-doctorid]').map(function(rowi,row){
		
		console.log(row,rowi)
		return{
			doctorid:parseInt(row.dataset.doctorid),
			days:$(row).find('.activeday').map(function(tdi,td){
				var dataset = td.dataset
				return {
					d:parseInt(dataset.d),
					legendaid:$(td).find('select').val(),
					legenda:$(td).find('select option:selected').text(),
				}
			}).get()
		}
	}).get()
	var ym = $('#planificare_ymchoose_ym')[0].dataset
	var payload = {
		spital:spital_activ.id,
		m:ym.m,
		y:ym.y,
		planificare:planificare
	}
	console.log(payload)
	cs('planificare/save',payload).then(function(d){
		admin_planificare_savebuttonsshow(true,false,false)
		update_planificare_tabel(ym.m,ym.y)
	})
}
function admin_planificare_savebuttonsshow(edit,cancel,save){
	$('#admin_planificare_edit_btn').css({display:edit?'inline-block':'none'})
	$('#admin_planificare_cancel_btn').css({display:cancel?'inline-block':'none'})
	$('#admin_planificare_save_btn').css({display:save?'inline-block':'none'})
}
function admin_specializari_onclick(catid){
	cs('specializari/breadcrumb',{catid:catid,callback:'admin_specializari_onclick'}).then(function(specializari_breadcrumb){
		$("div.detail-right  div.specializari_breadcrumb").html(specializari_breadcrumb.resp.html)
		specializari_breadcrumb_nodearr = specializari_breadcrumb.resp.nodearr
		specializari_breadcrumb_parentid = 0
		specializari_breadcrumb_parentparent = 0
		if (specializari_breadcrumb_nodearr.length > 0) specializari_breadcrumb_parentid = specializari_breadcrumb_nodearr[0]['id']
		if (specializari_breadcrumb_nodearr.length > 1) specializari_breadcrumb_parentparent = specializari_breadcrumb_nodearr[1]['id']
	})
	cs('specializari/list_a',{catid:catid,callback:'admin_specializari_onclick'}).then(function(specializari_list_a){
		//$("div.detail-right div.list-group").html(specializari_list_a.resp.html)
		$("div.detail-right div.list-group").empty()
		console.log(specializari_list_a.resp.specializari_grid.resp)
		$("div.detail-right div.list-group").append(specializari_list_a.resp.specializari_grid.resp.rows.map(function(d,i){
			var inputup = $('<button/>',{
				html:'<i class="fa fa-arrow-up" aria-hidden="true"></i>',
				class:'btn btn-info',
				click:function(e){
					e.preventDefault()
					e.stopPropagation()
					cs("specializari/sortorderchange",{id:d.id,newval:parseInt(d.sorder)-1}).then(function(t){
						admin_specializari_onclick(specializari_breadcrumb_parentid)
					})
					return false
				},
			})
			var inputdown = $('<button/>',{
				html:'<i class="fa fa-arrow-down" aria-hidden="true"></i>',
				class:'btn btn-warning',
				click:function(e){
					e.preventDefault()
					e.stopPropagation()
					cs("specializari/sortorderchange",{id:d.id,newval:parseInt(d.sorder)+1}).then(function(t){
						admin_specializari_onclick(specializari_breadcrumb_parentid)
					})
					return false
				},
			})
			var ediv = $('<div/>',{
				class:'list-group-item',
				click:function(){admin_specializari_onclick(d.id)}
			})
			ediv.append(inputup)
			ediv.append(inputdown)
			ediv.append(' ' + d.denumire)
			return ediv
		}))
	})
}
