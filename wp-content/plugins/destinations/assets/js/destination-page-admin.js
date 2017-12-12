if (destination_page_options.is_nomaster) {

    // allow to update nomaster info page permalinks
    jQuery(function ($) {
        var $realSlug = $('#post_name'),
            postName = $realSlug.val(),
            $publishBtn = $('#publish');

        $(document).ajaxSend(function (event, jqXHR, settings) {
            var data = getQueryParameters(settings.data || '');

            if (typeof data.action !== 'undefined' && data.action === 'sample-permalink') {
                data.action = 'update_info_page_permalink';
                settings.data = $.param(data);
                $publishBtn.prop('disabled', true);
            }
        });

        $(document).ajaxSuccess(function (event, jqXHR, settings) {
            var data = getQueryParameters(settings.data || '');

            if (typeof data.action !== undefined && data.action === 'update_info_page_permalink') {
                setTimeout(function () {
                    var href = $('#sample-permalink').find('a').attr('href');

                    $('#post-preview').attr('href', href);
                    $realSlug.val(postName);
                }, 0);
                $publishBtn.prop('disabled', false);
            }
        });

		// prevent post name updating
        $(document).on('keyup', '#new-post-slug', function() {
            $realSlug.val(postName);
        });

        function getQueryParameters(str) {
            return str.replace(/(^\?)/, '').split("&").map(function (n) {
                return n = n.split("="), this[n[0]] = n[1], this
            }.bind({}))[0];
        }

    });

}
