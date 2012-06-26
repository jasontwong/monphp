(function($){
    "use strict";
    $(function() {
        var form = $('form'),
            fieldtypes = $('.fieldtype > div', form);
        form
            .on({
                submit: function(){
                    fieldtypes
                        .not(":visible")
                        .remove();
                    return true;
                }
            });
        $('.field_type .fields_dropdown select', form)
            .on({
                change: function(){
                    var select = $(this),
                        selected = select.val();
                    fieldtypes
                        .each(function(){
                            var el = $(this);
                            if (el.data('type') === selected)
                            {
                                el.show();
                            }
                            else
                            {
                                el.hide();
                            }
                        });
                }
            })
            .change();
    });
}(jQuery));
