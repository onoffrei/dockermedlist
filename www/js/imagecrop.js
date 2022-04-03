imagecrop_parr ={
	jcrop_api:null,
	canvas:null,
	context:null,
	image:null,
	prefsize:null,
	boxWidth:450,
	boxHeight:450,
	aspectRatio:1,
};
imagecrop_start = function(p_arr){
	if ($(window).width() < imagecrop_parr.boxWidth){
		imagecrop_parr.boxWidth = $(window).width() - 50
		imagecrop_parr.boxHeight = $(window).width() - 50
	}
	imagecrop_parr.then = p_arr.then
	var modal = imagecrop_modalcreate()
	modal.modal('show')
	imagecrop_loadImage(p_arr.strDataURI)
}
imagecrop_loadImage =function(strDataURI){
	imagecrop_parr.canvas = null;
	imagecrop_parr.image = new Image();
	
	imagecrop_parr.image.onload = imagecrop_validateImage;
	imagecrop_parr.image.src = strDataURI;
}
imagecrop_validateImage = function(){
	if (imagecrop_parr.canvas != null) {
		imagecrop_parr.image = new Image();
		imagecrop_parr.image.onload = imagecrop_restartJcrop;
		imagecrop_parr.image.src = imagecrop_parr.canvas.toDataURL('image/png');
	} else imagecrop_restartJcrop();
}
imagecrop_dataURLtoBlob = function(dataURL){
	var BASE64_MARKER = ';base64,';
	if (dataURL.indexOf(BASE64_MARKER) == -1) {
		var parts = dataURL.split(',');
		var contentType = parts[0].split(':')[1];
		var raw = decodeURIComponent(parts[1]);
		return new Blob([raw], {
			type: contentType
		});
	}
	var parts = dataURL.split(BASE64_MARKER);
	var contentType = parts[0].split(':')[1];
	var raw = window.atob(parts[1]);
	var rawLength = raw.length;
	var uInt8Array = new Uint8Array(rawLength);
	for (var i = 0; i < rawLength; ++i) {
		uInt8Array[i] = raw.charCodeAt(i);
	}

	return new Blob([uInt8Array], {
		type: contentType
	});
}
imagecrop_restartJcrop = function() {
	if (imagecrop_parr.jcrop_api != null) {
		imagecrop_parr.jcrop_api.destroy();
	}
	$("#imagecrop_imagecontainer").empty();
	//$("#imagecrop_imagecontainer").append("<canvas id=\"imagecrop_canvas\" >");
	$("#imagecrop_imagecontainer").append(
		$('<div/>',{
			id:'imagecrop_canvascontainer', 
			style:'width:' + imagecrop_parr.image.width + 'px;'
					+'margin-left: auto;margin-right: auto;'
					,
		}).append(
			$("<canvas/>",{
				id:'imagecrop_canvas', 
				style:'width:' + imagecrop_parr.image.width +'px;'
					+'height:'+imagecrop_parr.image.height+'px;'
					,
			})
		)
	)
	console.log(imagecrop_parr.image.width)
	imagecrop_parr.canvas = $("#imagecrop_canvas")[0];
	imagecrop_parr.context = imagecrop_parr.canvas.getContext("2d");
	imagecrop_parr.canvas.width = imagecrop_parr.image.width;
	imagecrop_parr.canvas.height = imagecrop_parr.image.height;
	var max = (imagecrop_parr.image.width > imagecrop_parr.image.height ? imagecrop_parr.image.height : imagecrop_parr.image.width)
	imagecrop_parr.prefsize = {
		x: 10,
		y: 10,
		w: max-10,
		h: max - 10
	}
	imagecrop_parr.context.drawImage(imagecrop_parr.image, 0, 0);
	$("#imagecrop_canvas").Jcrop({
		aspectRatio: imagecrop_parr.aspectRatio,
		setSelect: [10, 10, max - 10, max - 10],
		onSelect: imagecrop_selectcanvas,
		onRelease: imagecrop_clearcanvas,
		boxWidth: imagecrop_parr.boxWidth,
		boxHeight: imagecrop_parr.boxHeight,
	}, function () {
		imagecrop_parr.jcrop_api = this;
		$("#imagecrop_canvascontainer").width($("#imagecrop_canvas").width())
		$("#imagecrop_canvascontainer").css({
			'max-height':$("#imagecrop_canvas").height() + 'px'
		})
	});
	//clearcanvas();
}
imagecrop_selectcanvas = function(coords){
	imagecrop_parr.prefsize = {
		x: Math.round(coords.x),
		y: Math.round(coords.y),
		w: Math.round(coords.w),
		h: Math.round(coords.h)
	};
	console.log(imagecrop_parr.prefsize)
}
imagecrop_clearcanvas =function(){
	imagecrop_parr.prefsize = {
		x: 0,
		y: 0,
		w: imagecrop_parr.canvas.width,
		h: imagecrop_parr.canvas.height,
	};
	console.log(imagecrop_parr.prefsize)
}
imagecrop_applyCrop=function(){
	imagecrop_parr.canvas.width = imagecrop_parr.prefsize.w;
	imagecrop_parr.canvas.height = imagecrop_parr.prefsize.h;
	imagecrop_parr.context.drawImage(
		imagecrop_parr.image, 
		imagecrop_parr.prefsize.x, 
		imagecrop_parr.prefsize.y, 
		imagecrop_parr.prefsize.w, 
		imagecrop_parr.prefsize.h, 
		0, 
		0, 
		imagecrop_parr.canvas.width, 
		imagecrop_parr.canvas.height);
	imagecrop_validateImage();
}
imagecrop_modalcreate = function(){
	var modal = $('<div/>',{
		id:'imagecrop_modal',
		class:'modal fade',
		tabindex:'-1',
		role:'dialog',
	}).append(
		$('<div/>',{
			class:'modal-dialog modal-md',
		}).append(
			$('<div/>',{
				class:'modal-content',
			}).append(
				$('<div/>',{
					class:'modal-header',
				}).append(
					$('<button/>',{
						type:'button',
						class:'close',
						'data-dismiss':'modal',
						'aria-label':'Close',
					}).append($('<span/>',{
							'aria-hidden':true,
							html:'&times;',
						})
					),
					$('<h4/>',{
						class:'modal-title',
						html:'imagecrop',
					})
				),
				$('<div/>',{
					class:'modal-body',
				}).append(
					$('<div/>',{
						class:'row',
					}).append(
						$('<div/>',{
							id:'imagecrop_imagecontainer',
							class:'col-xs-12',
							//style:'margin:0 auto 0 auto',
						})
					)
				),
				$('<div/>',{
					class:'modal-footer',
				}).append(
						$('<button/>',{
							class:'btn btn-success btn-block',
							html:'Salveaza',
							click:function(e){
								e.target.disabled = true
								imagecrop_applyCrop()
								imagecrop_parr.then(imagecrop_dataURLtoBlob(imagecrop_parr.canvas.toDataURL('image/jpeg')))
								modal.modal('hide')
							},
						})
					)
			)
		)
	)
	$('#imagecrop_modal').remove()
	$(document.body).append(modal);
	return modal
}