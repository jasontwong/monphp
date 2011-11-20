/*
$(document).ready(function() {

var list = '<div id="content_category_list"><h3>Current Categories</h3><ul class="ajax">';
$('form#content_category_list table tbody tr').each(function() {
    list += '<li><a category="' + $('input', this).val() + '">' + $('a', this).text() + '</a></li>';
});
list += '</ul></div>';

$('form#content_category_list').remove();
$('form#content_category_detail').after(list);
$('#content_category_list a').live('click', function() {
    var uri = '/admin/rpc/content/get_category/' + $(this).attr('category') + '/';
    $.get(uri, null, function(d, s) {
        if (s == 'success' && d != false)
        {
            var form = $('form#content_category_detail').attr('name', 'edit_category');
            $('div.form_label', form)
                .text('Edit Category')
                .append(' [<a>add new category</a>]');
            $('input[name="category[name][data][]"]', form).val(d.name);
            $('input[name="category[slug][data][]"]', form).val(d.slug);
            form.append('<input type="hidden" name="category[id][data][]" value="' + d.id + '" />' + 
                        '<input type="hidden" name="category[id][type]" value="hidden" />');
            if (d.parent_id == null)
            {
                d.parent_id = '';
            }
            $('select[name="category[parent_id][data][]"] option[value="' + d.parent_id +'"]', form).attr('selected', 'selected');

            $('div.form_label a', form).click(function() {
                $('form#content_category_detail').attr('name', 'add_category');
                $('form#content_category_detail div.form_label').text('New Category');
                $('form#content_category_detail input[name="category[name][data][]"], form#content_category_detail input[name="category[slug][data][]"]').val('');
                $('form#content_category_detail select[name="category[parent_id][data][]"] option[value=""]').attr('selected', 'selected');
                $('form#content_category_detail input[name="category[id][data][]"], form#content_category_detail input[name="category[id][type]"]').remove();
                return false;
            });
        }
    }, 'json');
    $('form#content_category_detail input[name="category[id][data][]"], form#content_category_detail input[name="category[id][type]"]').remove();
    return false;
});

$('form#content_category_detail').submit(function(e) {
    var form = $(this),
        action = form.attr('name'),
        data = form.serialize(),
        uri = '/admin/rpc/content/' + action + '/';
    console.log(uri);
    $.get(uri, data, function(d, s) {
        if (d.success)
        {
            $('#content_category_list ul').append('<li><a category="' + d.category.id + '">' + d.category.name + '</a></li>');
        }
        else
        {
            console.log(d.category.name);
        }
    }, 'json');
    return false;
});

});
*/
