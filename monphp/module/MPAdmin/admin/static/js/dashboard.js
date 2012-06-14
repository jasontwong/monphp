(function($){
    "use strict";
    $(function() {
        //{{{ function dashboard_record()
        var dashboard_record = function()
        {
            var elements = { left: {}, right: {}, trash: {} };
            $('.admin_dashboard_side')
                .each(function(i) {
                    var elside = $(this),
                        side = elside.attr('id').replace('admin_dashboard_', '');
                    $('> .admin_dashboard_element', elside).each(function(j) {
                        var id = $(this).attr('id'),
                            parts = id.match(/^admin_dashboard_element__([\d\w]+(?=__))__([\d\w]+)$/),
                            key = parts[1] + '__' + parts[2],
                            data = {
                                module: parts[1],
                                title: parts[2],
                                fold: $('> div.content', this).css('display') === 'block' ? 'opened' : 'closed'
                            };
                        switch (side)
                        {
                            case 'left':
                                elements.left[key] = data;
                            break;
                            case 'right':
                                elements.right[key] = data;
                            break;
                            case 'trash':
                                elements.trash[key] = data;
                            break;
                        }
                    });
                });
            $.post(
                '/admin/rpc/MPAdmin/dashboard/', 
                { json: JSON.stringify(elements) }, 
                function(data, tStatus)
                {
                    // something?
                }
            );
        };

        //}}}
        //{{{ drag and drop ordering
        $('.admin_dashboard_side:not(#admin_dashboard_trash)')
            .sortable({
                connectWith: '.admin_dashboard_side',
                dropOnEmpty: true,
                forcePlaceholderSize: true,
                handle: '> div.title',
                items: '> div.admin_dashboard_element',
                placeholder: 'admin_dashboard_element_placeholder',
                update: function(e, ui)
                {
                    $('.admin_dashboard_side')
                        .each(function() {
                            var el = $(this),
                                other;
                            if (!$('> div.admin_dashboard_element', this).length)
                            {
                                // var other = $('#admin_dashboard_' + (el.attr('id') === 'admin_dashboard_left' ? 'right' : 'left'));
                                other = $('#admin_dashboard_' + el.attr('id').replace('admin_dashboard_', ''));
                                if ($('> div.admin_dashboard_element', other).length)
                                {
                                    el.height(other.height());
                                }
                            }
                            else
                            {
                                el.height('auto');
                            }
                        });
                    dashboard_record();
                }
            });

        //}}}
        //{{{ dashboard control panel
        $('div.admin_dashboard_element > div.title')
            .each(function(){
                var el = $(this),
                    dash_el = el.parent(),
                    panel_text = dash_el.hasClass('admin_dashboard_element_closed')
                        ? "Open panel &darr;"
                        : "Close panel &uarr;";
                el.prepend('<div class="open_close">'+panel_text+'</div>');
                /*
                el.prepend(
                    (el.closest('#admin_dashboard_trash').length === 0)
                        ? '<div class="delete">TRASH ME</div>'
                        : '<div class="add">RETURN ME</div>');
                */
            });

        //}}}
        //{{{ collapsable dashboard elements
        $('div.admin_dashboard_element > div.title > div.open_close')
            .click(function(e) {
                e.stopImmediatePropagation();
                var el = $(this),
                    dash_el = el.parent().parent(),
                    panel_text = dash_el.hasClass('admin_dashboard_element_closed')
                        ? "Open panel &darr;"
                        : "Close panel &uarr;";
                dash_el
                    .toggleClass('admin_dashboard_element_closed')
                    .toggleClass('admin_dashboard_element_opened');
                el.html(panel_text);
                    
                dashboard_record();
            });

        //}}}
        //{{{ dashboard trash
        /*
        $('div.admin_dashboard_element > div.title > div.delete')
            .live('click', function(e) {
                e.stopImmediatePropagation();
                var el = $(this),
                    dash_el = el.parent().parent();
                el.removeClass('delete').addClass('add').text('RETURN ME');
                dash_el.prependTo('#admin_dashboard_trash');
                dashboard_record();
            });

        $('div.admin_dashboard_element > div.title > div.add')
            .live('click', function(e) {
                e.stopImmediatePropagation();
                var el = $(this),
                    dash_el = el.parent().parent();
                el.removeClass('add').addClass('delete').text('TRASH ME');
                dash_el.appendTo('#admin_dashboard_left');
                dashboard_record();
            });
        */

        //}}}
    });
}(jQuery));
