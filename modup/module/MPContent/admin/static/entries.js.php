$(document).ready(function() {
//{{{ entry ordering

var table = $('table#content_entries[class="manual_ordering"]');

table
    .before('<p>This entry type can be manually ordered. Drag and drop the rows to the order you would like.</p>')
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
                var p = table.prev('p');
                p.append(' <a>Save new order</a>.');
                $('> a', p).click(function() {
                    var rows = {},
                        id = $("select[name='filter[type][data]'] option:selected").val();
                        // id = location.pathname.match(/^\/admin\/module\/MPContent\/edit_entries\/$/)[1];
                    $('> tbody > tr', table)
                        .each(function(i) {
                            var href = $('> td:first > a', this).attr('href'),
                                id = href.match(/^.+\/(\d+)\/$/)[1];
                            rows[i] = id;
                        });
                    $.post(
                        '/admin/rpc/MPContent/order_entries/', 
                        { 
                            data: admin.JSON.make(rows),
                            type: id
                        }, 
                        function(data, tStatus)
                        {
                            if (data.success)
                            {
                                admin.messenger.add('success', 'Ordered correctly');
                            }
                            else
                            {
                                admin.messenger.add('error', 'Could not order');
                            }
                        },
                        'json'
                    );
                });
                table.data('sorted', true);
            }

        }
    });

//}}}
});
