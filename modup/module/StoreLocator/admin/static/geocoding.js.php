(function ($) {
    "use strict";
    $(function () {
        var form = $('form[name="store_location_add"], form[name="store_location_edit"]'),
            button = $('button[type="submit"]', form);
        
        button 
            .click(function () {
                var line_address = $('input[name="location[address1][data]"]', form).val(),
                    line_city = $('input[name="location[city][data]"]', form).val(),
                    line_state = $('input[name="location[state][data]"]', form).val(),
                    line_country = $('input[name="location[country][data]"]', form).val(),
                    line_zip_code = $('input[name="location[zip_code][data]"]', form).val(),
                    field_latitude = $('input[name="location[latitude][data]"]', form),
                    field_longitude = $('input[name="location[longitude][data]"]', form),
                    address = line_address + ', ' + line_city,
                    geocoder = new GClientGeocoder();
                if (line_state !== '') {
                    address += ', ' + line_state;
                }
                if (line_country !== '') {
                    address += ', ' + line_country;
                }
                if (line_zip_code !== '') {
                    address += ', ' + line_zip_code;
                }
                geocoder.getLocations(address, function (response) {
                    var status_code = response.Status.code,
                        data, 
                        point;
                    if (status_code === 200) {
                        data = response.Placemark[0];
                        point = { 
                            lat: data.Point.coordinates[1], 
                            lng: data.Point.coordinates[0] 
                        };
                        field_latitude.val(point.lat);
                        field_longitude.val(point.lng);
                        form.submit();
                    } else {
                        alert('Invalid address');
                    }
                });
                return false;
            });
    });
}($));
