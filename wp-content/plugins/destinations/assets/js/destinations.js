// Directory type - toggle results
jQuery(document).ready(function($) {

    $('#custom-post-query-submit').on('click', function(e) {
        e.preventDefault();

        var filter = $('#filter-by-date').val();
        document.location.href = 'edit.php?post_type=destination&page=destination-settings&tab=master-pages&m=' + filter;
    });

});

jQuery(document).ready(function($) {
    var langs = [];
    var langs_footer = [];

    $('.lang-alt-switcher').each(function(index) {
        langs.push($(this).attr('data-alt'));
     });
    $('.lang-alt-switcher-footer').each(function(index) {
        langs_footer.push($(this).val());
     });

    $('li.menu-item-language').each(function(index) {
        if(!$(this).hasClass('menu-item-language-current')) {
            $(this).find('a').attr('href', $('#lang-alt-switcher-'+langs[index]).val());
	    	if($('#lang-alt-switcher-'+langs[index]).hasClass('lang-alt-switcher-del')) {
				$(this).remove();
	        }
        }    
    });

    $('#lang_sel_footer li').each(function(index) {
        $(this).find('a').attr('href', $('#lang-alt-switcher-'+langs_footer[index]).val());
        if($('#lang-alt-switcher-'+langs_footer[index]).hasClass('lang-alt-switcher-del')) {
            $(this).remove();
        }
    });
});

// Rating category select
jQuery(document).ready(function($) {

    // Show/hide ratings for selected category
    $catSelect = $("select[name='category_id']");
    if ($catSelect.length) {

        // Update on change
        $catSelect.change(function() {
            cat = $(this).val();
            $('.travel-dir-rating').hide();
            $('.travel-dir-' + cat).show();
        });

        // Initial setting triggered
        $catSelect.trigger('change');
    }

});

