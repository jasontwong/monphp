(function($){
    "use strict";
    $(function() {
        $('#user-edit, #user-add').submit(function() {

            var pass = $('input[name$="[pass][password]"]', this),
                conf = $('input[name$="[pass][password_confirm]"]', this),
                hash = $('input[name$="[pass][password_hashed]"]', this),
                name_pass = pass.attr('name'),
                name_conf = conf.attr('name');

            if (pass.val() !== '' && conf.val() !== '')
            {
                hash.val('1');
                pass.attr('name', 'password_view');
                conf.attr('name', 'password_confirm_view');
                $(this)
                    .append('<input type="hidden" name="' + name_pass + '" value="' + hex_sha1(pass.val()) + '" />')
                    .append('<input type="hidden" name="' + name_conf + '" value="' + hex_sha1(conf.val()) + '" />');
            }

        });
    });
}(jQuery));
