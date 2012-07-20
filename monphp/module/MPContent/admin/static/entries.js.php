(function($){
    "use strict";
    $(function() {
        var table = $('table#content_entries[class="manual_ordering"]'),
            message = $('<p />'),
            order_text = 'This entry type can be manually ordered. Drag and drop the rows to the order you would like. ',
            save_order = $('<a href="javascript:;">Save New Order</a>');
        // {{{ save_order
        save_order
            .on({
                click: function() {
                    var rows = [],
                        name = $("select[name='filter[type][data]'] option:selected").val();
                    $('> tbody > tr', table)
                        .each(function() {
                            rows.push($(this).data('id'));
                        });
                    $.post(
                        '/admin/rpc/MPContent/order_entries/', 
                        { 
                            ids: rows,
                            type: name
                        }, 
                        function(data)
                        {
                            if (data.success)
                            {
                                message
                                    .empty()
                                    .removeAttr('class')
                                    .addClass('success')
                                    .text('Ordered correctly');
                                table.data('sorted', true);
                            }
                            else
                            {
                                message
                                    .empty()
                                    .removeAttr('class')
                                    .addClass('error')
                                    .text('Could not order');
                            }
                        },
                        'json'
                    );
                }
            });
        // }}}
        table
            .before(message.text(order_text))
            .data('sorted', false);
        $('> tbody', table)
            .sortable({
                axis: 'y',
                forceHelperSize: true,
                forcePlaceholderSize: true,
                update: function(e, ui)
                {
                    if (!table.data('sorted'))
                    {
                        message
                            .text(order_text)
                            .append(save_order);
                    }

                }
            });
    });
}(jQuery));
