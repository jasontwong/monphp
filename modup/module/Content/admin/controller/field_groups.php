<?php

if (!User::perm('edit content type'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

$entry_type = Content::get_entry_type_by_name(URI_PART_4);

if (!$entry_type)
{
    header('Location: /admin/');
    exit;
}

Admin::set('title', 'Edit &ldquo;'.htmlentities($entry_type['nice_name'], ENT_QUOTES).'&rdquo; Field Groups');
Admin::set('header', 'Edit &ldquo;'.htmlentities($entry_type['nice_name'], ENT_QUOTES).'&rdquo; Field Groups');
// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'nice_name',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'weight',
        'type' => 'text',
        'value' => array(
            'data' => 0,
        ),
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
    var_dump($data);
    exit;
    try
    {
        $data['name'] = slugify($data['nice_name']);
        $data['fields'] = array();
        if (!is_numeric($data['weight']))
        {
            $data['weight'] = 0;
        }
        $entry_type['field_groups'][] = $data;
        Content::save_entry_type($entry_type);
        Admin::notify(Admin::TYPE_SUCCESS, 'Group successfully created.');
        header('Location: /admin/module/Content/edit_type/' . $entry_type['name'] . '/');
        exit;
    }
    catch (Exception $e)
    {
        $layout->merge($_POST['field_group']);
        Admin::notify(Admin::TYPE_ERROR, 'There was an error creating the group.');
    }
}

//}}}
//{{{ field group form build
$field_groups = $entry_type['field_groups'];
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
                'fields' => $layout->get_layout('nice_name'),
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
