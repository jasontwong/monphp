<?php

if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

MPAdmin::set('title', 'Edit MPField Group');
MPAdmin::set('header', 'Edit MPField Group');

$field_group = MPContent::get_field_group_by_id(
    URI_PART_4,
    array('select' => array('fg.id', 'fg.name', 'fg.weight', 'fg.content_entry_type_id'))
);
// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => URI_PART_4
        )
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('hidden'),
        'name' => 'content_entry_type_id',
        'type' => 'hidden',
        'value' => array(
            'data' => $field_group['content_entry_type_id']
        )
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'value' => array(
            'data' => $field_group['name']
        )
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'weight',
        'type' => 'text',
        'value' => array(
            'data' => $field_group['weight']
        )
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_reset'),
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
    MPContent::save_field_group($fpost);
    header('Location: /admin/module/MPContent/field_groups/'.$fpost['content_entry_type_id'].'/');
    exit;
}

//}}}
//{{{ field group form build
$gform = new MPFormRows;
$gform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$gform->label = array(
    'text' => 'MPField Group'
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
