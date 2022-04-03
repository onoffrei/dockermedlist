document.addEventListener("DOMContentLoaded", function(d) {
	$(".detailViewImg").hover(function() {$('.zoomerGlass').css('display','block');});
	$(".detailViewImg").mouseleave(function() {$('.zoomerGlass').css('display','none');});
	$('.detailViewImg').click(function(){StartGallery();});
	$('.zoomerGlass').click(function(){StartGallery();});

	if (typeof(maxImgHeightImgWidth) != 'undefined') $(window).bind("resize", ScaleSlider);

	$(function () {
		$('.ChangeImageNext').click(function(){
			GoToPicture(parseInt($('.detailViewImg').attr('currentpic')) + 1);
		});
		$('.ChangeImagePrev').click(function(){
			GoToPicture(parseInt($('.detailViewImg').attr('currentpic')) - 1);
		});
		$('.detailthumbs img').click(function(){
			GoToPicture($('.detailthumbs img').index(this));
		});
		if (typeof(maxImgHeightImgWidth) != 'undefined') ScaleSlider();
	});

})
var ImgHeightFinal = 0;
var standardHeight = 0;
var procentaj = 0;
var pswpElement = document.querySelectorAll('.pswp')[0];
function ScaleSlider(){
	standardHeight = parseInt($('.view_content').width()) * 0.75;
	if($('.view_content').width() < maxImgHeightImgWidth){
		procentaj = $('.view_content').width() * 100 / maxImgHeightImgWidth;
		ImgHeightFinal = maxImgHeight * procentaj / 100;
	}else ImgHeightFinal = maxImgHeight;

	if(standardHeight < ImgHeightFinal){
		ImgHeightFinal = standardHeight
	}

	$('.imgArrayL').attr('style','top:' + (ImgHeightFinal - 55)/2 + 'px;');
	$('.imgArrayR').attr('style','top:' + (ImgHeightFinal - 55)/2 + 'px;');
	$('.zoomerGlass').attr('style','top:' + (ImgHeightFinal - 55)/2 + 'px;');
	$('.imgZone').attr('style','height:' + ImgHeightFinal + 'px;width:' + $('.view_content').width() + 'px;line-height:' + ImgHeightFinal + 'px;');
	$('.detailViewImg').attr('style','max-height:' + ImgHeightFinal + 'px;max-width:' + $('.view_content').width() + 'px;');

	var maxVisibleThumbsS = Math.floor($('.thumbZone').width() / $('.detailthumbs li').outerWidth(true)) - 1;

	$('.detailthumbs li').each(function(index, item) {
		if(Math.floor(($('#imageViewNumber').text() - 1) / maxVisibleThumbsS) < ((index + 1)  / maxVisibleThumbsS) &&  ((index + 1) / maxVisibleThumbsS) <= (Math.floor(($('#imageViewNumber').text()-1) / maxVisibleThumbsS) + 1))
			$('.detailthumbs li').eq(index).show();
		else
			$('.detailthumbs li').eq(index).hide();
	});
}

function GoToPicture(currentImageNumber){
	var maxVisibleThumbs = Math.floor($('.thumbZone').width() / $('.detailthumbs li').outerWidth(true)) -1;

	if(currentImageNumber < 0)
		currentImageNumber = imageList.length - 1;
	if (currentImageNumber > imageList.length - 1)
		currentImageNumber = 0;
	$('.detailViewImg').attr('currentpic', currentImageNumber);
	$('.detailViewImg').removeAttr("srcset");
	$('.detailViewImg').attr("src", imageList[currentImageNumber].src);
	$('#imageViewNumber').html(currentImageNumber+1);

	$('.detailthumbs li').css('border-color', 'white');
	$('.detailthumbs li').eq(currentImageNumber).css('border-color', '#ddd');

	$('.detailthumbs li').each(function(index, item) {


		if(Math.floor(($('#imageViewNumber').text() - 1) / maxVisibleThumbs) < ((index + 1)  / maxVisibleThumbs) &&  ((index + 1) / maxVisibleThumbs) <= (Math.floor(($('#imageViewNumber').text()-1) / maxVisibleThumbs) + 1))
			$('.detailthumbs li').eq(index).show();
		else
			$('.detailthumbs li').eq(index).hide();
	});
}

var gallery = null;
function StartGallery() {
	// define options (if needed)
	var options = {
		// history & focus options are disabled on CodePen
		index: parseInt($('.detailViewImg').attr('currentpic')),
		history: true,
		shareEl: false,
		focus: false,
		tapToClose: false,
		showAnimationDuration: 0,
		hideAnimationDuration: 0,
		preload: [1, 2],
		pinchToClose: false,
		closeOnScroll: false,
		closeOnVerticalDrag: false,
		fullscreenEl: false,
		zoomEl: false //disable zoom
	};

	// Initializes and opens PhotoSwipe
	gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, imageList, options);
	gallery.init();

	gallery.listen('destroy', function() {
		GoToPicture(gallery.getCurrentIndex());
	});

}
