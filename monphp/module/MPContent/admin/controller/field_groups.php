<?php

if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

$entry_type = MPContent::get_entry_type_by_name(URI_PART_4);

if (!$entry_type)
{
    header('Location: /admin/');
    exit;
}

MPAdmin::set('title', 'Edit &ldquo;'.htmlentities($entry_type['nice_name'], ENT_QUOTES).'&rdquo; Field Groups');
MPAdmin::set('header', 'Edit &ldquo;'.htmlentities($entry_type['nice_name'], ENT_QUOTES).'&rdquo; Field Groups');
// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'nice_name',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'weight',
        'type' => 'text',
        'value' => array(
            'data' => 0,
        ),
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
        MPContent::save_entry_type($entry_type);
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Group successfully created.');
        header('Location: /admin/module/MPContent/edit_type/' . $entry_type['name'] . '/');
        exit;
    }
    catch (Exception $e)
    {
        $layout->merge($_POST['field_group']);
        MPAdmin::notify(MPAdmin::TYPE_ERROR, 'There was an error creating the group.');
    }
}

//}}}
//{{{ field group form build
$field_groups = $entry_type['field_groups'];
$gform = new MPFormRows;
$gform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$gform->label = array(
    'text' => 'New MPField Group'
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
