<?php

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
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'status',
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

if (isset($_POST['group']))
{
    $group = $layout->acts('post', $_POST['group']);
    $layout->merge($_POST['group']);
    if ($group['status'])
    {
        $group['status'] = Profile::UNLISTED;
    }
    $grp = new ProfileGroup();
    $grp->merge($group);
    if ($grp->isValid())
    {
        $grp->save();
        header('Location: /admin/module/Profile/manage/');
        exit;
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

echo $form->build();

?>
