<?php 
$field_types = Field::types();
$autoslug = Data::query('Content', 'autoslug');
$autoslug = is_string($autoslug) && strlen($autoslug) === 1 ? $autoslug : '-';
?>
$(document).ready(function() {
// TODO copy this function for fields with default data?
//{{{ content type meta field switchup
$('.field_type select[name^="field[type][_fieldtype]"]').change(function() {

    var select = $(this),
        selected = select.val(),
        fields = select.parent().parent(),
        meta = new Array(),
        desc = new Array(),
        field = new Array(),
        label = new Array(),
        label_value = new Array(),
        required = new Array(),
        required_value = new Array(),
        type = new Array();
    $('div.meta', fields).remove();
    switch (selected)
    {

        <?php foreach ($field_types as $type => $info): ?>

            case '<?php echo $type ?>':
                <?php 
                    if ($info['meta'])
                    {
                        $fields = Field::quick_act('meta', $type);
                        foreach ($fields as $fk => $v)
                        {
                            echo 'meta["'.$fk.'"] = true;';
                            echo 'desc["'.$fk.'"] = "'.$v['description'].'";';
                            echo 'label["'.$fk.'"] = "'.deka(FALSE, $v, 'label_field').'";';
                            echo 'label_value["'.$fk.'"] = "'.deka('', $v, 'label_value').'";';
                            echo 'field["'.$fk.'"] = "'.$v['field'].'";';
                            echo 'required["'.$fk.'"] = "'.deka(FALSE, $v, 'required_option').'";';
                            echo 'required_value["'.$fk.'"] = "'.deka(FALSE, $v, 'required_value').'";';
                            echo 'type["'.$fk.'"] = "'.$v['type'].'";';
                        }
                    }
                    else
                    {
                        echo 'meta = false;';
                    }
                ?>
            break;

        <?php endforeach ?>

        default:
            meta = false;
        break;

    }
    if (meta)
    {
        var i, div,
            div_field, div_description, 
            div_label, div_required;
        for (i in meta)
        {
            div_label = label[i] ? '<div class="label"><label><input type="text" class="text" name="field[type][_label_'+i+']" value="">Optional label for this field</label></div>' : '';
            div_field = '<div class="field">' + field[i] + '</div>';
            div_required = required[i] ? '<div class="required"><input type="hidden" class="hidden" name="field[type][_required_'+i+']" value="0"><label><input type="checkbox" class="checkbox" name="field[type][_required_'+i+']" value="1">This field is required</label></div>' : '';
            div_description = desc[i] ? '<div class="description">' + desc[i] + '</div>' : '';

            div = $('<div class="meta meta_'+i+'">' + div_label + div_field + div_required + div_description + '</div>');
            switch (type[i])
            {
                case 'checkbox':
                    $('div.field :input', div).attr('name', 'field[type]['+i+'][]');
                break;
                default:
                    $('div.field :input', div).attr('name', 'field[type]['+i+']');
            }
            fields.append(div);
        }
    }
});

$('form').each(function() {
    $('.field_type select[name^="field[type][_fieldtype]"]', this).change();
});

//}}}
//{{{ title and auto-slugging
$('input[name="entry[slug][data]"], ' +
  'input[name="field[slug][data]"], ' + 
  'input[name="category[slug][data]"]')
    .data('auto_slug', true)
    .keypress(function() {
        $(this).data('auto_slug', false);
    });

$('input[name="entry[title][data]"], ' +
  'input[name="field[name][data]"], ' + 
  'input[name="category[name][data]"]')
    .data('slug', false)
    .keyup(function(e) {
        var input = $(this),
            slug = input.data('slug'),
            new_char = '<?php echo $autoslug; ?>';
        if (slug === false)
        {
            var sname = input.attr('name').replace(/^([^\[]+)\[(?:title|name)\](.+)$/, '$1[slug]$2');
            slug = $('input[name="' + sname + '"]');
            input.data('slug', slug);
        }
        if (slug.data('auto_slug'))
        {
            var val = $(this).val()
                        .replace(/[^\w\s-_]+/g, '')
                        .replace(/[\s-_ ]+/g, new_char)
                        .toLowerCase(),
                end = val.length - 1;
            if (val.charAt(0) == new_char)
            {
                val = val.substr(1);
            }
            if (val.charAt(end) == new_char)
            {
                val = val.substr(0, end);
            }
            slug.val(val);
        }
        e.stopImmediatePropagation();
    });

//}}}
});
