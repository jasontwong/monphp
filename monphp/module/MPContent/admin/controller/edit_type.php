<?php
// {{{ prep
if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

$entry_type = MPContent::get_type_by_name(URI_PART_4);
if (!$entry_type)
{
    MPAdmin::notify(MPAdmin::TYPE_ERROR, 'That entry type does not exist');
    header('Location: /admin/module/MPContent/edit_types/');
    exit;
}

MPAdmin::set('title', 'Edit Content Type &ldquo;'.htmlentities($entry_type['nice_name'], ENT_QUOTES).'&rdquo;');
MPAdmin::set('header', 'Edit Content Type &ldquo;'.htmlentities($entry_type['nice_name'], ENT_QUOTES).'&rdquo;');

$other_links = MPModule::h('mpcontent_edit_type_other_links', MPModule::TARGET_ALL, URI_PART_4);
// }}}
// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'nice_name',
        'type' => 'text',
        'value' => array(
            'data' => $entry_type['nice_name'],
        ),
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('textarea'),
        'name' => 'description',
        'type' => 'textarea',
        'value' => array(
            'data' => $entry_type['description'],
        ),
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('textarea_array'),
        'name' => 'statuses',
        'type' => 'textarea_array',
        'value' => array(
            'data' => implode("\n", $entry_type['statuses']),
        ),
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('checkbox_boolean'),
        'name' => 'ordering',
        'type' => 'checkbox_boolean',
        'value' => array(
            'data' => $entry_type['ordering'],
        ),
    )
);

$hook_layouts = MPModule::h('mpcontent_edit_type_layout', MPModule::TARGET_ALL, $layout, $entry_type);
foreach ($hook_layouts as $module => $h_layouts)
{
    if (is_array($h_layouts))
    {
        foreach ($h_layouts as $h_layout)
        {
            $layout->add_layout($h_layout);
        }
    }
}

$layout->add_layout(
    array(
        'field' => MPField::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

// }}}
// {{{ form submission
if (isset($_POST['form']))
{
    try
    {
        $data = $layout->acts('post', $_POST['content_type']);
        $layout->merge($_POST['content_type']);
        $entry_type = array_merge($entry_type, $data);
        MPContent::save_type($entry_type);
        MPModule::h('mpcontent_edit_type_process', MPModule::TARGET_ALL, $layout, $entry_type, $_POST);
        MPModule::h('mpcontent_edit_type_process_' . $entry_type['name'], MPModule::TARGET_ALL, $layout, $entry_type, $_POST);
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Entry type successfully updated');
    }
    catch (Exception $e)
    {
        MPAdmin::notify(MPAdmin::TYPE_ERROR, 'There was a problem updating the entry type');
    }
}

// }}}
// {{{ type form build
$tform = new MPFormRows;
$tform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$tform->label = array(
    'text' => 'Edit content type'
);
$tform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('nice_name'),
                'label' => array(
                    'text' => 'Name'
                ),
            ),
            array(
                'fields' => $layout->get_layout('description'),
                'label' => array(
                    'text' => 'Description'
                ),
            ),
            array(
                'fields' => $layout->get_layout('statuses'),
                'label' => array(
                    'text' => 'Statuses'
                ),
            ),
            array(
                'fields' => $layout->get_layout('ordering'),
                'label' => array(
                    'text' => 'Manually ordered'
                )
            ),
        )
    ),
    'content_type'
);

$hook_forms = MPModule::h('mpcontent_edit_type_form', MPModule::TARGET_ALL, $layout, $entry_type);
foreach ($hook_forms as $module => $h_forms)
{
    $tform->add_group($h_forms);
}

$tform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            )
        )
    ),
    'form'
);
$tfh = $tform->build();

// }}}
