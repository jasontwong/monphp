<?php

if (!User::perm('edit content type'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

$entry_type = Content::get_entry_type_by_id(
    URI_PART_4,
    array('select' => array('ety.name'))
);

if (!$entry_type)
{
    header('Location: /admin/');
    exit;
}

Admin::set('title', 'Edit &ldquo;'.htmlentities($entry_type['name'], ENT_QUOTES).'&rdquo; Field Groups');
Admin::set('header', 'Edit &ldquo;'.htmlentities($entry_type['name'], ENT_QUOTES).'&rdquo; Field Groups');
// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'content_entry_type_id',
        'type' => 'hidden',
        'value' => array(
            'data' => URI_PART_4
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'weight',
        'type' => 'text'
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
if (isset($_POST['form']))
{
    $data = $layout->acts('post', $_POST['field_group']);
    try
    {
        Content::save_field_group($data);
    }
    catch (Exception $e)
    {
        $layout->merge($_POST['field_group']);
        echo 'error';
    }
}

//}}}
//{{{ field group form build
$field_groups = Content::get_entry_type_fields_by_id(URI_PART_4);
$gform = new FormBuilderRows;
$gform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$gform->label = array(
    'text' => 'New Field Group'
);
$gform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('content_entry_type_id'),
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
