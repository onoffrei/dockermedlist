planifauto_save_onclick = function(){
	var payload = {personal:[]}
	$("#planifauto_tabel_place table tr").map(function(i,e){
		if ((typeof(e.dataset.doctorid) != 'undefined') && (parseInt(e.dataset.doctorid) > 0)){
			var persoana = {
				doctor:parseInt(e.dataset.doctorid),
				spital:parseInt(e.dataset.spital),
				activ:$(e).find('input[name=activ]')[0].checked,
				week:[]
			}
			for (var dow = 1; dow <= 7; dow++){
				var start = $(e).find('select[name=start_' + dow + ']').val()
				var stop = $(e).find('select[name=stop_' + dow + ']').val()
				if (start != '' && stop != ''){
					persoana.week[persoana.week.length] = {
						dow:dow,
						start:start,
						stop:stop,
					}
				}
			}
			payload.personal[payload.personal.length] = persoana
		}
	})
	console.log(payload)
	cs('planifauto/set',payload).then(function(set){
		console.log(set)
	})
	/*
	cs('planifauto/generate',payload).then(function(generate){
		console.log(generate)
	})
	*/
	
}
planifauto_generate_onclick = function(){
	var payload = {personal:[]}
	$("#planifauto_tabel_place table tr").map(function(i,e){
		if ((typeof(e.dataset.doctorid) != 'undefined') && (parseInt(e.dataset.doctorid) > 0)){
			var persoana = {
				doctor:parseInt(e.dataset.doctorid),
				spital:parseInt(e.dataset.spital),
				activ:$(e).find('input[name=activ]')[0].checked,
				week:[]
			}
			for (var dow = 1; dow <= 7; dow++){
				var start = $(e).find('select[name=start_' + dow + ']').val()
				var stop = $(e).find('select[name=stop_' + dow + ']').val()
				if (start != '' && stop != ''){
					persoana.week[persoana.week.length] = {
						dow:dow,
						start:start,
						stop:stop,
					}
				}
			}
			payload.personal[payload.personal.length] = persoana
		}
	})
	console.log(payload)
	/*
	cs('planifauto/set',payload).then(function(set){
		console.log(set)
	})
	*/
	cs('planifauto/generate',payload).then(function(generate){
		console.log(generate)
	})
	
}
planifauto_sg_onclick = function(){
	var payload = {personal:[]}
	$("#planifauto_tabel_place table tr").map(function(i,e){
		if ((typeof(e.dataset.doctorid) != 'undefined') && (parseInt(e.dataset.doctorid) > 0)){
			var persoana = {
				doctor:parseInt(e.dataset.doctorid),
				spital:parseInt(e.dataset.spital),
				activ:$(e).find('input[name=activ]')[0].checked,
				week:[]
			}
			for (var dow = 1; dow <= 7; dow++){
				var start = $(e).find('select[name=start_' + dow + ']').val()
				var stop = $(e).find('select[name=stop_' + dow + ']').val()
				if (start != '' && stop != ''){
					persoana.week[persoana.week.length] = {
						dow:dow,
						start:start,
						stop:stop,
					}
				}
			}
			payload.personal[payload.personal.length] = persoana
		}
	})
	console.log(payload)
	cs('planifauto/set',{
		personal:payload.personal,
		then:['planifauto/generate',{
			personal:payload.personal,
		}]
	}).then(function(set){
		console.log(set)
	})
	
}