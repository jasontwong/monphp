<?php

if (!User::perm('edit content type'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

$field_group = Content::get_field_group_by_id(
    URI_PART_4,
    array('select' => array('fg.id', 'fg.name', 'fg.content_entry_type_id'))
);
$header = 'Delete Field Group &ldquo;'.$field_group['name'].'&rdquo;';
Admin::set('title', $header);
Admin::set('header', $header);
// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout(
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
        Content::delete_field_group_by_id(URI_PART_4);
        header('Location: /admin/module/Content/edit_type/'.$field_group['content_entry_type_id'].'/');
    }
    else
    {
        header('Location: /admin/module/Content/edit_field_gourp/'.$field_group['id'].'/');
    }
    exit;
}

//}}}
//{{{ type form build
$gform = new FormBuilderRows;
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
