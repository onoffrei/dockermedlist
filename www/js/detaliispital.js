function detaliispital_image_onchange(finput){
    if (('files' in finput) && (finput.files.length > 0)) {
		for (var i = 0; i < finput.files.length; i++) {
			detaliispital_image_draw(finput,i)
		}
		detaliispital_uploadall(finput,0)
		$('.spitale_images_adauga_imagine_button').attr('disabled','disabled')
		/*
		$('.modifica_anunt_submit_button').attr('disabled','disabled')
		$('.modifica_anunt_sterge_button').attr('disabled','disabled')
		*/
    }
}
function detaliispital_image_draw(finput,i){
	//console.log(file)
	var reader = new FileReader();
	var image = $("<img>").addClass("image")
	var fa_loading	 		= $("<span></span>").addClass("fa fa-upload")
	var fa_error	 		= $("<span></span>").addClass("fa fa-refresh")
	var fa_rotate_left 		= $("<span></span>").addClass("fa fa-rotate-left")
	var fa_trash 			= $("<span></span>").addClass("fa fa-trash-o")
	var fa_rotate_right 	= $("<span></span>").addClass("fa fa-rotate-right")
	var option_loading	 	= $("<a></a>").addClass("loading").append(fa_loading).append(" Loading...")
	var option_error	 	= $("<a></a>").addClass("error").append(fa_error).append(" Error").attr('onclick','detaliispital_retry(this,event)')
	var option_rotate_left 	= $("<a></a>").addClass("option").append(fa_rotate_left).attr('onclick','detaliispital_rotate(this,event,90)')
	var option_trash 		= $("<a></a>").addClass("option").append(fa_trash).attr('onclick','detaliispital_delete(this,event)')
	var option_rotate_right = $("<a></a>").addClass("option").append(fa_rotate_right).attr('onclick','detaliispital_rotate(this,event,270)')
	var options = $("<div></div>").addClass("options").append(option_loading).append(option_error).append(option_rotate_left).append(option_trash).append(option_rotate_right)
	var wrapper = $("<div></div>").addClass("wrapper").addClass("loading").append(image).append(options).attr('findex',i)
	$(".spitale_images_container .sortable").append(wrapper)
	reader.onload = function (e) {
		image[0].src = e.target.result;
	}
	reader.readAsDataURL(finput.files[i]);
}
function detaliispital_uploadall(finput,index){
	cs('spitale_images/add?spital=' + spital_activ.id,finput.files[index]).then(function(t){
		console.log(t)
		var wrapper = $(".spitale_images_container .wrapper[findex=" + index + "]")
		wrapper.removeAttr("findex")
		wrapper.removeClass("loading")
		if ((typeof(t.success) == 'undefined')||(t.success != true)){
			wrapper.addClass("error")
		}else{
			wrapper.attr('imgid',t.resp.id)
			wrapper.find("img.image").attr('src',cs_url + 'csapi/images/view/?thumb=0&id=' + t.resp.id)
		}
		if (index + 1 < finput.files.length){
			detaliispital_uploadall(finput,index + 1)
		}else{
			$('.spitale_images_adauga_imagine_button').removeAttr('disabled')
			$('.modifica_anunt_submit_button').removeAttr('disabled')
			$('.modifica_anunt_sterge_button').removeAttr('disabled')
		}
	})
}
function detaliispital_retry(el,ev){
	var $wrapper = $(el).closest('.wrapper')
	var $img = $wrapper.find('img.image')
	//console.log($img.attr('src'))
	var image = convertCanvasToImage(convertImageToCanvas($img[0]))
	cs('spitale_images/add?spital=' + spital_activ.id,image.src).then(function(t){
		console.log(t)
		if ((typeof(t.success) == 'undefined')||(t.success != true)){
		}else{
			$wrapper.removeClass("error")
			$wrapper.attr('imgid',t.resp.id)
			$wrapper.find("img.image").attr('src',cs_url + 'csapi/images/view/?thumb=0&id=' + t.resp.id)
		}
	})
}
function convertImageToCanvas(image) {
	var canvas = document.createElement("canvas");
	canvas.width = image.width;
	canvas.height = image.height;
	canvas.getContext("2d").drawImage(image, 0, 0);

	return canvas;
}
function convertCanvasToImage(canvas) {
	var image = new Image();
	image.src = canvas.toDataURL("image/jpeg");
	return image;
}
function detaliispital_delete(el,ev){
	var $wrapper = $(el).closest('.wrapper')
	cs('spitale_images/delete_js',{image:$wrapper.attr('imgid')}).then(function(d){
		if ((typeof(d.success) == 'undefined')||(d.success != true)){
			alert('ups')
		}else{
			$wrapper.remove()
		}
	})
}
