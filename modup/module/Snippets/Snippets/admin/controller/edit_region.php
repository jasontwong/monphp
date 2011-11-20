<?php

if (!user::perm('add content'))
{
    admin::set('title', 'Permission Denied');
    admin::set('header', 'Permission Denied');
    return;
}

admin::set('title', 'Edit Snippet Region');
admin::set('header', 'Edit Snippet Region');

$srt = Doctrine::getTable('SnippetRegion');
$sct = Doctrine::getTable('SnippetContent');
$region = $srt->findOneById(URI_PART_4);
$rpost = $region->toArray();

//{{{ form submission
if (isset($_POST['region']))
{
    $rpost = field::acts('post', $_POST['region']);
    try
    {
        if ($rpost['id'] == $region->id)
        {
            $region->merge($rpost);
        }
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
                    field::act('form', 'text', 'name', $region['name']),
                    field::act('form', 'hidden', 'id', $region['id'])
                ),
                'label' => array(
                    'text' => 'Name'
                )
            ),
            array(
                'fields' => array(
                    field::act('form', 'textarea', 'description', $region['description'])
                ),
                'label' => array(
                    'text' => 'Description'
                )
            ),
        )
    ),
    'region'
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
