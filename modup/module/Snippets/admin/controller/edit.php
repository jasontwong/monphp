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
$layout_attachment = array(
    'field' => Field::layout('file'),
    'name' => 'attachment',
    'type' => 'file'
);
$layout_attachment['html_after']['data'] = $snippet['attachment']
    ? '<p>Image preview:<br><img src="/file/upload/'.urlencode($snippet['attachment']).'" alt=""></p>'
    : '<p>No image uploaded</p>';
$layout->add_layout($layout_attachment);
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
    $snippet = $layout->acts('post', $_POST['data']);
    if ($snippet['attachment']['delete'] === FALSE && !deka(FALSE, $_FILES, 'data', 'name', 'attachment', 'data'))
    {
        $keep_attachment = TRUE;
    }
    else
    {
        $keep_attachment = FALSE;
        $has_new_attachment = deka(1, $_FILES, 'data', 'error', 'attachment', 'data') === 0;
        if ($has_new_attachment)
        {
            $aname = $_FILES['data']['name']['attachment']['data'];
            $atype = $_FILES['data']['type']['attachment']['data'];
            $atemp = $_FILES['data']['tmp_name']['attachment']['data'];
            $aerror = $_FILES['data']['error']['attachment']['data'];
            $asize = $_FILES['data']['size']['attachment']['data'];
            $adest = available_filename(DIR_FILE.'/upload/'.$aname);
            move_uploaded_file($atemp, $adest);
        }
    }
    try
    {
        $update = Doctrine_Query::create()
                    ->update('SnippetRegion')
                    ->set('name', '?', $snippet['name'])
                    ->set('description', '?', $snippet['description'])
                    ->set('title', '?', $snippet['title'])
                    ->set('content', '?', $snippet['content'])
                    ->set('active', '?', $snippet['active'])
                    ->where('id = ?', $snippet['id']);
        if (!$keep_attachment)
        {
            $update->set('attachment', '?', $has_new_attachment ? basename($adest) : '');
        }
        $update->execute();
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
