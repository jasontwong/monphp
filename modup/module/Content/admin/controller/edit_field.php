<?php

if (!User::perm('edit content type'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Edit Custom Field');
Admin::set('header', 'Edit Custom Field');

$field_details = Content::get_field_details_by_id(URI_PART_4);
if (empty($field_details))
{
    header('Location: /admin/');
    exit;
}

$field_type = &$field_details['type'];
$field_metas = &$field_details['meta'];
$entry_type = &$field_type['content_entry_type_id'];

$field_groups = array();
$field_types = Field::type_options();
$rows = Content::get_field_group_by_type_id($entry_type);
foreach ($rows as $row)
{
    $field_groups[$row['id']] = $row['name'];
}
//{{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'value' => array(
            'data' => $field_type['name']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'weight',
        'type' => 'text',
        'value' => array(
            'data' => $field_type['weight']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('textarea'),
        'name' => 'description',
        'type' => 'textarea',
        'value' => array(
            'data' => $field_type['description']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $field_groups
                )
            )
        ),
        'name' => 'content_field_group_id',
        'type' => 'dropdown',
        'value' => array(
            'data' => $field_type['content_field_group_id']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('fieldtype'),
        'name' => 'type',
        'type' => 'fieldtype',
        'value' => array(
            '_fieldtype' => $field_type['type']
        )
    )
);
$layout_multiple['field'] = Field::layout('checkbox_boolean');
$layout_multiple['name'] = 'multiple';
$layout_multiple['type'] = 'checkbox_boolean';
if ($field_type['multiple'])
{
    $layout_multiple['value'] = array('data' => $field_type['multiple']);
}
$layout->add_layout($layout_multiple);
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => URI_PART_4
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

//}}}
//{{{ form submitted
if (isset($_POST['field']))
{
    $fpost = $layout->acts('post', $_POST['field']);
    $types = $layout->acts('fieldtype', $_POST['field']);
    $layout->merge($_POST['field']);
    $fpost['multiple'] = (int)$fpost['multiple'];
    $fpost['type'] = $types['type']['type'];

    $cftt = Doctrine::getTable('ContentFieldType');
    $field_type = $cftt->find($fpost['id']);
    $field_type_before = $field_type->toArray();
    $field_type->merge($fpost);

    $same_type = $field_type_before['type'] == $field_type['type'];

    if ($field_type->isValid())
    {
        $field_type->save();
        $cfmt = Doctrine::getTable('ContentFieldMeta');
        $field_meta = $cfmt->findByContentFieldTypeId($field_type->id);

        $type_map = array();
        foreach ($types['type'] as $k => $type)
        {
            if (is_numeric($k))
            {
                $type_map[$type['name']] = $type;
            }
        }

        foreach ($field_meta as &$fm)
        {
            if ($same_type)
            {
                $pft = &$_POST['field']['type'];
                if (eka($pft, $fm->name))
                {
                    $fm->meta = $pft[$fm->name];
                }
            }

            if (eka($type_map, $fm->name))
            {
                $type_data = &$type_map[$fm->name];
                $fm->meta = $type_data['meta'];
                $fm->default_data = $type_data['default_data'];
            }
            if ($fm->isValid())
            {
                $fm->save();
            }
            else
            {
                $field_type->delete();
            }
        }
        $field_meta->free();
    }
    $field_type->free();
}

//}}}
//{{{ custom field form build
$fform = new FormBuilderRows;
$fform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$fform->label = array(
    'text' => 'Custom Field'
);
$fform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('id')
            ),
            array(
                'fields' => $layout->get_layout('name'),
                'label' => array(
                    'text' => 'Name'
                )
            ),
            array(
                'fields' => $layout->get_layout('weight'),
                'label' => array(
                    'text' => 'Weight'
                )
            ),
            array(
                'fields' => $layout->get_layout('description'),
                'label' => array(
                    'text' => 'Description'
                )
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
                )
            ),
            array(
                'fields' => $layout->get_layout('multiple'),
                'label' => array(
                    'text' => 'Allow multiples of this field?'
                )
            ),
            array(
                'fields' => $layout->get_layout('content_field_group_id'),
                'label' => array(
                    'text' => 'Field Group'
                )
            )
        )
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

?>
