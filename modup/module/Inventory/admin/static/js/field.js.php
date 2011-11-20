$(function () {
    "use strict";
    //{{{ ajax calls
    function get_options(id, cb) {
        $.post('/admin/rpc/Inventory/get_options/', {'id': id}, cb, 'json');
    }
    function get_inventory(id, cb) {
        $.post('/admin/rpc/Inventory/get_inventory/', {'id': id}, cb, 'json');
    }
    function get_product(id, cb) {
        $.post('/admin/rpc/Inventory/get_product/', {'id': id}, cb, 'json');
    }
    //}}}
    var grids = $('input.inventory-grid');
    grids
        .each(function (index) {
            var el = $(this),
                /**
                 * if value is set, this is the product id to look up
                 */
                value = el.val(),
                name = el.attr('name'),
                ox_id = el.data('options-x'),
                oy_id = el.data('options-y'),
                product_id = el.data('product'),
                group_id = el.data('group-id'),
                options_x = ox_id ? undefined : [],
                options_y = oy_id ? undefined : [],
                has_options_x = false,
                has_options_y = false,
                /**
                 * because a lot of Ajax callbacks attempt to build the table, sometimes
                 * duplicate tables are made. These are state flags to prevent that
                 */
                building = false,
                built = false,
                columns = 0,
                rows = 0,
                /**
                 * html table for the inventory grid that later becomes a jQuery object
                 */
                table,
                /**
                 * array of quantity values
                 * inventory[row index][column index] = integer or empty string ''
                 */
                inventory = product_id ? undefined : [],
                /**
                 * holds info about x-axis and y-axis options
                 * product[x] = [[id, name], [id, name]]
                 * product[y] = [[id, name], [id, name]]
                 */
                product = product_id ? undefined : {x:[],y:[]},
                /**
                 * where everything will get stored in the val attribute as JSON string
                 */
                form_data_inventory = $('<input type="hidden" name="' + name + '[inventory]" />'),
                form_data_options_x = $('<input type="hidden" name="' + name + '[options_x]" />'),
                form_data_options_y = $('<input type="hidden" name="' + name + '[options_y]" />'),
                form_data_group_id = $('<input type="hidden" name="' + name + '[group_id]" />'),
                form_data_product_id = $('<input type="hidden" name="' + name + '[product_id]" />');
            form_data_group_id
                .val(group_id);
            form_data_product_id
                .val(value);
            el
                .after(form_data_inventory)
                .after(form_data_options_x)
                .after(form_data_options_y)
                .after(form_data_group_id)
                .after(form_data_product_id);

            //{{{ function serialize_inventory()
            function serialize_inventory() {
                var data = [];
                $('> tbody > tr', table)
                    .each(function () {
                        var el = $(this),
                            data_row = [];
                        if (!el.hasClass('control')) {
                            $('> td > input', this)
                                .each(function () {
                                    var val = parseInt($(this).val(), 10);
                                    val = isNaN(val) ? '' : val;
                                    data_row.push(val);
                                });
                            data.push(data_row);
                        }
                    });
                inventory = data;
                return admin.JSON.make(data);
            }
            //}}}
            //{{{ function activate_inputs()
            function activate_inputs() {
                $('input[type="text"]', table)
                    .keyup(function () {
                        form_data_inventory.val(serialize_inventory());
                    });
            }
            //}}}
            //{{{ function activate_column_deleters()
            function activate_column_deleters() {
                $('thead th div.deleter', table)
                    .each(function (i) {
                        var el = $(this),
                            th_i = has_options_y ? 1 : 0, // thead offset
                            tb_i = i; // tbody offset
                        th_i += i;
                        el.click(function (e) {
                            e.stopImmediatePropagation();
                            product.x.splice(i, 1);
                            columns -= 1;
                            $('thead tr', table)
                                .each(function () {
                                    $('th', this).eq(th_i).remove();
                                });
                            $('tbody tr', table)
                                .each(function () {
                                    $('td', this).eq(tb_i).remove();
                                });
                            form_data_options_x.val(admin.JSON.make(product.x));
                            form_data_inventory.val(serialize_inventory());
                        });
                    });
            }
            //}}}
            //{{{ function activate_row_deleters()
            function activate_row_deleters() {
                $('tbody tr', table)
                    .each(function (i) {
                        var el = $(this),
                            deleter = $('> th > div.deleter', el);
                        deleter.click(function (e) {
                            e.stopImmediatePropagation();
                            rows -= 1;
                            product.y.splice(i, 1);
                            el.remove();
                            form_data_options_y.val(admin.JSON.make(product.y));
                            form_data_inventory.val(serialize_inventory());
                        });
                    });
            }
            //}}}
            //{{{ function build_form()
            // callback runs after all rpc calls for option group and inventory data
            function build_form() {
                if (options_x === undefined || 
                    options_y === undefined || 
                    inventory === undefined || 
                    product === undefined ||
                    building || 
                    built) {
                        return;
                    }
                // option_x, option_y, row, col, i, and j might not be needed if
                // refactored with $.each()
                var option_x,
                    option_y,
                    row,
                    col,
                    col_select,     // the dropdown with the options
                    col_adder,      // the th cell
                    col_trigger,    // the anchor that gets clicked
                    row_select,     //
                    row_adder,      // same as col_*
                    row_trigger;    //
                building = true;
                table =     '<table class="inventory-grid" id="inventory-grid-' + index + '">';
                if (product.x.length && product.y.length) {
                    if (has_options_x && product.x.length) {
                        table +=    '<thead>' + 
                                        '<tr>';
                        if (has_options_y) {
                            table +=        '<th>&nbsp;</th>';
                        }
                        $.each(product.options_x, function (i, option_x) {
                            columns += 1;
                            table +=        '<th>' +
                                                '<div class="deleter">' +
                                                    '<a>&times;</a>' +
                                                '</div>' +
                                                '<div class="header">' + 
                                                    option_x.name + 
                                                '</div>' +
                                            '</th>';
                        });
                        table +=            '<th class="add-column"><a>add another</a></th>' +
                                        '</tr>' +
                                    '</thead>';
                    } 
                    table +=    '<tbody>';
                    if (has_options_y && product.y.length) {
                        $.each(product.options_y, function (i, option_y) {
                            table +=    '<tr>' +
                                            '<th>' + 
                                                '<div class="deleter">' +
                                                    '<a>&times;</a>' +
                                                '</div>' +
                                                '<div class="header">' + 
                                                    option_y.name + 
                                                '</div>' +
                                            '</th>';
                            $.each(product.inventory[i], function (j, row) {
                                //$.each(row, function (k, col) {
                                    table +=    '<td>' +
                                                    '<input type="text" class="text" value="' + row + '">' +
                                                '</td>';
                                //});
                            });
                            table +=        '<td>&nbsp;</td>' +
                                        '</tr>';
                        });
                        table +=        '<tr class="control">' +
                                            '<th class="add-row"><a>add another</a></th>';
                        $.each(product.inventory[0], function (j, col) {
                            table +=        '<td>&nbsp;</td>';
                        });
                        table +=            '<td>&nbsp;</td>' +
                                        '</tr>';
                    }
                    table +=    '</tbody>';
                } else {
                    if (has_options_x) {
                        table +=    '<thead>' +
                                        '<tr>';
                        if (has_options_y) {
                            table +=        '<th>&nbsp;</th>';
                        }
                        table +=            '<th class="control add-column"><a>add another</a></th>' +
                                        '</tr>' +
                                    '</thead>';
                    }
                    table +=    '<tbody>' +
                                    '<tr class="control">';
                    if (has_options_y) {
                        table +=        '<th class="add-row"><a>add another</a></th>';
                    } 
                    table +=            '<td>&nbsp;</td>' +
                                    '</tr>' +
                                '</tbody>';
                }
                table +=    '</table>';

                // initialize the table and add functionality
                table = $(table);
                activate_inputs();
                activate_column_deleters();
                activate_row_deleters();
                form_data_options_x.val(admin.JSON.make(product.x));
                form_data_options_y.val(admin.JSON.make(product.y));
                form_data_inventory.val(serialize_inventory());

                col_adder = $('th.add-column', table);
                if (has_options_x) {
                    col_select = '<select>';
                    col_select += '<option val="">[select]</option>';
                    $.each(options_x, function (i, option) {
                        col_select += '<option val="' + option.id + '">' + option.name + '</option>';
                    });
                    col_select += '</select>';
                    col_select = $(col_select);
                    col_trigger = $('> a', col_adder);

                    col_select
                        .hide()
                        .change(function () {
                            var el = $(this),
                                deleter = $('<div class="deleter"><a>&times;</a></div>'),
                                cell = $('<th><div class="header">' + el.val() + '</div></th>'),
                                option = $('> option:selected', el).attr('val');
                            el.hide();
                            col_trigger.show();
                            col_adder.before(cell);
                            cell.prepend(deleter);
                            if (has_options_y) {
                                $('> tbody > tr', table)
                                    .each(function () {
                                        var el = $(this);
                                        if (!el.hasClass('control')) {
                                            $('> td:last', el).before('<td><input type="text" class="text"></td>');
                                        } else {
                                            $('> td:last', el).before('<td>&nbsp;</td>');
                                        }
                                    });
                            } else {
                                $('> tbody > tr > td:last', table)
                                    .before('<td><input type="text" class="text"></td>');
                            }
                            product.x.push(option);
                            form_data_options_x.val(admin.JSON.make(product.x));
                            columns += 1;
                            activate_inputs();
                            activate_column_deleters();
                        })
                        .appendTo(col_adder);
                    col_trigger
                        .click(function () {
                            var el = $(this);
                            el.hide();
                            $('> option:first', col_select).attr('selected', 'selected');
                            col_select.show();
                        });
                }
                row_adder = $('th.add-row', table);
                if (has_options_y) {
                    row_select = '<select>';
                    row_select += '<option val="">[select]</option>';
                    $.each(options_y, function (i, option) {
                        row_select += '<option val="' + option.id + '">' + option.name + '</option>';
                    });
                    row_select += '</select>';
                    row_select = $(row_select);
                    row_trigger = $('> a', row_adder);
                    row_select
                        .hide()
                        .change(function () {
                            var el = $(this),
                                deleter = $('<div class="deleter"><a>&times;</a></div>'),
                                cell = $('<th><div class="header">' + el.val() + '</div></th>'),
                                row = $('<tr />'),
                                option = $('> option:selected', el).attr('val'),
                                html = '',
                                i;
                            cell.prepend(deleter);
                            el.hide();
                            row_trigger.show();
                            if (has_options_x) {
                                if (columns) {
                                    for (i = 0; i < columns; i += 1) {
                                        html += '<td><input type="text" class="text"></td>';
                                    }
                                }
                                html += '<td>&nbsp;</td>';
                            } else {
                                html += '<td><input type="text" class="text"></td>';
                            }
                            html += '</tr>';
                            row
                                .append(cell)
                                .append(html);
                            $('> tbody > tr:last', table)
                                .before(row);
                            product.y.push(option);
                            form_data_options_y.val(admin.JSON.make(product.y));
                            rows += 1;
                            activate_inputs();
                            activate_row_deleters();
                        })
                        .appendTo(row_adder);
                    row_trigger
                        .click(function () {
                            var el = $(this);
                            el.hide();
                            $('> option:first', row_select).attr('selected', 'selected');
                            row_select.show();
                        });
                }
                el.after($(table));
                building = false;
                built = true;
            }
            //}}}
            //{{{ get all data needed to start building the form
            if (ox_id) {
                get_options(ox_id, function (data) {
                    options_x = data;
                    has_options_x = options_x.length > 0;
                    build_form();
                });
            }
            if (oy_id) {
                get_options(oy_id, function (data) {
                    options_y = data;
                    has_options_y = options_y.length > 0;
                    build_form();
                });
            }
            if (product_id) {
                get_product(product_id, function (data) {
                    product = data;
                    get_inventory(product_id, function (data) {
                        inventory = data;
                        build_form();
                    });
                });
            } else if (value) { 
                get_product(value, function (data) {
                    product = data;
                    get_inventory(value, function (data) {
                        inventory = data;
                        build_form();
                    });
                });
            } else if (product) { 
                get_inventory(product_id, function (data) {
                    inventory = data;
                    build_form();
                });
            }
            //}}}
        });
});
