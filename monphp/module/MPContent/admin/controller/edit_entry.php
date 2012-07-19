<?php
// {{{ prep
$entry = MPContent::get_entry_by_id(URI_PART_4);
if (is_null($entry))
{
    MPAdmin::notify(MPAdmin::TYPE_ERROR, 'That entry does not exist');
    header('Location: /admin/');
    exit;
}
$entry_type = MPContent::get_entry_type_by_name($entry['entry_type']['name']);
if (is_null($entry_type))
{
    MPAdmin::notify(MPAdmin::TYPE_ERROR, 'That entry does not belong to the entry type: ' . $entry_type['nice_name']);
    header('Location: /admin/');
    exit;
}
$entry_field_groups = &$entry_type['field_groups'];
$revision = NULL;
if (eka($_GET, 'revision'))
{
    $revision = $cemt->findEntryRevision($entry_id, $_GET['revision']); 
}
elseif (URI_PARTS > 5)
{
    $revision = $cemt->findEntryRevision($entry_id, URI_PART_5);
}

if (!is_null($revision))
{
    $entry = array_join($entry, $revision['entry']);
}
if ($user_access = MPUser::has_perm('edit content entries type', 'edit content entries type-'.$entry_type['name']))
{
    $user_access_level = MPContent::ACCESS_EDIT;
}
elseif ($user_access = MPUser::has_perm('view content entries type', 'view content entries type-'.$entry_type['name']))
{
    $user_access_level = MPContent::ACCESS_VIEW;
}
else
{
    $user_access_level = MPContent::ACCESS_DENY;
}

$module_access_level = MPModule::h('mpcontent_entry_edit_access', MPModule::TARGET_ALL, $entry_type['name'], URI_PART_4);
$access_level = max($module_access_level, $user_access_level);

if ($access_level === MPContent::ACCESS_DENY)
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    $efh = '';
    return;
}
MPAdmin::set('header', 'Edit ' . $entry['entry_type']['nice_name']);
MPAdmin::set('title', 'Edit Entry');

