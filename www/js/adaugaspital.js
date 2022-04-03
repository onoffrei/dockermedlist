document.addEventListener("DOMContentLoaded", function(){
	$("#spital_form").bootstrapValidator(spital_validschema)
	autocomplete(document.querySelector("#spital_form input[name=localitate_name]")
		, cs_url + "csapi/localitati/autocomplete?justsub=1&q="
		,function(id){
		if ($("#spital_form input[name=localitate]").val() != id){
			$("#spital_form input[name=localitate]").val(id)
			$("#spital_form").data('bootstrapValidator').resetForm()
			$("#spital_form").data('bootstrapValidator').validate()
			
		}
	})
	qeditor = new Quill('#mytextarea', {
		modules: {
			toolbar: [
				[
					{ 'font': [] },
					{ 'size': [] },
				],
				['bold', 'italic', 'underline'],
				[
					{'color': []},
					{'background': []},
				],
				[{ 'align': [] }],
				['link'],
				[{ list: 'ordered' }, { list: 'bullet' }]
			]
		},
		placeholder: 'Descriere',
		theme: 'snow'
	});
})
spital_submit = function(form){
	$("#spital_form").data('bootstrapValidator').resetForm()
	$("#spital_form").data('bootstrapValidator').validate()
	if (!($("#spital_form").data('bootstrapValidator').isValid())) return
	setTimeout(function(){
		$("#spital_form").data('bootstrapValidator').resetForm()
		$('.spital_submit_button').attr('disabled','disabled')
	},500)
	$('.spital_submit_button').attr('disabled','disabled')
	if (typeof(cs_users_id) == 'undefined'){
		cs('users/login_rs',{activate:true,callback:'spital_user_resgister_callback'})
		return
	}else{
		var myform = document.querySelector('#spital_form')
		var myformData = new FormData(myform)
		myformData.set('descriere',document.querySelector(".ql-editor").innerHTML)
		cs('inscriere/adauga',myformData).then(function(){
			window.location.href = '/detaliispital'		
		})
	}
}
spital_user_resgister_callback = function(d){
	$("div.modal-backdrop.fade.in").remove()
	$('#users_register_htmlmodal').modal('hide');
	$('#users_login_htmlmodal').modal('hide');
	console.log(d)
	if ((typeof(d.success) == 'undefined') || (d.success != true)){
		alert('something went wrong' + JSON.stringify(d))
		$('.spital_submit_button').removeAttr('disabled')
		return		
	}
	var myform = document.querySelector('#spital_form')
	var myformData = new FormData(myform)
	myformData.set('descriere',document.querySelector(".ql-editor").innerHTML)
	cs('inscriere/adauga',myformData).then(function(){
		window.location.href = '/detaliispital'		
	})
	//spital_change();
}
var spital_validschema = {
	feedbackIcons: {
		valid: 'glyphicon glyphicon-ok',
		invalid: 'glyphicon glyphicon-remove',
		validating: 'glyphicon glyphicon-refresh'
	},
	fields: {
		nume: {
			validators: {
				notEmpty: {
					message: 'Titlul este obligatoriu'
				},
				stringLength: {
					min: 3,
					max: 100,
					message: 'Titlul trebuie sa fie alcatuit din cel putin 3 caractere si cel mult 100'
				}
			}
		},
		localitate_name: {
			container: '#spital_form span[name=localitate_span]',
			validators: {
				callback: {
					callback: function(value, validator, $field) {
						var error = {valid: false,message: 'Localitatea este obligatorie'}
						if ((parseInt($("#spital_form input[name=localitate]").val()) > 0)) return true
						return error
					}
				},
			},
		},
		adresa: {
			validators: {
				notEmpty: {
					message: 'Campul este obligatoriu'
				},
				stringLength: {
					min: 3,
					max: 100,
					message: 'Campul trebuie sa fie alcatuit din cel putin 3 caractere si cel mult 100'
				}
			}
		},
		Telefon: {
			validators: {
				notEmpty: {
					message: 'Campul este obligatoriu'
				},
				stringLength: {
					min: 5,
					max: 15,
					message: 'Campul trebuie sa fie alcatuit din cel putin 3 caractere si cel mult 15'
				}
			}
		},
	}
}
