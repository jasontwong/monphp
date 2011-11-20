<?php

if (!User::perm('edit content type'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

$entry_type = Content::get_entry_type_by_id(URI_PART_4);
if (!$entry_type)
{
    header('Location: /admin/');
    exit;
}

Admin::set('title', 'Edit Content Type &ldquo;'.htmlentities($entry_type['name'], ENT_QUOTES).'&rdquo;');
Admin::set('header', 'Edit Content Type &ldquo;'.htmlentities($entry_type['name'], ENT_QUOTES).'&rdquo;');

$other_links = Module::h('content_edit_type_other_links', Module::TARGET_ALL, URI_PART_4);

//{{{ layout
$stacks = array(
    ContentEntryType::STACK_TOP => 'top',
    ContentEntryType::STACK_BOTTOM => 'bottom'
);
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'value' => array(
            'data' => $entry_type['name']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('textarea'),
        'name' => 'description',
        'type' => 'textarea',
        'value' => array(
            'data' => $entry_type['description']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'ordering',
        'type' => 'checkbox_boolean',
        'value' => array(
            'data' => $entry_type['ordering']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'radio',
            array(
                'data' => array(
                    'options' => $stacks
                )
            )
        ),
        'name' => 'stack',
        'type' => 'radio',
        'value' => array(
            'data' => $entry_type['stack']
        )
    )
);

$hook_layouts = Module::h('content_edit_type_layout', Module::TARGET_ALL, $layout, $entry_type);
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
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

//}}}
//{{{ form submission
if (isset($_POST['form']))
{
    $data = $layout->acts('post', $_POST['content_type']);
    $layout->merge($_POST['content_type']);
    $entry_type = array_merge($entry_type, $data);
    Content::save_entry_type($entry_type);
    Module::h('content_edit_type_process', Module::TARGET_ALL, $layout, $entry_type, $_POST);
}

//}}}
//{{{ type form build
$tform = new FormBuilderRows;
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
            array(
                'fields' => $layout->get_layout('ordering'),
                'label' => array(
                    'text' => 'Manually ordered'
                )
            ),
            array(
                'fields' => $layout->get_layout('stack'),
                'label' => array(
                    'text' => 'If entries can be manually ordered, where should new ones go?'
                )
            ),
        )
    ),
    'content_type'
);

$hook_forms = Module::h('content_edit_type_form', Module::TARGET_ALL, &$layout, $entry_type);
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

//}}}

?>
