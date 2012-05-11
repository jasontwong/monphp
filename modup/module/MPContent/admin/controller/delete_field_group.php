<?php

if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

$field_group = MPContent::get_field_group_by_id(
    URI_PART_4,
    array('select' => array('fg.id', 'fg.name', 'fg.content_entry_type_id'))
);
$header = 'Delete MPField Group &ldquo;'.$field_group['name'].'&rdquo;';
MPAdmin::set('title', $header);
MPAdmin::set('header', $header);
// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout(
            'submit_confirm',
            array(
                'submit' => array(
                    'text' => 'Delete this field group'
                ),
                'cancel' => array(
                    'text' => 'Cancel'
                )
            )
        ),
        'name' => 'do',
        'type' => 'submit_confirm'
    )
);

// }}}
//{{{ type form submission
if (isset($_POST['confirm']))
{
    $confirm = $layout->acts('post', $_POST['confirm']);
    if ($confirm['do'])
    {
        MPContent::delete_field_group_by_id(URI_PART_4);
        header('Location: /admin/module/MPContent/edit_type/'.$field_group['content_entry_type_id'].'/');
    }
    else
    {
        header('Location: /admin/module/MPContent/edit_field_gourp/'.$field_group['id'].'/');
    }
    exit;
}

//}}}
//{{{ type form build
$gform = new MPFormBuilderRows;
$gform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$gform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('do')
            )
        )
    ),
    'confirm'
);
$gfh = $gform->build();

//}}}

?>
