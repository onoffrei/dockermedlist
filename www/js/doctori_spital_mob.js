document.addEventListener("DOMContentLoaded", function(d) {
		imgs = $(".imgZone a ul");
		$(".imgZone").swipe(swipeOptions);
		if (typeof(imageList) != 'undefined')
		ScaleSlider();
        $('.imgZone').on("click", function(){
            StartGallery();
        });
		$(window).bind("resize", ScaleSlider);

})
var imgs;
var currentImg = 0;
var pswpElement = document.querySelectorAll('.pswp')[0];
var gallery = null;
if (typeof(imageList) != 'undefined')
var maxImages = imageList.length;
var swipeOptions = {
	triggerOnTouchEnd: true,
	swipeStatus: swipeStatus,
	allowPageScroll: "vertical",
	threshold: 5,
	excludedElements: ""
};
function StartGallery() {
	// define options (if needed)
	var options = {
		// history & focus options are disabled on CodePen
		index: currentImg,
		history: true,
		shareEl: false,
		focus: false,
		tapToClose: false,
		showAnimationDuration: 0,
		hideAnimationDuration: 0,
		preload: [1, 2],
		pinchToClose: false,
		closeOnScroll: false,
		closeOnVerticalDrag: true,
		fullscreenEl: false,
		zoomEl: false //disable zoom
	};

	// Initializes and opens PhotoSwipe
	gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, imageList, options);
	gallery.init();
}
function ScaleSlider(){
	if (typeof(imageList) == 'undefined') return
	IMG_WIDTH = $(".detailViewImg").width();
	$('#gallery a li').css('width', 100/imageList.length + '%');
	$('#gallery a ul').css('width', 100*imageList.length + '%');
}

function GoToPicture(currentImageNumber){

	if(currentImageNumber < 0)
		currentImageNumber = imageList.length - 1;
	if (currentImageNumber > imageList.length - 1)
		currentImageNumber = 0;
	$('.detailViewImg').attr('currentpic', currentImageNumber);
	$('.detailViewImg').removeAttr("srcset");
	$('.detailViewImg').css("background", "url('" + imageList[currentImageNumber].src + "') no-repeat center");
	$('#imageViewNumber').html(currentImageNumber+1);
}



function swipeStatus(event, phase, direction, distance) {
	//If we are moving before swipe, and we are going L or R in X mode, or U or D in Y mode then drag.
	if (phase == "move" && (direction == "left" || direction == "right")) {
		var duration = 0;
		if (direction == "left") {
			scrollImages((IMG_WIDTH * currentImg) + distance, duration);
		} else if (direction == "right") {
			scrollImages((IMG_WIDTH * currentImg) - distance, duration);
		}
	} else if (phase == "cancel") {
		scrollImages(IMG_WIDTH * currentImg, speed);
	} else if (phase == "end") {
		if (direction == "right") {
			previousImage();
		} else if (direction == "left") {
			nextImage();
		}
	}
}

function previousImage() {
	if(currentImg >= 1)
		$('#imageViewNumber').html(currentImg);
	currentImg = Math.max(currentImg - 1, 0);

	$('.imgArrayR').show();
	if(currentImg == 0)
		$('.imgArrayL').hide();

	scrollImages(IMG_WIDTH * currentImg, speed);

}
function nextImage() {
	if(currentImg+2 <= imageList.length)
		$('#imageViewNumber').html(currentImg+2);
	currentImg = Math.min(currentImg + 1, maxImages - 1);

	$('.imgArrayL').show();
	if(currentImg+1 == imageList.length)
		$('.imgArrayR').hide();

	scrollImages(IMG_WIDTH * currentImg, speed);
	if(imageList.length > currentImg + 1)
	{
		$(".imgZone ul").append('<li style="width: ' + 100/imageList.length + '% ; background:url(\'' + imageList[currentImg + 1].src + '\') no-repeat center;" class="detailViewImg" itemprop="image"></li>');
	}

}
        /**
         * Manually update the position of the imgs on drag
         */
function scrollImages(distance, duration) {
	imgs.css("transition-duration", (duration / 1000).toFixed(1) + "s");
	//inverse the number we set in the css
	var value = (distance < 0 ? "" : "-") + Math.abs(distance).toString();
	imgs.css("transform", "translate(" + value + "px,0)");
}
function view_phone_enable(){
	if (typeof($('.view_phone_call').attr('href')) == 'undefined') {	
		$('.view_phone_call').attr('href','tel:' + atob($('.view_phone_call').attr('data')))
		$('.view_phone_sms').attr('href','sms:' + atob($('.view_phone_sms').attr('data')))
	}
}