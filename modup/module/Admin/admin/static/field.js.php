$(document).ready(function() {
//{{{ date field
$('input.date').datepicker({
    dateFormat: 'yy-mm-dd',
    duration: '',
    showTime: true,
    constrainInput: false,
    stepMinutes: 1,
    stepHours: 1,
    altTimeField: '',
    time24h: true,
    prevText: '&laquo;',
    nextText: '&raquo;'
});
//}}}
//{{{ time field
/*
$('form .field input.timepickr').timepickr({
    updateLive: false,
    trigger: 'click'
});
*/
//}}}
//{{{ tabs
var tab_classes = '';
$('.tabbed').each(function(i){
    var el = $(this),
        el_class = el.attr('class').match(/tab-\w+/);
    if (el_class === null)
    {
        el_class = 'tabbed';
    }
    if (tab_classes.match(el_class) === null)
    {
        var tmp = '.' + el_class;
        $(tmp).wrapAll('<div />');
        create_tabs($(tmp + ':first'), tmp);
    }
    tab_classes += el_class;
});

//}}}
//{{{ dropdown double
var dropdown_double = $('select.dropdown_double'),
    dropdown_double_reorder = function() {
        $('> option', this)
            .each(function() {
                var option = $(this),
                    prev = option.prev();
                if (prev.length && prev.text() > option.text())
                {
                    option.after(prev);
                }
            });
    };

$('select.dropdown_double')
    .each(function() {
        var left = $(this),
            values = left.nextAll('input[type="hidden"]'),
            right = $('<select name="' + left.attr('name') + '" class="dropdown_double dropdown_double_right" multiple="multiple" />');

        left
            .addClass('dropdown_double_left')
            .removeAttr('name')
            .after(right)
            .bind('option_add', dropdown_double_reorder);

        right
            .bind('option_add', dropdown_double_reorder);

        values  
            .each(function() {
                right
                    .prepend($('> option[value="' + $(this).val() + '"]', left));
            });

    });

$('select.dropdown_double > option')
    .live('dblclick', function() {
        var option = $(this),
            select = option.parent(),
            other = select.siblings('select.dropdown_double');
        other
            .prepend(option)
            .trigger('option_add');
    });

//}}}
//{{{ list double ordered
$('select.list_double_ordered')
    .each(function() {
        var select = $(this),
            name = select.attr('name'),
            name_pre = name.match(/^\w+\[\d+\]\[.+\]$/)[0],
            div_left = $('<div class="list_double_ordered list_double_ordered_left" />'),
            div_right = $('<div class="list_double_ordered list_double_ordered_right" />'),
            list_left = $('<ul />'),
            list_right = $('<ul />');

        div_left
            .append('<p class="label">Unselected</p>')
            .append(list_left);

        div_right
            .append('<p class="label">Selected</p>')
            .append(list_right);
    
        select
            .removeAttr('name')
            .removeAttr('class')
            .hide()
            .after(div_right)
            .after(div_left);

        $('> option', select)
            .each(function() {
                var o = $(this),
                    item = $('<li>' + o.text() + '</li>');
                item
                    .data('val', o.val())
                    .bind('dblclick', function() {
                        var select = $(this).parent()
                            side = select.data('side');
                        if (side == 'left')
                        {
                            list_right.append(this);
                        }
                        else if (side == 'right')
                        {
                            list_left.append(this);
                        }
                        list_left
                            .trigger('altered');
                        list_right
                            .trigger('altered');
                    });
                list_left.append(item);
            });

        list_right
            .data('side', 'right')
            .bind('altered', function() {
                $('> li', this)
                    .each(function(i) {
                        var item = $(this);
                        $('> input[type="hidden"]', item).remove();
                        item.append('<input type="hidden" name="' + name_pre + '[' + i + ']" value="' + item.data('val') + '" />');
                    });
            })
            .sortable({
                axis: 'y',
                update: function() {
                    list_right.trigger('altered');
                }
            })
            .css('padding-left', '10px');

        list_left
            .data('side', 'left')
            .bind('altered', function() {
                $('> li > input[type="hidden"]', this).remove();
            })
            .css('padding-left', '10px');

        // initial data
        $('> option:selected', select)
            .each(function() {
                var val = $(this).val();
                $('> li', list_left)
                    .each(function() {
                        var item = $(this);
                        if (item.data('val') == val)
                        {
                            item.dblclick();
                        }
                    });
            })
            .remove();

    })
    .closest('.field')
    .css({ 'background-color': '#FFF', 'border': '1px solid #000', 'padding-bottom': '15px'});

//}}}
//{{{ RTE
$('textarea.rte').tinymce(<?php echo Module::h('admin_tinymce'); ?>);

//}}}
//{{{ turn reset button into link
link_cancel = $('<a class="cancel">Cancel</a>');
link_cancel.click(function(){
    //$(this).closest('form').get(0).reset();
    history.go(-1);
});
$(':reset').replaceWith(link_cancel);

//}}}
});

// {{{ function create_tabs(tab_marker, map_class)
function create_tabs(tab_marker, map_class)
{
    var tab_id = map_class.replace('.', '');
    tab_marker.before('<ul class="clear">' +
                        $.map($(map_class), function(el, i) {
                            $(el).addClass('tab').attr('id', tab_id + '-' + i);
                            return '<li><a href="#' + tab_id + '-' + i + '">' + $('> div.label', el).remove().html() + '</a></li>';
                        }).join('') +
                    '</ul>');
    tab_marker.parent().tabs();
}

// }}}
