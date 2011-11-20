<?php

if (!defined('URI_PART_4') || !is_numeric(URI_PART_4))
{
    header('Location: /admin/module/Profile/manage/');
    exit;
}

$pgt = Doctrine::getTable('ProfileGroup');
$pg = $pgt->find(URI_PART_4);

$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'value' => array(
            'data' => $pg->name
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'checkbox_boolean'
        ),
        'name' => 'status',
        'type' => 'checkbox_boolean',
        'value' => $pg->status == Profile::UNLISTED
            ? array('data' => $pg->status)
            : array()
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

if (isset($_POST['group']))
{
    $group = $layout->acts('post', $_POST['group']);
    $layout->merge($_POST['group']);
    if ($group['status'])
    {
        $group['status'] = Profile::UNLISTED;
    }
    $pg->merge($group);
    if ($pg->isModified())
    {
        $pg->save();
    }
}

$form = new FormBuilderRows();
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Group Name'
                ),
                'fields' => $layout->get_layout('name')
            ),
            array(
                'label' => array(
                    'text' => 'Make Private Group'
                ),
                'fields' => $layout->get_layout('status')
            ),
            array(
                'fields' => $layout->get_layout('submit')
            )
        )
    ),
    'group'
);

?>
