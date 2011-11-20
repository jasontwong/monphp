<?php

if (!user::perm('add content'))
{
    admin::set('title', 'Permission Denied');
    admin::set('header', 'Permission Denied');
    return;
}

admin::set('title', 'Create New Snippet Region');
admin::set('header', 'Create New Snippet Region');
$snippet = array();

//{{{ form submission
if (isset($_POST['snippet']))
{
    $snippet = field::acts('post', $_POST['snippet']);
    try
    {
        $entry = new SnippetRegion;
        $entry->merge($snippet);
        $entry->save();
        header('Location: /admin/module/snippets/edit_region/'.$entry->id.'/');
        exit;
    }
    catch (Exception $e)
    {
    }
}

//}}}
//{{{ form build
$eform = new form(new form_builder_rows);
$eform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$eform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => array(
                    field::act('form', 'text', 'name', deka('', $snippet, 'name'))
                ),
                'label' => array(
                    'text' => 'Name'
                )
            ),
            array(
                'fields' => array(
                    field::act('form', 'textarea', 'description', deka('', $snippet, 'description'))
                ),
                'label' => array(
                    'text' => 'Description'
                )
            ),
        )
    ),
    'snippet'
);
$eform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => field::act('form', 'submit_reset', 'do', 'add_snippet')
            )
        )
    ),
    'form'
);

$efh = $eform->build();

//}}}

?>
