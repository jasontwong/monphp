<?php

$entry_type = MPContent::get_entry_type_by_name(URI_PART_4);
if (is_null($entry_type))
{
    MPAdmin::notify(MPAdmin::TYPE_ERROR, 'That entry type does not exist');
    header('Location: /admin/');
    exit;
}
MPAdmin::set('title', 'Create New Entry');
MPAdmin::set('header', 'Add a new &ldquo;' . $entry_type['nice_name'] . '&rdquo;');
$entry_field_groups = &$entry_type['field_groups'];
// {{{ check user access
if ($user_access = MPUser::has_perm('add content entries type', 'add content entries type-' . $entry_type['name']))
{
    $user_access_level = MPContent::ACCESS_ALLOW;
}
else
{
    $user_access_level = MPContent::ACCESS_DENY;
}

$module_access_level = MPModule::h('mpcontent_entry_add_access', MPModule::TARGET_ALL, $entry_type['name']);
$access_level = max($module_access_level, $user_access_level);

if ($access_level < MPContent::ACCESS_ALLOW)
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    $efh = '';
    return;
}
// }}}
mp_enqueue_script(
    'mpcontent_field',
    '/admin/static/MPContent/field.js',
    array('jquery', 'tiny_mce'),
    FALSE,
    TRUE
);
// {{{ layout
$layout = new MPField();
$layout_sidebar = MPModule::h('mpcontent_entry_sidebar_new', MPModule::TARGET_ALL, URI_PART_4);
$esides = array();
foreach ($layout_sidebar as $mod => &$groups)
{
    if (!is_array($groups))
    {
        continue;
    }
    foreach ($groups as &$group)
    {
        $esides[] = $group;
        $glayout = $group['fields'];
        $layout->add_layout($glayout, $glayout['name']);
    }
}
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'title',
        'type' => 'text',
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'slug',
        'type' => 'text',
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => array_combine(
                        $entry_type['statuses'], 
                        $entry_type['statuses']
                    ),
                ),
            )
        ),
        'name' => 'status',
        'type' => 'dropdown',
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset',
    )
);
// {{{ custom fields
foreach ($entry_field_groups as &$entry_field_group)
{
    $rows = array();
    foreach ($entry_field_group['fields'] as &$entry_field)
    {
        $field = MPField::get_field($entry_field['id']);
        $fmeta = $fval = array();
        foreach ($field['meta'] as $nm => &$fm)
        {
            $fval[$nm] = $fm['default_data'];
            unset($fm['default_data']);
            $fmeta[$nm] = $fm;
        }
        $layout->add_layout(
            array(
                'field' => MPField::layout($field['type'], $field['meta']),
                'name' => $field['nice_name'],
                'type' => $field['type'],
                'required' => $field['required'],
                'array' => $field['multiple'],
                'value' => $fval,
            )
        );
        if (isset($_POST['data']))
        {
            $layout->merge($_POST['data']);
        }
        $flayout = $layout->get_layout($field['nice_name']);
        /*
        switch ($flayout['type'])
        {
            case 'file':
                $flayout['hidden']['delete'] = $flayout['array']
                    ? array(0 => TRUE)
                    : TRUE;
            break;
        }
        */
        $row['fields'] = $flayout;
        $row['label']['text'] = $field['nice_name'];
        if (strlen($field['description']))
        {
            $row['description']['text'] = $field['description'];
        }
        if ($flayout['array'])
        {
            $row['row']['attr']['class'] = 'content_multiple';
        }
        $rows[] = $row;
        unset($row);
    }
    if (!empty($rows))
    {
        $cfgroups[] = array(
            'attr' => array(
                'class' => 'clear tabbed'
            ),
            'label' => array(
                'text' => $entry_field_group['nice_name']
            ),
            'rows' => $rows
        );
    }
}
// }}}
// }}}
// {{{ form submission
if (ake('entry', $_POST))
{
    try
    {
        $content['entry'] = $layout->acts('post', $_POST['entry']);
        if (!isset($_POST['data']))
        {
            $_POST['data'] = array();
        }
        $content['data'] = $_POST['data'];
        $layout->merge($_POST['entry']);
        $layout->merge($_POST['data']);
        $entry_data = MPContent::save_entry($content, $entry_type);
        if (is_array($entry_data) && ake('_id', $entry_data))
        {
            MPModule::h('mpcontent_entry_sidebar_new_process', MPModule::TARGET_ALL, $layout, $entry_data, $_POST['module']);
            MPModule::h('mpcontent_entry_sidebar_new_process_' . $entry_type['name'], MPModule::TARGET_ALL, $layout, $entry_data, $_POST['module']);

            MPModule::h('mpcontent_entry_new_finish', MPModule::TARGET_ALL, $entry_data);
            MPModule::h('mpcontent_entry_new_finish_' . $entry_type['name'], MPModule::TARGET_ALL, $entry_data);

            MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'The entry was successfully created');
            header('Location: /admin/module/MPContent/edit_entry/' . $entry_data['_id']->{'$id'} . '/');
            exit;
        }
    }
    catch (Exception $e)
    {
        MPAdmin::notify(MPAdmin::TYPE_ERROR, 'The entry was unsuccessfully created');
    }
}
// }}}
// {{{ form build
$eform = new MPFormRows;
$eform->attr = array(
    'action' => URI_PATH,
    'enctype' => 'multipart/form-data',
    'method' => 'post'
);

// $form_sidebar = MPModule::h('mpcontent_entry_sidebar_new', MPModule::TARGET_ALL, URI_PART_4);

foreach ($esides as &$eside)
{
    $class = slugify($eside['label']['text']);
    $class .= $class === 'taxonomy'
        ? ' collapsible'
        : '';
    $eform->add_group(
        array(
            'attr' => array(
                'class' => $class,
            ),
            'rows' => array(
                $eside,
            ),
        ),
        'module'
    );
}
$eform->add_group(
    array(
        'attr' => array(
            'class' => 'tsc',
        ),
        'rows' => array(
            array(
                'row' => array(
                    'attr' => array(
                        'class' => 'status',
                    ),
                ),
                'fields' => $layout->get_layout('status'),
                'label' => array(
                    'text' => 'Status',
                ),
            ),
            array(
                'row' => array(
                    'attr' => array(
                        'class' => 'title',
                    ),
                ),
                'fields' => $layout->get_layout('title'),
                'label' => array(
                    'text' => 'Title',
                ),
            ),
            array(
                'row' => array(
                    'attr' => array(
                        'class' => 'slug',
                    )
                ),
                'fields' => $layout->get_layout('slug'),
                'label' => array(
                    'text' => 'URL Slug',
                ),
            ),
        ),
    ),
    'entry'
);
if (isset($cfgroups))
{
    foreach ($cfgroups as $cfgroup)
    {
        $eform->add_group($cfgroup, 'data');
    }
}
$eform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit'),
            ),
        ),
    ),
    'form'
);

$efh = $eform->build();
// }}}
