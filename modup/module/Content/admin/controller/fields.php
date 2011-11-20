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

Admin::set('title', 'Edit &ldquo;'.htmlentities($entry_type['name'], ENT_QUOTES).'&rdquo; Fields');
Admin::set('header', 'Edit &ldquo;'.htmlentities($entry_type['name'], ENT_QUOTES).'&rdquo; Fields');
$field_types = Field::type_options();
$field_group = array();
$fgs = Content::get_field_group_by_type_id(
    URI_PART_4,
    array('select' => array('fg.id', 'fg.name'))
);
foreach ($fgs as $fg)
{
    $field_group[$fg['id']] = $fg['name'];
}
//{{{ layout
$layout = new Field();
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
        'field' => Field::layout('textarea'),
        'name' => 'description',
        'type' => 'textarea'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $field_group
                )
            )
        ),
        'name' => 'content_field_group_id',
        'type' => 'dropdown'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('fieldtype'),
        'name' => 'type',
        'type' => 'fieldtype'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'multiple',
        'type' => 'checkbox_boolean'
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
//{{{ form submission
if (isset($_POST['form']))
{
    $data = $layout->acts('post', $_POST['field']);
    $types = $layout->acts('fieldtype', $_POST['field']);
    $data['type'] = $types['type'];
    /*
     TODO the old code missed lots of meta data, but does this API call store
     it correctly? re: what could ContentFieldMeta.meta be missing?
     */
    $field_type = new ContentFieldType;
    $data['type'] = $types['type']['type'];
    $field_type->merge($data);
    if ($field_type->isValid())
    {
        $field_type->save();
        $fmeta = $layout->meta($field_type->type);
        foreach (Field::layout($field_type->type, $field_type->id) as $name => $field)
        {
            $field_meta = new ContentFieldMeta;
            if (is_array($fmeta))
            {
                foreach ($fmeta as $key => $meta)
                {
                    if ($key === $name)
                    {
                        // TODO make another loop if the meta type has multiple keys
                        $mdata = $layout->acts('post', $meta['type'], array('meta', array('data' => $_POST['field']['meta'][$name])));
                        if ($mdata !== FALSE)
                        {
                            $field_meta->meta = $mdata;
                        }
                        break;
                    }
                }
            }
            $field_meta->name = $name;
            $field_meta->content_field_type_id = $field_type->id;
            if ($field_meta->isValid())
            {
                $field_meta->save();
            }
            else
            {
                $field_type->delete();
            }
            $field_meta->free();
        }
    }
    $field_type->free();
}

//}}}
//{{{ custom field form build
$fform = new FormBuilderRows;
$fform->attr = array(
    'action' => URI_PATH,
    'method' => 'post',
    'id' => 'custom-field'
);
$fform->label = array(
    'text' => 'New Custom Field'
);
$fform->add_group(
    array(
        'rows' => array(
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
            array(
                'fields' => $layout->get_layout('content_field_group_id'),
                'label' => array(
                    'text' => 'Field Group'
                ),
            ),
            array(
                'fields' => $layout->get_layout('multiple'),
                'label' => array(
                    'text' => 'Allow multiples of this field?'
                ),
            ),
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
$ffh = $fform->build();

//}}}
$field_groups = Content::get_entry_type_fields_by_id(URI_PART_4);

?>
