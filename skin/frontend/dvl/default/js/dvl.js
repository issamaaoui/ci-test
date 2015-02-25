var $j = jQuery.noConflict();

$j(document).ready(function(){
  resizeDiv();
});

$j(window).on("orientationchange",function(){
	  resizeDiv();
});

window.onresize = function(event) {
	  if( !(/Android/i.test(navigator.userAgent)) ) {
	    resizeDiv();
	  }
}

function resizeDiv() {
    vpw = $j(window).width();
    vph = $j(window).height();
    $j("#prez-section").css("height", vph + "px");
    $j(".cms-home .std .navbar").data("offset-top", vph);
}