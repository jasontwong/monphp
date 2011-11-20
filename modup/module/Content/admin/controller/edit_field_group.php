<?php

if (!User::perm('edit content type'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Edit Field Group');
Admin::set('header', 'Edit Field Group');

$field_group = Content::get_field_group_by_id(
    URI_PART_4,
    array('select' => array('fg.id', 'fg.name', 'fg.weight', 'fg.content_entry_type_id'))
);
// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => URI_PART_4
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'content_entry_type_id',
        'type' => 'hidden',
        'value' => array(
            'data' => $field_group['content_entry_type_id']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'value' => array(
            'data' => $field_group['name']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'weight',
        'type' => 'text',
        'value' => array(
            'data' => $field_group['weight']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

// }}}
//{{{ form submission
if (isset($_POST['field_group']))
{
    $fpost = $layout->acts('post', $_POST['field_group']);
    $layout->merge($_POST['field_group']);
    Content::save_field_group($fpost);
    header('Location: /admin/module/Content/field_groups/'.$fpost['content_entry_type_id'].'/');
    exit;
}

//}}}
//{{{ field group form build
$gform = new FormBuilderRows;
$gform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$gform->label = array(
    'text' => 'Field Group'
);
$gform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('content_entry_type_id'),
                'hidden' => TRUE
            ),
            array(
                'fields' => $layout->get_layout('id'),
                'hidden' => TRUE
            ),
            array(
                'fields' => $layout->get_layout('name'),
                'label' => array(
                    'text' => 'Name'
                ),
            ),
            array(
                'fields' => $layout->get_layout('weight'),
                'label' => array(
                    'text' => 'Weight'
                ),
            ),
        )
    ),
    'field_group'
);
$gform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit'),
            )
        )
    ),
    'form'
);
$gfh = $gform->build();

//}}}

?>
