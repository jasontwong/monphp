<?php

$entry_type = MPContent::get_entry_type_by_name(URI_PART_4);
if (is_null($entry_type))
{
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
        'type' => 'text'
    )
);
/*
$layout->add_layout(
    array(
        'field' => MPField::layout('hidden'),
        'name' => 'content_entry_type_name',
        'type' => 'hidden',
        'value' => array(
            'data' => URI_PART_4
        )
    )
);
*/
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'slug',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('date'),
        'name' => 'start_date',
        'type' => 'date'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('date'),
        'name' => 'end_date',
        'type' => 'date'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
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
                // 'name' => $field['_id']->{'$id'},
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
        // $content['meta'] = $layout->acts('post', $_POST['meta']);
        if (!isset($_POST['data']))
        {
            $_POST['data'] = array();
        }
        // $content['data'] = $layout->acts('save', $_POST['data'], $content['entry']);
        $content['data'] = $layout->acts('post', $_POST['data']);
        $layout->merge($_POST['entry']);
        // $layout->merge($_POST['meta']);
        $layout->merge($_POST['data']);
        var_dump($content, $entry_type);
        die;
        $entry_data = MPContent::save_entry($content, $entry_type);
        if (ake('_id', $entry_data))
        {
            $content['meta']['content_entry_meta_id'] = $eid;
            MPModule::h('mpcontent_entry_sidebar_new_process', MPModule::TARGET_ALL, $layout, $content['meta'], $_POST['module']);

            /*
            //{{{ MPCache: updating block
            $content_type = MPContent::get_entry_type_details_by_id($content['meta']['content_entry_type_name']);
            $content_type_name = $content_type['type']['name'];

            // MPCache: update single entry
            $entry = MPContent::get_entry_details_by_id($eid, FALSE);
            MPCache::set('entry:'.$eid, $entry, 0, 'MPContent');

            // MPCache: update all entries for content type
            $entries = MPContent::get_entries_details_by_type_id($content['meta']['content_entry_type_id'], array(), FALSE);
            MPCache::set($content_type_name.' - entries', $entries, 0, 'MPContent');

            // MPCache: update ids slugs map for content type
            $ids_slugs = MPContent::get_entries_slugs($content_type_name, FALSE);
            MPCache::set($content_type['type']['name'].' - ids slugs', $ids_slugs, 0, 'MPContent');
            //}}}
            */

            MPModule::h('mpcontent_entry_new_finish', MPModule::TARGET_ALL, $content['meta']);

            MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'The entry was successfully created');
            header('Location: /admin/module/MPContent/edit_entry/' . $eid->{'$id'} . '/');
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

//$form_sidebar = MPModule::h('mpcontent_entry_sidebar_new', MPModule::TARGET_ALL, URI_PART_4);

foreach ($esides as &$eside)
{
    $class = slugify($eside['label']['text']);
    $class .= $class === 'taxonomy'
        ? ' collapsible'
        : '';
    $eform->add_group(
        array(
            'attr' => array(
                'class' => $class
            ),
            'rows' => array(
                $eside
            )
        ),
        'module'
    );
}
$eform->add_group(
    array(
        'attr' => array(
            'class' => 'tsc'
        ),
        'rows' => array(
            array(
                'row' => array(
                    'attr' => array(
                        'class' => 'title'
                    )
                ),
                'fields' => $layout->get_layout('title'),
                'label' => array(
                    'text' => 'Title'
                )
            ),
            array(
                'row' => array(
                    'attr' => array(
                        'class' => 'slug'
                    )
                ),
                'fields' => $layout->get_layout('slug'),
                'label' => array(
                    'text' => 'URL Slug'
                )
            )
        )
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
/*
$eform->add_group(
    array(
        'attr' => array(
            'class' => 'hiddens'
        ),
        'rows' => array(
            array(
                'fields' => $layout->get_layout('content_entry_type_name')
            )
        )
    ),
    'meta'
);
if ($entry_type->status || $entry_type->flagging)
{
    $srows[] = array(
        'fields' => $layout->get_layout('date_control'),
        'label' => array(
            'text' => 'Option to use with date'
        )
    );
    $srows[] = array(
        'fields' => $layout->get_layout('start_date'),
        'label' => array(
            'text' => 'Begin with this date'
        )
    );
    $srows[] = array(
        'fields' => $layout->get_layout('end_date'),
        'label' => array(
            'text' => 'End with this date'
        )
    );
}
if (isset($srows))
{
    $eform->add_group(
        array(
            'attr' => array(
                'class' => 'status_flags'
            ),
            'rows' => $srows,
        ),
        'meta'
    );
}
*/

$eform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            )
        )
    ),
    'form'
);

$efh = $eform->build();

// }}}
