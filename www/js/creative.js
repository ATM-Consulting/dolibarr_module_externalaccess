$(document).ready(function(){
  "use strict"; // Start of use strict

  $('[data-toggle="tips"]').popover({trigger:'hover'});
  $('[data-toggle="tooltip"]').tooltip()
  
  // Smooth scrolling using jQuery easing
  $('a.js-scroll-trigger[href*="#"]:not([href="#"])').click(function() {
    if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      if (target.length) {
        $('html, body').animate({
          scrollTop: (target.offset().top - 57)
        }, 1000, "easeInOutExpo");
        return false;
      }
    }
  });

  // Closes responsive menu when a scroll trigger link is clicked
  $('.js-scroll-trigger').click(function() {
    $('.navbar-collapse').collapse('hide');
  });

  // Activate scrollspy to add active class to navbar items on scroll
  $('body').scrollspy({
    target: '#mainNav',
    offset: 57
  });


  if( $("#mainNav").length) {
    // Collapse Navbar
    var navbarCollapse = function () {

      if ($("#mainNav").offset().top > 100) {
        $("#mainNav").addClass("navbar-shrink");
      } else {
        $("#mainNav").removeClass("navbar-shrink");
      }
    };

    if ($("#mainNav").data('defaultshrink') == undefined) {
      // Collapse now if page is not at top
      navbarCollapse();
      // Collapse the navbar when page is scrolled
      $(window).scroll(navbarCollapse);
    }
  }

  // Scroll reveal calls
  window.sr = ScrollReveal();
  sr.reveal('.sr-icons', {
    duration: 600,
    scale: 0.3,
    distance: '0px'
  }, 200);
  sr.reveal('.sr-button', {
    duration: 1000,
    delay: 200
  });
  sr.reveal('.sr-contact', {
    duration: 600,
    scale: 0.3,
    distance: '0px'
  }, 300);

  // Magnific popup calls
  $('.popup-gallery').magnificPopup({
    delegate: 'a',
    type: 'image',
    tLoading: 'Loading image #%curr%...',
    mainClass: 'mfp-img-mobile',
    gallery: {
      enabled: true,
      navigateByImgClick: true,
      preload: [0, 1]
    },
    image: {
      tError: '<a href="%url%">The image #%curr%</a> could not be loaded.'
    }
  });

  // scroll
  if(window.location.hash) {
    // Fragment exists

    if($("#mainNav").length == 0){
        var navbaroffset = 0;
    }else{
        var navbaroffset = $("#mainNav").height();
    }

    $(window).scrollTop($(window.location.hash).offset().top - navbaroffset - 30);
  }

  // convert select imput into searchable select input class="selectsearchable" data-live-search="true"
  $('.selectsearchable').selectpicker();



  // Confirm dialog button trigger
	$(document).on("click", "[data-confirm=\"1\"]", function(event) {
		event.preventDefault();
		var dial = $(this);

		// http://bootboxjs.com/examples.html

		bootbox.confirm({
			title: dial.attr('data-confirm-title'),
			message: dial.attr('data-confirm-message'),
			buttons: {
				cancel: {
					label: '<i class="fa fa-times"></i> ' + dial.attr('data-confirm-canceltxt')
				},
				confirm: {
					label: '<i class="fa fa-check"></i> ' + dial.attr('data-confirm-confirmtxt')
				}
			},
			callback: function (result) {
				if(result){
					window.location.replace(dial.attr('data-confirm-url'));
				}
			}
		});
	});

	// SUBMENU PATCH
	$('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
		if (!$(this).next().hasClass('show')) {
			$(this).parents('.dropdown-menu').first().find('.show').removeClass('show');
		}
		var $subMenu = $(this).next('.dropdown-menu');
		$subMenu.toggleClass('show');


		$(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
			$('.dropdown-submenu .show').removeClass('show');
		});


		return false;
	});

}); // End of use strict


