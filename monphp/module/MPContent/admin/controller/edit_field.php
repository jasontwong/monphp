<?php

if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

$entry_type = MPContent::get_entry_type_by_name(URI_PART_4);
if (is_null($entry_type))
{
    header('Location: /admin/');
    exit;
}
$entry_field_group = $entry_field = $entry_field_data = array();
foreach ($entry_type['field_groups'] as &$fg)
{
    if (URI_PART_5 === $fg['name'])
    {
        $entry_field_group = &$fg;
        foreach ($entry_field_group['fields'] as &$fgf)
        {
            if (URI_PART_6 === $fgf['name'])
            {
                $entry_field = &$fgf;
                $entry_field_data = array_merge($fgf, MPField::get_field($fgf['id']));
                break;
            }
        }
        break;
    }
}

mp_enqueue_script(
    'mpcontent_field_type',
    '/admin/static/MPContent/field.type.js',
    array('jquery'),
    FALSE,
    TRUE
);

MPAdmin::set('title', 'Edit &ldquo;'.htmlentities($entry_type['nice_name'], ENT_QUOTES).'&rdquo; &rarr; &ldquo;'.hsc($entry_field_group['nice_name']).'&rdquo; Fields');
MPAdmin::set('header', 'Edit &ldquo;'.htmlentities($entry_type['nice_name'], ENT_QUOTES).'&rdquo; &rarr; &ldquo;'.hsc($entry_field_group['nice_name']).'&rdquo; Fields');
$entry_field_types = MPField::type_options();
$entry_field_groups = &$entry_type['field_groups'];
//{{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'nice_name',
        'type' => 'text',
        'value' => array(
            'data' => $entry_field_data['nice_name'],
        ),
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'weight',
        'type' => 'text',
        'value' => array(
            'data' => $entry_field_data['weight'],
        ),
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('textarea'),
        'name' => 'description',
        'type' => 'textarea',
        'value' => array(
            'data' => $entry_field_data['description'],
        ),
    )
);
$entry_field_group_options = array();
foreach ($entry_field_groups as &$group)
{
    $entry_field_group_options[$group['name']] = $group['nice_name'];
}
$layout->add_layout(
    array(
        'field' => MPField::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $entry_field_group_options,
                ),
            )
        ),
        'name' => 'field_group_name',
        'type' => 'dropdown',
        'value' => array(
            'data' => $entry_field_group['name'],
        ),
    )
);
$types = MPField::types();
$type_metas = $type_options = array();
foreach ($types as $k => &$type)
{
    $type_options[$k] = $type['name'];
    if ($type['meta'])
    {
        $type_metas[$k] = $k;
    }
}
$layout->add_layout(
    array(
        'field' => MPField::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $type_options,
                ),
            )
        ),
        'name' => 'type',
        'type' => 'dropdown',
        'value' => array(
            'data' => $entry_field_data['type'],
        ),
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('checkbox_boolean'),
        'name' => 'multiple',
        'type' => 'checkbox_boolean',
        'value' => array(
            'data' => $entry_field_data['multiple'],
        ),
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('checkbox_boolean'),
        'name' => 'required',
        'type' => 'checkbox_boolean',
        'value' => array(
            'data' => $entry_field_data['required'],
        ),
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset',
    )
);
$meta_fields = array();
foreach ($type_metas as &$meta)
{
    $fmeta = $entry_field_data['type'] === $meta ? $entry_field_data['meta'] : array();
    $fields = MPField::quick_act('meta', $meta, $fmeta);
    foreach ($fields as $key => &$field)
    {
        $name = array(
            $meta,
            $key,
        );
        $hash = sha1(serialize($name) . serialize($field));
        $name[] = $hash;
        $field['name'] = $name;
        $layout->add_layout(
            $field,
            $hash
        );
        $meta_fields[] = array(
            'label' => ake('label', $field) ? $field['label'] : '',
            'key' => $hash,
            'type' => $meta,
        );
    }
}

//}}}
//{{{ form submission
if (isset($_POST['form']))
{
    try
    {
        $data = $layout->acts('post', $_POST['field']);
        $data['name'] = slugify($data['nice_name']);
        if (ake('type', $_POST))
        {
            $ftdata = array();
            foreach ($_POST['type'] as &$type)
            {
                foreach ($type as $k => &$pdata)
                {
                    $tmp = $layout->acts('post', $pdata);
                    $ftdata[$k] = array_shift($tmp);
                }
            }
        }
        $data['meta'] = MPField::quick_act('fieldtype', $data['type'], $ftdata);
        $data = array_merge($entry_field_data, $data);
        if ($entry_field_group['name'] !== $data['field_group_name'])
        {
            MPContent::save_entry_field($entry_field_groups, $data);
            $entry_field = array();
            foreach ($entry_field_group['fields'] as $k => &$v)
            {
                if (empty($v))
                {
                    unset($entry_field_group['fields'][$k]);
                }
            }
        }
        else
        {
            $field = MPField::register_field($data);
            $entry_field['name'] = $data['name'];
            $entry_field['weight'] = $data['weight'];
        }
        MPContent::save_entry_type($entry_type);
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Field successfully updated');
        header('Location: /admin/module/MPContent/edit_field/' . $entry_type['name'] . '/' . $entry_field_group['name'] . '/' . $field['name'] . '/');
        exit;
    }
    catch (Exception $e)
    {
        MPAdmin::notify(MPAdmin::TYPE_ERROR, 'Field unsuccessfully updated');
    }
}

//}}}
//{{{ custom field form build
$fform = new MPFormRows;
$fform->attr = array(
    'action' => URI_PATH,
    'method' => 'post',
    'id' => 'custom-field'
);
$fform->label = array(
    'text' => 'Edit &ldquo;' . hsc($entry_field_data['nice_name']) . '&rdquo; Field'
);
$fform->add_group(
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
            array(
                'fields' => $layout->get_layout('description'),
                'label' => array(
                    'text' => 'Description'
                ),
            ),
            array(
                'row' => array(
                    'attr' => array(
                        'class' => 'field_type'
                    )
                ),
                'fields' => $layout->get_layout('type'),
                'label' => array(
                    'text' => 'Field Type'
                ),
            ),
        )
    ),
    'field'
);
$rows = array();
foreach ($meta_fields as &$meta_field)
{
    $rows[] = array(
        'row' => array(
            'attr' => array(
                'class' => $meta_field['type'] . ' hiddens',
                'data-type' => $meta_field['type'],
            ),
        ),
        'fields' => $layout->get_layout($meta_field['key']),
        'label' => array(
            'text' => $meta_field['label'],
        ),
    );
}
$fform->add_group(
    array(
        'attr' => array(
            'class' => 'fieldtype',
        ),
        'rows' => $rows,
    ),
    'type'
);
$fform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('field_group_name'),
                'label' => array(
                    'text' => 'Field Group'
                ),
            ),
            array(
                'fields' => $layout->get_layout('required'),
                'label' => array(
                    'text' => 'Make this field required?'
                ),
            ),
            array(
                'fields' => $layout->get_layout('multiple'),
                'label' => array(
                    'text' => 'Allow multiples of this field?'
                ),
            ),
        ),
    ),
    'field'
);
$fform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            )
        )
    ),
    'form'
);
$cfh = $fform->build();

//}}}
