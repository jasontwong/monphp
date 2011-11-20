<?php

// {{{ field layout
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
        'field' => Field::layout('textarea'),
        'name' => 'description',
        'type' => 'textarea'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'status',
        'type' => 'checkbox_boolean'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'flagging',
        'type' => 'checkbox_boolean'
    )
);

$hook_layouts = Module::h('content_new_type_layout');
if ($hook_layouts)
{
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
}

$layout->add_layout(
    array(
        'field' => Field::layout(
            'submit_reset',
            array(
                'submit' => array(
                    'text' => 'Save'
                )
            )
        ),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

// }}}
//{{{ form build
$form = new FormBuilderRows;
$form->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$form->label = array(
    'text' => 'Add new content type'
);

$type_group = array(
    'rows' => array(
        array(
            'fields' => $layout->get_layout('name'),
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
    )
);
$form->add_group($type_group, 'content_type');

$hook_forms = Module::h('content_new_type_form', Module::TARGET_ALL, &$layout);
foreach ($hook_forms as $module => $h_forms)
{
    $form->add_group($h_forms);
}

$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit'),
            )
        )
    )
);

$fh = $form->build();

//}}}
//{{{ form submission
if (isset($_POST['content_type']))
{
    try
    {
        $ptype = $layout->acts('post', $_POST['content_type']);
        $layout->merge($_POST['content_type']);
        $type = Content::save_entry_type($ptype);
        Content::save_field_group(
            array(
                'name' => $type['name'], 
                'content_entry_type_id' => $type['id']
            )
        );
        Module::h('content_new_type_process', Module::TARGET_ALL, &$layout, $type, $_POST);
        header('Location: /admin/module/Content/edit_type/'.$type['id'].'/');
        exit;
    }
    catch(Doctrine_Validator_Exception $e)
    {
        $records = $e->getInvalidRecords();
        foreach ($records as $record)
        {
            $errors = $record->getErrorStack()->toArray();
            foreach ($errors as $name => $messages)
            {
                foreach ($messages as $message)
                {
                    Admin::append('notices', $message);
                }
            }
        }
    }
}

//}}}

?>
