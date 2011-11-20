<?php

if (!User::perm('add content'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Edit Snippet Region');
Admin::set('header', 'Edit Snippet Region');

$snippet = Doctrine_Query::create()
           ->from('SnippetRegion')
           ->where('id = ?', URI_PART_4)
           ->orderBy('name ASC')
           ->fetchOne(array(), Doctrine::HYDRATE_ARRAY);

//{{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'value' => array(
            'data' => $snippet['name']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'description',
        'type' => 'text',
        'value' => array(
            'data' => $snippet['description']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'title',
        'type' => 'text',
        'value' => array(
            'data' => $snippet['title']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('richtext'),
        'name' => 'content',
        'type' => 'richtext',
        'value' => array(
            'data' => $snippet['content']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('file'),
        'name' => 'attachment',
        'type' => 'file',
        'value' => array(
            'data' => $snippet['attachment']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'active',
        'type' => 'checkbox_boolean',
        'value' => array(
            'data' => $snippet['active']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => $snippet['id']
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
if (isset($_POST['data']))
{
    $layout->merge($_POST['data']);
    $snippet_save = $layout->acts('save', $_POST['data']);
    $snippet = $layout->acts('post', $_POST['data']);
    $snippet['attachment'] = deka('', $snippet_save, 'attachment', 'data', 0, 'cdata');
    try
    {
        $update = Doctrine_Query::create()
                    ->update('SnippetRegion')
                    ->set('name', '?', $snippet['name'])
                    ->set('description', '?', $snippet['description'])
                    ->set('title', '?', $snippet['title'])
                    ->set('content', '?', $snippet['content'])
                    ->set('attachment', '?', $snippet['attachment'])
                    ->set('active', '?', $snippet['active'])
                    ->where('id = ?', $snippet['id'])
                    ->execute();
        header('Location: /admin/module/Snippets/edit/'.$snippet['id'].'/');
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
    'enctype' => 'multipart/form-data',
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
                'fields' => $layout->get_layout('id')
            ),
            array(
                'fields' => $layout->get_layout('active'),
                'label' => array(
                    'text' => 'This region is active'
                )
            )
        )
    ),
    'data'
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
