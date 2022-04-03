programari_monthcheck = null;
programari_daycheck = null;
vanillaCalendar.redraw({
	onclick:programari_calendaronclick,
	onmonthchange:function(){$('#interval_list').empty();programari_calendarpaint()},
	initdate:sqlToJsDate(programari_cautadate),
	selectdate:sqlToJsDate(programari_cautadate),
})
programari_calendarpaint({then:function(d){console.log(d)}})
$('#programari_selectora').on('change',function(){
	$('.programari_submit').css({display:''})
})
function programari_calendarpaint(p_arr){
	//$('#interval_list').empty()
	$('.programari_submit').css({display:'none'})
	cs('programari/monthcheck',{
		doctor:programari_doctor_id,
		spital:programari_spital_id,
		y:vanillaCalendar.date.getFullYear(),
		m:vanillaCalendar.date.getMonth() + 1
	}).then(function(programari_monthcheck){
		var datenow = new Date()
		datenow = new Date(datenow.getFullYear(),datenow.getMonth(),datenow.getDate())
		var caldays = $('#v-cal div.vcal-date');
		if (typeof(programari_monthcheck.success) != 'undefined' && programari_monthcheck.success == true){
			window.programari_monthcheck = programari_monthcheck;
			//console.log(programari_monthcheck)
			programari_monthcheck.resp.map(function(day,dayindex){
				var datecurrent = new Date(vanillaCalendar.date.getFullYear(),vanillaCalendar.date.getMonth(),dayindex + 1)
				if (datecurrent.getTime() >= datenow.getTime()){
					if (typeof(day.status) != 'undefined'){
						$(caldays[dayindex]).addClass(day.status)
						if (day.status == 'epuizat'){
							$(caldays[dayindex]).addClass('vcal-date--disabled')
							caldays[dayindex].removeEventListener('click', vcal_date_click_func);
						}
					}else{
						$(caldays[dayindex]).addClass('vcal-date--disabled')
						caldays[dayindex].removeEventListener('click', vcal_date_click_func);
					}
				}
			})
			
		}
		if ((typeof(p_arr) != 'undefined') && (typeof(p_arr.then) == 'function')){
			p_arr.then(programari_monthcheck)
		}
	})
}
function programari_calendaronclick(date){
	programari_cautadate = date.getFullYear()
		+ '-' + (date.getMonth() + 1)
		+ '-' + date.getDate()
	$('#interval_list').empty()
	cs('programari/daycheck',{
		spital:programari_spital_id,
		doctor:programari_doctor_id,
		y:date.getFullYear(),
		m:date.getMonth() + 1,
		d:date.getDate()
	}).then(function(programari_daycheck){
		//console.log(programari_daycheck)
		if (typeof(programari_daycheck.success) != 'undefined' && programari_daycheck.success == true){
			window.programari_daycheck = programari_daycheck;
			$('#interval_list').append(programari_daycheck.resp.map(function(interval,intervali){
				var datestart = sqlToJsDate(interval.start)
				var datestop = sqlToJsDate(interval.stop)
				var datenow = new Date();
				if (datenow.getTime() > datestart.getTime()){
					return 
				}
				var option = {
					href:'javascript:void(0)',
					value:intervali,
					class:'interval_item status_' + interval.status,
					text: ("0" + datestart.getHours()).slice(-2) + ':' + ("0" + datestart.getMinutes()).slice(-2) 
				}
				if (interval.status == 'disponibil') option.click = function(){
					//console.log({
					finalizaezaprogramare({
						doctor:programari_doctor_id,
						spital:programari_spital_id,
						specializare:programari_specializari_id,
						y:datestart.getFullYear(),
						m:datestart.getMonth() + 1,
						d:datestart.getDate(),
						h:datestart.getHours(),
						i:datestart.getMinutes(),
					})
				}
				return $('<a/>',option)
			}))
		}
	})
}
function programari_aplicaonclick(){
	if ($('#programari_selectora').val() === null) return
	if (typeof(programari_daycheck.resp[$('#programari_selectora').val()]) == 'undefined') return
	if ($('#programari_nume').val() === '') {alert('numele este obligatoriu'); $('#programari_nume').focus(); return;}
	var payload = {
		start:programari_daycheck.resp[$('#programari_selectora').val()].start,
		stop:programari_daycheck.resp[$('#programari_selectora').val()].stop,
		doctor:programari_doctor_id,
		nume:$('#programari_nume').val(),
	} 
	cs('programari/setappoint',payload).then(function(programari_setappoint){
		console.log(programari_setappoint)
		if (typeof(programari_setappoint.success) != 'undefined' && programari_setappoint.success == true){
			alert('felicitari progrmare creeata')
			window.location.reload()
		}
	})
}