// Ratings Controls
jQuery(document).ready(function($) {

    var data = {};
    var opts = {};
    var elems = null;
    $.fn.raterater = function(options) {

        /* Default options
         */
        $.fn.raterater.defaults = {
            submitFunction: 'submitRating', // this function will be called when a rating is chosen
            allowChange: false, // allow the user to change their mind after they have submitted a rating
            starWidth: 20, // width of the stars in pixels
            spaceWidth: 5, // spacing between stars in pixels
            numStars: 5
        };

        opts = $.extend({}, $.fn.raterater.defaults, options);
        opts.width = opts.numStars * (opts.starWidth + opts.spaceWidth); // total rating div width
        opts.starAspect = 0.9226; // aspect ratio of the font awesome stars

        elems = this;

        var value = $('#rating-star').val();

        rateraterInit();

        rateraterInitializePositions();

        return this;
    }

    var isFront = $('input.rating-is-front').val();

    $('.ratebox').raterater({
        submitFunction: 'rateAlert',
        allowChange: true,
        starWidth: (isFront == 'false') ? 20 : 13,
        spaceWidth: 5,
        numStars: 5
    });

    function rateraterInit() {

        elems.each(function() {

            var $this = $(this);
            var id = dataId($this);

            if (!id) {
                throw "Error: Each raterater element needs a unique data-id attribute.";
            }

            var key = $(this).attr('data-id');
            var val = (isFront == 'false')? $('#rating-'+key).val() : $(this).parent().find('input[name="rating-types_'+key+'"]').val();
            $(this).attr('data-rating', val);

            /* This is where we store our important data for each rating box
             */
            data[id] = {
                state: 'inactive', // inactive, hover, or rated
            };

            /* Make our wrapper relative if it is static so we can position children absolutely */
            if ($this.css('position') === 'static')
                $this.css('position', 'relative');

            /* Add class raterater-wrapper */
            $this.addClass('raterater-wrapper');

            /* Clear out anything inside so we can append the relevent children */
            $this.html('');

            /* We have 4 div children here as different star layers
             * Layer 1 contains the full filled stars as a background
             * Layer 2 shows the bright filled stars that represent the current user's rating
             * Layer 3 shows the bright filled stars that represent the item's rating
             * Layer 4 shows the outline stars and is just for looks
             * Layer 5 covers the widget and mainly exists to keep event.offsetX from being ruined by child elements
             */
            $.each(['bg', 'hover', 'rating', 'outline', 'cover'], function() {
                $this.append(' <div class="raterater-layer raterater-' + this + '-layer"></div>');
            });

			var rating_class = (isFront == 'false')? $(this).parent().next().find('.rate-class').val() : $(this).parent().find('.rate-class').val();
            var rating_color = (isFront == 'false')? $(this).parent().next().find('.rate-color').val() : $(this).parent().find('.rate-color').val();

            for( var i = 0; i < opts.numStars; i++ ) {
                $this.children( '.raterater-bg-layer' ).first()
                    .append( '<i class="' + rating_class + '"></i>' );
                $this.children( '.raterater-outline-layer' ).first()
                    .append( '<i class="' + rating_class + '"></i>' );
                $this.children( '.raterater-hover-layer' ).first()
                    .append( '<i class="' + rating_class + '"></i>' );
                $this.children( '.raterater-rating-layer' ).first()
                    .append( '<i class="' + rating_class + '"></i>' );
            }
            $('.raterater-hover-layer i.'+rating_class.substring(3)+', .raterater-rating-layer i.'+rating_class.substring(3)).css({'color':rating_color});

            /* Register mouse event callbacks */
            $this.find( '.raterater-cover-layer' ).hover( mouseEnter, mouseLeave );
            $this.find( '.raterater-cover-layer' ).mousemove( hiliteStarsHover );
            $this.find( '.raterater-cover-layer' ).click( rate );
        });
    }

    function rateraterInitializePositions() {
        elems.each( function() {
           
            var $this = $( this );
            var id = dataId( $this );
        
            /* Set the width and height of the raterater wrapper and layers */

            var width = opts.width + 'px';
            var height = Math.floor(opts.starWidth / opts.starAspect) + 'px';

            $this.css('width', width).css('height', height);

            $this.find('.raterater-layer').each(function() {
                $(this).css('width', width).css('height', height);
            });

            /* Absolutely position the stars (necessary for partial stars) */
            for (var i = 0; i < opts.numStars; i++) {
                $.each(['bg', 'hover', 'rating', 'outline'], function() {
                    $this.children('.raterater-' + this + '-layer').first().children('i').eq(i)
                        .css('left', i * (opts.starWidth + opts.spaceWidth) + 'px')
                        .css('font-size', Math.floor(opts.starWidth / opts.starAspect) + 'px');
                });
            }

            /* show the item's current rating on the raterater-rating-layer */
            var rating = parseFloat($this.attr('data-rating'));
            var whole = Math.floor(rating);
            var partial = rating - whole;
            hiliteStars(
                $this.find('.raterater-rating-layer').first(),
                whole,
                partial
            );
        });
    }

    function rate(e) {
        var $this = $(e.target).parent();
        var id = dataId($this);
        var stars = data[id].whole_stars_hover + data[id].partial_star_hover;

        /* Round stars to 2 decimals */
        stars = Math.round(stars * 100) / 100;

        /* Set the state to 'rated' to disable functionality */
        data[id].state = 'rated';

		$this.attr('data-rating', stars);
        $this.parent().next().find('.rate-manual-input').val(stars);

        /* Add the 'rated' class to the hover layer for additional styling flexibility */
        $this.find('.raterater-hover-layer').addClass('rated');

        /* Call the user-defined callback function if it exists */
        if (typeof window[opts.submitFunction] === 'function')
            window[opts.submitFunction](id, stars);
    }

    /* Calculate the number of stars from the x position of the mouse relative to the cover layer
     * (This is only compicated because of the spacing between stars)
     */
    function calculateStars(x, id) {

        /* Whole star = floor( x / ( star_width + space_width ) ) */
        var whole_stars = Math.floor(x / (opts.starWidth + opts.spaceWidth));

        /* Partial star = max( 1, ( x - whole_stars * ( star_width + space_width ) ) / star_width ) */
        var partial_star = x - whole_stars * (opts.starWidth + opts.spaceWidth);
        if (partial_star > opts.starWidth)
            partial_star = opts.starWidth;
        partial_star /= opts.starWidth;

        /* Store our result in the data object */
        data[id].whole_stars_hover = whole_stars;
        data[id].partial_star_hover = partial_star;
    }

    /* Given a layer object and rating data, highlight the stars */
    function hiliteStars($layer, whole, partial) {
        var id = dataId($layer.parent());

        /* highlight the 'whole' stars */
        for (var i = 0; i < whole; i++) {
            $layer.find('i').eq(i)
                .css('width', opts.starWidth + 'px');
        }

        /* highlight the partial star */
        $layer.find('i').eq(whole)
            .css('width', opts.starWidth * partial + 'px');

        /* clear the extra stars */
        for (var i = whole + 1; i < opts.numStars; i++) {
            $layer.find('i').eq(i)
                .css('width', '0px');
        }
    }

    /* Highlight the hover layer stars - This is the callback for the mousemove event */
    function hiliteStarsHover(e) {
        var id = dataId($(e.target).parent());

        /* Leave it alone, we aren't hovering */
        if (data[id].state !== 'hover') {
            return;
        }

        /* Get the mouse offsetX */
        var x = e.offsetX;

        /* Firefox requires a pageX hack */
        if (x === undefined) {
            x = e.pageX - $(e.target).offset().left;
        }

        data[id].stars = calculateStars(x, id);

        /* Find the layer element */
        var $layer = $(e.target).parent().children('.raterater-hover-layer').first();

        /* Call the more generic highlighting function */
        hiliteStars($layer, data[id].whole_stars_hover, data[id].partial_star_hover);
    }

    /* Active this rating box - This is the callback for the mouseenter event */
    function mouseEnter(e) {
        if (isFront == 'true')
            return;

        var id = dataId($(e.target).parent());
        /* Leave it alone, we have already rated this item */
        if (data[id].state === 'rated' && !opts.allowChange) {
            //return;
        }
        /* set the state to 'hover' */
        data[id].state = 'hover';
        /* show the hover layer and hide the rating layer */
        $(e.target).parent().children('.raterater-rating-layer').first().css('display', 'none');
        $(e.target).parent().children('.raterater-hover-layer').first().css('display', 'block');
    }

    /* Deactivate this rating box - This is the callback for the mouseleave event */
    function mouseLeave(e) {
        var id = dataId($(e.target).parent());
        /* Leave it alone, we have already rated this item */
        if (data[id].state === 'rated') {
            return;
        }
        /* set the state to 'inactive' */
        data[id].state = 'inactive';
        /* hide the hover layer, set rating value and show the rating layer */
        rateraterInitializePositions();
        $(e.target).parent().children('.raterater-hover-layer').first().css('display', 'none');
        $(e.target).parent().children('.raterater-rating-layer').first().css('display', 'block');
    }

    /* Shorthand function to get the data-id of an element */
    function dataId(e) {
        return $(e).attr('data-id');
    }

    $( '.rate-manual-input' ).on( 'change', function(e) {
        e.preventDefault();
        $(this).parent().prev().find('.ratebox').attr( 'data-rating', $(this).val() );
        rateraterInitializePositions();
    }); 

    $('.add_custom_contact').on('click', function(e) {
        e.preventDefault();
        var contacts_len = $('.details-contacts-last-number').val();
        var custom_contact = $('.contact-extra-template').clone(true);
        var new_contact = setCustomContactAttrs(custom_contact, contacts_len);
        $('.contact-extra-description').before(new_contact);
        $('.details-contacts-last-number').val(parseInt(contacts_len) + 1);

    });

    function setCustomContactAttrs(el, len) {
        el.find('.details-contacts-name').attr({
            id: 'contact_name[' + len + ']',
            name: 'contact_name[' + len + ']'
        });
        el.find('.details-contacts-value').attr({
            id: 'contact_value[' + len + ']',
            name: 'contact_value[' + len + ']'
        });
        el.removeClass('contact-extra-template');
        el.addClass('details-contacts');
        el.show();
        return el;
    }

    $('.remove_custom_other, .remove_custom_contact').on('click', function(e) {
        e.preventDefault();
        $(this).parent().parent().remove();
    });

    $('.add_custom_other').on('click', function(e) {
        e.preventDefault();
        var other_len = $('.details-other-last-number').val();
        var custom_other = $('.other-extra-template').clone(true);
        var new_other = setCustomOtherAttrs(custom_other, other_len);
        $('.other-extra-description').before(new_other);
        $('.details-other-last-number').val(parseInt(other_len) + 1);

    });

    function setCustomOtherAttrs(el, len) {
        el.find('.details-other-name').attr({
            id: 'other_name[' + len + ']',
            name: 'other_name[' + len + ']'
        });
        el.find('.details-other-value').attr({
            id: 'other_value[' + len + ']',
            name: 'other_value[' + len + ']'
        });
        el.removeClass('other-extra-template');
        el.addClass('details-other');
        el.show();
        return el;
    }
});