jQuery(document).ready(function($){

	// Top navbar animation
	// ------------------------------------------------------------------------
	$(document).bind('shrink-menu-init', function(e, status) {
		var topBarHeight = $('.navbar-extra-top').outerHeight(); // getting the height of the nabar-extra-top
		scrollMark = Math.max(topBarHeight, 30); // forced minimum of 30
		style = ".menu-shrink {top : -"+topBarHeight+"px !important;}";
		if ( !$('#ShrinkMenu').length ) {
			$( "<style></style>" ).attr('id','ShrinkMenu').data('scrollMark',scrollMark).appendTo( "head" ); // add custom CSS for height offset
		}
		$('#ShrinkMenu').html(style);
	});
	// navbar adjustments on scroll
	$(document).bind('shrink-menu', function(e, status){
		scrollMark = $('#ShrinkMenu').data('scrollMark');
		// when scroll hits height of navbar top, apply style changes
		if ( $(this).scrollTop() < scrollMark ) {
			$('#MainMenu').removeClass('scrolled menu-shrink');
		} else {
			$('#MainMenu').addClass('scrolled menu-shrink');
		}
	});
	// trigger shrink-menu on scroll
	$(window).resize( function(){
		$(document).trigger('shrink-menu-init');
	});
	$(window).scroll( function(){
		$(document).trigger('shrink-menu');
	});


	// Sub-navbar affix on scroll
	// ------------------------------------------------------------------------
	if ($('#SubMenu').length) {
		$('#SubMenu').affix({
			offset: {
				top: function () {
					return $('#SubMenu').parent().offset().top - $('#navbar-main-container').outerHeight();
				},
			}
		}).css('top',$('#navbar-main-container').outerHeight());
		// Update values on window resize
		$(window).resize( function() {
			theTop = $('#SubMenu').parent().offset().top - $('#navbar-main-container').outerHeight();
			$('#SubMenu').data('bs.affix').options.offset = { top: theTop };
		});

		$('#SubMenu').on('affixed.bs.affix', function() {
			$('a.navbar-brand.scrollTop span').text($('#destination-the-title').val());
		});
		$('#SubMenu').on('affixed-top.bs.affix', function() {
			setTimeout( function() { $('a.navbar-brand.scrollTop span').text(''); } , 600)
		});		
	}


	// Accordions - always have 1 panel open
	// ------------------------------------------------------------------------
	if ( $('.panel-heading').length ) {

		$('.panel-heading').on('click',function(e){
			if($(this).parents('.panel').children('.panel-collapse').hasClass('in')){
				e.preventDefault();
				e.stopPropagation();
			}
		});
	}


	// Tooltips
	// ------------------------------------------------------------------------
	$('[data-toggle="tooltip"]').tooltip({
		placement: function(tip, trigger) {
			// show above, unless no space. show bottom on affixed sub-nav
			return ( $(trigger).parents('#SubMenu.affix').length ) ? 'bottom' : 'auto top';
		}
	});


	// Popovers
	// ------------------------------------------------------------------------
	$('[data-toggle="popover"]').popover();

	// Next/Prev Post Nav
	$('.nav-previous > a, .nav-next > a').popover({
		html : true,
		placement : 'top',
		trigger : 'hover',
		delay : { "show": 500, "hide": 100 },
		title : function() {
			return $(this).find('.meta-nav-title').html();
		},
		content : function() {
			var img = $('<img class="placeholder" width="600" height="800" style="visibility:hidden" >');
			img.attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAYAAAAICAMAAADtGH4KAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDE0IDc5LjE1Njc5NywgMjAxNC8wOC8yMC0wOTo1MzowMiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjMxQjk3NTA3RjJGRDExRTRCNjk0Rjg0QjlEODkzNDkxIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjMxQjk3NTA4RjJGRDExRTRCNjk0Rjg0QjlEODkzNDkxIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6MzFCOTc1MDVGMkZEMTFFNEI2OTRGODRCOUQ4OTM0OTEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6MzFCOTc1MDZGMkZEMTFFNEI2OTRGODRCOUQ4OTM0OTEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6NsDE8AAAABlBMVEXp6usAAAD3NurCAAAADklEQVR42mJgIA8ABBgAADgAAYdNUgcAAAAASUVORK5CYII=');
			return img;
		},
		template : '<div class="popover post-nav-popover" role="tooltip"><div class="arrow"></div><div class="popover-content"></div><h3 class="popover-title"></h3></div>'
	});
	// add image to background
	$('.nav-previous > a, .nav-next > a').on('shown.bs.popover', function (e) {
		id = $(this).attr('aria-describedby');
		$('#'+id).css({
			'background-image' : 'url('+ $(this).find('.meta-nav-img').text() +')'
		});
	})


	// Owl carousel
	// ------------------------------------------------------------------------
	if ( $('.featured-carousel').length ) {
		$(".featured-carousel").owlCarousel({
			items: 1,
			loop: true,
			autoplay: true,
			autoplayHoverPause: true,
			autoplayTimeout: 3800,
			autoplaySpeed: 800,
			navSpeed: 500,
			dots: false,
			nav: true,
			navText: [
				'<i class="fa fa-angle-left"></i>',
				'<i class="fa fa-angle-right"></i>'
			]
		});
	}


	// Navbar Hover/Click Responsive Behavior
	// ------------------------------------------------------------------------
	collapseSize = 1299; // 768;

	// hover sub-menu items
	$('.navbar-nav a').click( function(e) {
		$this = $(e.target);
		href = $this.attr('href'); // Link URL

		// Check link value
		if (href === undefined || !href.length || href === '#' || href === 'javascript:;') {
			href = false;
		}
		// Link behavior
		if ($this.hasClass('dropdown-toggle')) {
			// Parent menu items
			if ($(window).width() > collapseSize) {
				if (href) {
					// large screens, follow the parent menu link when clicked
					if (e.which !== 2 && e.target.target != '_blank') {
						window.location.href = href;
					}
				}
			 } else if ( $this.parent().hasClass('open') && href !== false) {
				// small screens, 1st tap opens sub-menu & 2nd tap follows link
				$(document).trigger('collapse-menus');
				window.location.href = href;
			}
		} else {
			// All other menu items, close menu on click
			$(document).trigger('collapse-menus');
		}
	});
	// Keep parent menus open on sub-menu expand
	$(document).on('show.bs.dropdown', function(obj) {
		if ($(window).width() <= collapseSize) {
			$(obj.target).parents('.show-on-hover').addClass('open');
		}
	});
	$('.navbar a:not(.dropdown-toggle)').click( function(e) {

		$this = $(e.target);
		href = $this.attr('href'); // Link URL

		// Check link value
		if (href === undefined || !href.length || href === '#' || href === 'javascript:;') {
			href = false;
		}
		// Link behavior
		if ($(window).width() > collapseSize) {
			if (href) {
				// large screens, follow the parent menu link when clicked
				if (e.which !== 2 && e.target.target != '_blank') {
					window.location.href = href;
				}
			}
		 } else if ( $this.parent().hasClass('open') && href !== false) {
			// small screens, 1st tap opens sub-menu & 2nd tap follows link
			$(document).trigger('collapse-menus');
			window.location.href = href;
		}
	});
	// Close all menus
	$(document).on('collapse-menus', function () {
		$('.collapse.in').removeClass('in').children().removeClass('open');
	});
	// Hover styling helpers
	$('.navbar-nav > li.show-on-hover').hover(function() {
		if ($(window).width() > collapseSize) {
			$(this).addClass('open');
		}
	}, function() {
		if ($(window).width() > collapseSize) {
			$(this).removeClass('open');
		}
	});


	// Setup local scrolling and pre-defined anchor tags
	// ------------------------------------------------------------------------
	$scrollTop = $("a.scrollTop, .scrollTop a, a[href='#top']");

	// Back to the top of the page (behavior)
	$scrollTop.on("click", function( event ) {
		event.preventDefault();
		$('html,body').stop().animate({ scrollTop: 0 }, 1000); // scroll
	});


	// Responsive video embeds
	// ------------------------------------------------------------------------
	$(".entry-content, .video-container").fitVids();


	// Maps - toggle show/hide button
	// ------------------------------------------------------------------------
	mapTransition = {
		'height':'100%',
		'transition':'height .45s ease-out',
		'-webkit-transition':'height .45s ease-out',
		'-moz-transition':'height .45s ease-out'
	};
	jQuery('#HeaderMapToggle').click( function(e) {
		e.preventDefault();
		$(this).blur(); // prevents lingering focus styles

		$hero = jQuery('section.hero');
		$map = jQuery('#gmap_wrapper');
		mapZindex = $map.css('z-index');


		// on hidden start, z-index used to ensure map is rendered but not visible
		if (parseInt(mapZindex) < 0) {
			$map.css({
				'z-index' : 0,
				'height'  : '0px'
			});
		} else {
			$map.css(mapTransition); // makes sure transitions work on visible start
		}

		mapHeight = $map.css('height');

		// Show/hide
		if ($hero.hasClass('mapOn')) {
			$map.css({
				'height':'0px',
			});

			$hero.addClass('mapOff').removeClass('mapOn');
			$(this).parent('li').removeClass('open');
		} else {
			$map.css(mapTransition); //

			$hero.addClass('mapOn').removeClass('mapOff');
			$(this).parent('li').addClass('open');

			setTimeout(function(){
				jQuery('html, body').animate({
					scrollTop: 0
				}, 500);
			}, 100);
		}

	});


	// Maps - fade overlays on map hover
	// ------------------------------------------------------------------------
	$heroOverlays = $( this ).find('.bg-overlay').add($('#MainMenu'));
	$('.hero').hover(
		function() {
			if ( $(this).hasClass('mapOn') ) {
				// Hide overlays
				$heroOverlays.stop(true).animate({
					opacity: 0
				}, 120, function() {
					$( this ).css('z-index', '-1');
				});
			}
		}, function() {
			if ( $(this).hasClass('mapOn') ) {
				// Show overlays
				$heroOverlays.stop(true).css('z-index', '1').animate({
					opacity: 1
				}, 400, function() {
					$('#MainMenu').css('z-index', '1030'); // needs to be above bg-overlay
				});
			}
		}
	);


	// Things we want to trigger once, manually, after loading the page
	// ------------------------------------------------------------------------

	// Fire the menu shrink function
	$(document).trigger('shrink-menu-init');
	$(document).trigger('shrink-menu');

});
