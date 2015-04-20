jQuery(window).load(function() {
              jQuery('#ANSlide'+setting.id+' .flexslider').flexslider({
                animation: setting.animation,
                prevText: "Prev",
                nextText: "Next",
                slideshow: setting.slideshow, 
                slideshowSpeed: setting.slideshowSpeed
              });
            });