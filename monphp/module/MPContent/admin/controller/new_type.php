<?php

// {{{ field layout
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
        'field' => MPField::layout('textarea'),
        'name' => 'description',
        'type' => 'textarea'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('checkbox_boolean'),
        'name' => 'status',
        'type' => 'checkbox_boolean'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('checkbox_boolean'),
        'name' => 'flagging',
        'type' => 'checkbox_boolean'
    )
);

$hook_layouts = MPModule::h('mpcontent_new_type_layout');
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
        'field' => MPField::layout(
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
//{{{ form submission
if (isset($_POST['content_type']))
{
    try
    {
        $ptype = $layout->acts('post', $_POST['content_type']);
        $layout->merge($_POST['content_type']);
        $type = MPContent::save_type($ptype);
        MPModule::h('mpcontent_new_type_process', MPModule::TARGET_ALL, $layout, $type, $_POST);
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Successfully created');
        header('Location: /admin/module/MPContent/edit_type/'.$type['name'].'/');
        exit;
    }
    catch(Exception $e)
    {
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'There was an error creating this type.');
    }
}

//}}}
//{{{ form build
$form = new MPFormRows;
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
    )
);
$form->add_group($type_group, 'content_type');

$hook_forms = MPModule::h('mpcontent_new_type_form', MPModule::TARGET_ALL, $layout);
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
