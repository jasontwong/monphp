<?php $field_types = MPField::types(); ?>
(function($){
    "use strict";
    $(function() {
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
                                $fields = MPField::quick_act('meta', $type);
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
    });
}(jQuery));
