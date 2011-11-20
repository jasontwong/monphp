<?php

if (!User::perm('add content'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Create New Snippet Region');
Admin::set('header', 'Create New Snippet Region');
$snippet = array();

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
        'name' => 'description',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'title',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('richtext'),
        'name' => 'content',
        'type' => 'richtext'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('file'),
        'name' => 'attachment',
        'type' => 'file'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'active',
        'type' => 'checkbox_boolean',
        'value' => array(
            'data' => 1
        )
    )
);
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
//}}}
//{{{ form submission
if (isset($_POST['snippet']))
{
    $layout->merge($_POST['snippet']);
    $snippet = $layout->acts('post', $_POST['snippet']);
    try
    {
        $entry = new SnippetRegion;
        $entry->merge($snippet);
        $entry->save();
        header('Location: /admin/module/Snippets/edit/'.$entry->id.'/');
        exit;
    }
    catch (Exception $e)
    {
    }
}

//}}}
//{{{ form build
$form = new FormBuilderRows;
$form->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('name'),
                'label' => array(
                    'text' => 'Name (to distinguish entries in back end)'
                )
            ),
            array(
                'fields' => $layout->get_layout('description'),
                'label' => array(
                    'text' => 'Description'
                )
            ),
            array(
                'fields' => $layout->get_layout('title'),
                'label' => array(
                    'text' => 'Title (used in front end)'
                )
            ),
            array(
                'fields' => $layout->get_layout('content'),
                'label' => array(
                    'text' => 'Content'
                )
            ),
            array(
                'fields' => $layout->get_layout('attachment'),
                'label' => array(
                    'text' => 'File Attachment'
                )
            ),
            array(
                'fields' => $layout->get_layout('active'),
                'label' => array(
                    'text' => 'This region is active'
                )
            )
        )
    ),
    'snippet'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit'),
            )
        )
    )
);


$efh = $form->build();

//}}}

?>
