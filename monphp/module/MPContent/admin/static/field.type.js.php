(function($){
    "use strict";
    $(function() {
        // TODO copy this function for fields with default data?
        //{{{ content type meta field switchup
        $('.field_type select[name^="field[type][_fieldtype]"]').change(function() {

            var select = $(this),
                selected = select.val(),
            $('div.meta', fields).remove();
            switch (selected)
            {
            }
        });

        $('form').each(function() {
            $('.field_type select[name^="field[type][_fieldtype]"]', this).change();
        });

        //}}}
    });
}(jQuery));