mp_enqueue_script(
    'mpcontent_field',
    '/admin/static/MPContent/field.js',
    array('jquery', 'tiny_mce'),
    FALSE,
    TRUE
);
// }}}
//{{{ layout
$layout = new MPField();
/*
$entry_sidebar = MPModule::h('mpcontent_entry_sidebar_edit', MPModule::TARGET_ALL, &$entry);
$esides = array();
foreach ($entry_sidebar as $mod => $groups)
{
    if (!is_array($groups))
    {
        continue;
    }
    foreach ($groups as $group)
    {
        $esides[] = $group;
        $glayout = $group['fields'];
        $layout->add_layout($glayout, $glayout['name']);
    }
}
*/
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'title',
        'type' => 'text',
        'value' => array(
            'data' => $entry['title']
        )
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'slug',
        'type' => 'text',
        'value' => array(
            'data' => $entry['slug']
        )
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
        'value' => array(
            'data' => $entry['status'],
        ),
    )
);
/*
$layout->add_layout(
    array(
        'field' => MPField::layout('date'),
        'name' => 'start_date',
        'type' => 'date',
        'value' => array(
            'data' => $entry['start_date']
        )
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('date'),
        'name' => 'end_date',
        'type' => 'date',
        'value' => array(
            'data' => $entry['end_date']
        )
    )
);
*/
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout(
            'submit',
            array(
                'data' => array(
                    'text' => 'Delete this entry'
                )
            )
        ),
        'name' => 'delete',
        'type' => 'submit'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout(
            'submit',
            array(
                'data' => array(
                    'text' => 'Duplicate this entry'
                )
            )
        ),
        'name' => 'duplicate',
        'type' => 'submit'
    )
);
// {{{ custom fields
foreach ($entry_field_groups as &$entry_field_group)
{
    $rows = array();
    foreach ($entry_field_group['fields'] as &$entry_field)
    {
        $field = MPField::get_field($entry_field['_id']);
        $fmeta = $fval = array();
        foreach ($field['meta'] as $nm => &$fm)
        {
            $fval[$nm] = $fm['default_data'];
            unset($fm['default_data']);
            $fmeta[$nm] = $fm;
        }
        $fval = array_merge($fval, deka(array(), $entry, 'data', $field['nice_name']));
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
//{{{ form submission
if (isset($_POST['form']))
{
    if ($access_level === MPContent::ACCESS_EDIT)
    {
        try
        {
            $form = $layout->acts('post', $_POST['form']);
            if (ake('delete', $form))
            {
                header('Location: /admin/module/MPContent/delete_entry/' . URI_PART_4 . '/');
                exit;
            }
            elseif (ake('submit', $form))
            {
                $content['entry'] = array_merge($layout->acts('post', $_POST['entry']), $entry);;
                if (!ake('data', $_POST))
                {
                    $_POST['data'] = array();
                }
                $content['data'] = $_POST['data'];
                $entry_data = MPContent::save_entry($content, $entry_type);
                MPModule::h('mpcontent_entry_edit_finish', MPModule::TARGET_ALL, $entry_data);
                MPModule::h('mpcontent_entry_edit_finish_' . $entry_type['name'], MPModule::TARGET_ALL, $entry_data);
                MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Entry successfully saved');
                header('Location: /admin/module/MPContent/edit_entry/' . URI_PART_4 . '/');
                exit;
            }
            elseif (ake('duplicate', $form))
            {
                $content['entry'] = $layout->acts('post', $_POST['entry']);
                $content['entry']['title'] .= ' COPY';
                $content['entry']['slug'] .= '-copy';
                if (!ake('data', $_POST))
                {
                    $_POST['data'] = array();
                }
                $content['data'] = $_POST['data'];
        
                $entry_data = MPContent::save_entry($content, $entry_type);
                if (is_array($entry_data) && ake('_id', $entry_data))
                {
                    MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Entry successfully duplicated');
                    MPModule::h('mpcontent_entry_new_finish', MPModule::TARGET_ALL, $entry_data);
                    MPModule::h('mpcontent_entry_new_finish_' . $entry_type['name'], MPModule::TARGET_ALL, $entry_data);
                    header('Location: /admin/module/MPContent/edit_entry/' . $entry_data['_id']->{'$id'} . '/');
                    exit;
                }
            }
        }
        catch (Exception $e)
        {
            MPAdmin::notify(MPAdmin::TYPE_ERROR, 'There was an error editing your entry');
        }
    }
    else
    {
        MPAdmin::notify(MPAdmin::TYPE_ERROR, 'You do not have access to save');
    }
}
elseif (isset($_POST['do']))
{
    switch ($_POST['do'])
    {
        case 'jump':
            header('Location: /admin/module/MPContent/edit_entry/'.URI_PART_4.'/'.$_POST['revision'].'/');
            exit;
        break;
        case 'set':
            if ($access_level === MPContent::ACCESS_EDIT)
            {
                $cemt->setEntryRevision(URI_PART_4, $_POST['revision']);
                //{{{ MPCache: updating block
                $entry_meta_id = URI_PART_4;
                $entry_type_info = MPContent::get_entry_type_by_entry_id($entry_meta_id);
                $entry_type_id = $entry_type_info['entry_type_id'];
                $content_type = MPContent::get_entry_type_details_by_id($entry_type_id);
                $content_type_name = $content_type['type']['name'];

                // MPCache: update single entry
                $entry = MPContent::get_entry_details_by_id($entry_meta_id, FALSE);
                MPCache::set('entry:'.$entry_meta_id, $entry, 0, 'MPContent');

                // MPCache: update all entries for content type
                $entries = MPContent::get_entries_details_by_type_id($entry_type_id, array(), FALSE);
                MPCache::set($content_type_name.' - entries', $entries, 0, 'MPContent');

                // MPCache: update ids slugs map for content type
                $ids_slugs = MPContent::get_entries_slugs($content_type_name, FALSE);
                MPCache::set($content_type['type']['name'].' - ids slugs', $ids_slugs, 0, 'MPContent');
                //}}}
                header('Location: /admin/module/MPContent/edit_entry/'.URI_PART_4.'/');
                exit;
            }
        break;
    }
}

//}}}
//{{{ form build
$eform = new MPFormRows;
$eform->attr = array(
    'action' => URI_PATH,
    'enctype' => 'multipart/form-data',
    'method' => 'post'
);
/*
foreach ($esides as $eside)
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
*/
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
if ($access_level === MPContent::ACCESS_EDIT)
{
    $eform->add_group(
        array(
            'attr' => array(
                'class' => 'buttons'
            ),
            'rows' => array(
                array(
                    'fields' => $layout->get_layout('submit')
                ),
                array(
                    'row' => array(
                        'attr' => array(
                            'class' => 'delete'
                        )
                    ),
                    'fields' => $layout->get_layout('delete')
                ),
                array(
                    'row' => array(
                        'attr' => array(
                            'class' => 'duplicate'
                        )
                    ),
                    'fields' => $layout->get_layout('duplicate')
                )
            )
        ),
        'form'
    );
}

$efh = $eform->build();

//}}}
