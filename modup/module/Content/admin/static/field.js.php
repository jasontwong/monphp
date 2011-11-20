$(document).ready(function() {
//{{{ add another link behavior
var content_multiple_remove = $('<div class="remove">&times; Remove field</div>'),
    content_multiple_move_down = $('<a href="javascript:;" class="move_down">Move down</a>'),
    content_multiple_move_up = $('<a href="javascript:;" class="move_up">Move up</a>'),
    content_multiple_add = $('<a href="javascript:;" class="content_multiple_add">Add Another</a>');
content_multiple_move_down
    .click(function(){
        var el = $(this),
            field = el.closest('.fields'),
            next_field = field.next('.fields'),
            row = el.closest('.content_multiple'),
            rte = $('textarea.rte', field);
        if (next_field.length)
        {
            if (rte.length)
            {
                rte.tinymce().remove();
            }
            field.insertAfter(next_field);
            row.trigger('add_remove');
            if (rte.length)
            {
                rte.tinymce(<?php echo Module::h('admin_tinymce'); ?>);
            }
        }
    });
content_multiple_move_up
    .click(function(){
        var el = $(this),
            field = el.closest('.fields'),
            prev_field = field.prev('.fields'),
            row = el.closest('.content_multiple'),
            rte = $('textarea.rte', field);
        if (prev_field.length)
        {
            if (rte.length)
            {
                rte.tinymce().remove();
            }
            field.insertBefore(prev_field);
            row.trigger('add_remove');
            if (rte.length)
            {
                rte.tinymce(<?php echo Module::h('admin_tinymce'); ?>);
            }
        }
    });
content_multiple_remove
    .click(function() {
        var el = $(this);
        el.parent().remove();
        el.closest('.content_multiple').trigger('add_remove');
    });
content_multiple_add
    .click(function() {
    var row = $(this).parent().parent(),
        old_rte = $('.fields:first textarea.rte', row),
        editor_ids = [],
        clone, new_rte;
    if (old_rte.length)
    {
        old_rte
            .tinymce()
            .remove();
    }
    clone = $('.fields:first', row)
                .clone(true)
                .addClass('content_multiple_fields_additional');
    if (old_rte.length)
    {
        old_rte
            .tinymce(<?php echo Module::h('admin_tinymce'); ?>);
    }
    row.append(clone)
        .trigger('add_remove');
    new_rte = $('textarea.rte', clone);
    if (new_rte.length)
    {
        new_rte
            .each(function(){
                var el = $(this);
                el.attr('id', '')
                    .tinymce(<?php echo Module::h('admin_tinymce'); ?>);
                tinymce.get(el.tinymce().editorId).setContent('');
            });
    }

    // added special class checks for special uses
    // else blocks made to preserve original add another design
    $('input', clone)
        .each(function(){
            var el = $(this);
            el.trigger('new_clone')
            if (el.hasClass('array_preserve'))
            {
            }
            else if (el.hasClass('array_clear'))
            {
                if (el.attr('type') == 'checkbox')
                {
                    el.removeAttr('checked');
                }
                else
                {
                    el.val('');
                }
            }
            else
            {
                if (el.attr('type') == 'checkbox')
                {
                    el.removeAttr('checked');
                }
                else if (el.attr('type') != 'hidden')
                {
                    el.val('');
                }
            }
        });
    $('select', clone)
        .each(function(){
            var el = $(this);
            el.trigger('new_clone')
            if (el.hasClass('array_preserve'))
            {
            }
            else if (el.hasClass('array_clear'))
            {
                $(':selected', el).removeAttr('selected');
            }
            else
            {
                $(':selected', el).removeAttr('selected');
            }
        });
    $('textarea', clone)
        .each(function(){
            var el = $(this);
            el.trigger('new_clone')
            if (el.hasClass('array_preserve'))
            {
            }
            else if (el.hasClass('array_clear'))
            {
                el.text('');
            }
            else
            {
                el.text('');
            }
        });
    clone.trigger('new_clone');
});
//}}}
//{{{ add another dom manipulation
$('.content_multiple')
    .bind('add_remove', function() {
        $('> .fields', this)
            .each(function(i) {
                if (i === 0)
                {
                    $(this)
                        .removeClass('content_multiple_fields_additional')
                        .removeClass('fields_mouseover')
                        .addClass('fields_mouseout');
                }
                else
                {
                    $(this)
                        .addClass('content_multiple_fields_additional');
                }
                $(':input', this)
                    .each(function() {
                        var input = $(this),
                            name = input.attr('name');
                        new_name = name.replace(/(\w+\[\d+\]\[\w+\]\[)\d+(\])?/, '$1' + i + '$2');
                        input
                            .attr('name', new_name)
                            .trigger('name_updated');
                    });
            });
    })
    .each(function() {
        $('> div.label', this).append(content_multiple_add.clone('true'));
        $('> .fields:gt(0)', this).addClass('content_multiple_fields_additional');
        $('> .fields', this)
            .addClass('fields_mouseout')
            .append(content_multiple_move_down.clone(true))
            .append(content_multiple_move_up.clone(true))
            .append(content_multiple_remove.clone(true));
    });
$('.content_multiple > .content_multiple_fields_additional')
    .live('mouseover', function() {
        $(this)
            .removeClass('fields_mouseout')
            .addClass('fields_mouseover');
    })
    .live('mouseout', function() {
        $(this)
            .removeClass('fields_mouseover')
            .addClass('fields_mouseout');
    });

//}}}
// {{{ collapsible items
var collapsible_items = $('.collapsible')
    collapsible_link = $('<a>Open</a>');
collapsible_link.click(function(){
    var el = $(this)
        text = el.text() == 'Open' ? 'Close' : 'Open';
    el.text(text)
        .next('.fields')
        .slideToggle();
});
$('.fields', collapsible_items)
    .before(collapsible_link.clone(true))
    .slideUp();

// }}}
//{{{ turn delete button into link
$("button[name='form[delete][data]']").each(function(){
    var el = $(this),
        link_delete = $('<input type="hidden" name="' + el.attr('name') + '" value="1"/><a class="delete">Delete this entry</a>');
    link_delete.click(function(){
        $(this).closest('form').submit();
    });
});

//}}}
});
