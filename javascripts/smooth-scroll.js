// JavaScript Document
$(document).ready(function() {

	var device = navigator.userAgent.toLowerCase();
	var ios = device.match(/(iphone|ipod|ipad)/);

 //function that returns element's y position
    
    $("a[href*=#]").on('click', function() {
    	var scrollTarget = $(this.hash).offset().top;
      	if(parseInt(scrollTarget) !== parseInt($(window).scrollTop())) {
      		var intro = $("#intro"),
      			nav2 = $(".nav2");
    		if (ios) nav2.hide();
	        $('html,body').animate({scrollTop:scrollTarget}, 1500, function(evt) {
		    	if (ios) {
		    		nav2.css({position:'absolute', top:scrollTarget + 34});
		    		var nav2clone = $(".nav2")
		    		nav2clone.show();
		    	}
			});
		}
    });

    if (ios) {
      	$(document).bind('touchmove',function(){
      		var intro = $("#intro"),
      			nav2 = $(".nav2");
    		if(intro.height() <= nav2.position().top)
    		{
       			nav2.css({position:'fixed', top:'34px'});
       			nav2.show();
       		}
       		else
       			nav2.hide();
  		});   
  	}
});