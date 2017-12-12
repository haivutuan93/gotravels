(function ($) {

    $(function () {
        var $geocodeBtn = $('#geocode'),
            $address = $('input[name="google_map_address"]'),
            $lat = $('input[name="google_map_latitude"]'),
            $lng = $('input[name="google_map_longitude"]'),
            $zoom = $('select[name="google_map_zoom"]'),
            $msgBox = $('#geocode_message'),
            geocoder = new google.maps.Geocoder(),
            map,
            marker;

        // init
        updateMap(
            new google.maps.LatLng({
                lat: +destination_options.lat,
                lng: +destination_options.lng
            })
        );
        $address.val(destination_options.address);

        // DOM events
        $geocodeBtn.on('click', function (e) {
            var address = $address.val() || '';

            if (address.length > 0) {
                $msgBox.text('');

                geocoder.geocode({'address': address}, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        updateLocation(results[0].geometry.location);
                    } else {
                        $msgBox.html(destination_options.error_text + status);
                    }
                });

            }

            return false;
        });
        $lat.on('change keydown paste cut input', latLngChangeHandler);
        $lng.on('change keydown paste cut input', latLngChangeHandler);
        $zoom.on('change keydown paste cut input', function () {
            var zoom = +$zoom.val() || 5;

            if (zoom != map.getZoom()) {
                map.setZoom(zoom);
            }
        });

        // map events
        map.addListener('click', function (e) {
            $address.val('');
            updateLocation(e.latLng, true);
        });
        map.addListener('zoom_changed', function (e) {
            $zoom.val(map.getZoom()).change();
        });

        function latLngChangeHandler() {
            var lat = +$lat.val() || 0,
                lng = +$lng.val() || 0;

            updateMap(new google.maps.LatLng({
                lat: lat,
                lng: lng
            }));

            $address.val('');
        }

        function updateLocation(location) {
            updateMap(location);
            updateLatLng(location);
        }

        function updateMap(location) {
            if (!map) {
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: +destination_options.zoom || 5,
                    center: location
                });
            } else {
                map.setCenter(location);
            }

            if (!location.equals(new google.maps.LatLng({lat: 0, lng: 0}))) {
                if (marker) {
                    marker.setPosition(location);
                } else {
                    marker = new google.maps.Marker({
                        map: map,
                        position: location
                    });
                }
            }
        }

        function updateLatLng(location) {
            $lat.val(location.lat());
            $lng.val(location.lng());
        }

    });

})(jQuery);
