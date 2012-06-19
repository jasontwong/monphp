<?php

if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

MPAdmin::set('title', 'Edit Field Group');
MPAdmin::set('header', 'Edit Field Group');

$entry_type = MPContent::get_entry_type_by_name(URI_PART_4);
$field_group = array();
foreach ($entry_type['field_groups'] as &$group)
{
    if ($group['name'] === URI_PART_5)
    {
        $field_group = &$group;
        break;
    }
}
// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'nice_name',
        'type' => 'text',
        'value' => array(
            'data' => $field_group['nice_name'],
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
    $fpost['name'] = slugify($fpost['nice_name']);
    if (!is_numeric($fpost['weight']))
    {
        $fpost['weight'] = 0;
    }
    $field_group = array_merge($field_group, $fpost);
    $save = FALSE;
    if ($field_group['name'] === $fpost['name'])
    {
        $save = TRUE;
    }
    if (!$save)
    {
        $save = TRUE;
        foreach ($entry_type['field_groups'] as &$group)
        {
            if ($group['name'] === $field_group['name'])
            {
                $save = FALSE;
                break;
            }
        }
    }
    if ($save)
    {
        MPContent::save_entry_type($entry_type);
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Group successfully edited.');
        header('Location: /admin/module/MPContent/field_groups/' . $entry_type['name'] . '/');
        exit;
    }
    $layout->merge($_POST['field_group']);
}

//}}}
//{{{ field group form build
$gform = new MPFormRows;
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
