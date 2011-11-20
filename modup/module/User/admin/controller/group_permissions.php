<?php

if (!User::perm('view groups'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Edit Group Permissions');
Admin::set('header', 'Edit Group Permissions');

$gt = Doctrine::getTable('UserGroup');
$group = $gt->findOneById(URI_PART_4);
if ($group === FALSE)
{
    header('Location: /admin/');
    exit;
}
// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'value' => array(
            'data' => $group->name
        )
    )
);
foreach (User::permissions() as $mod => $perms)
{
    $perm_mods[] = $mod;
    $layout->add_layout(
        array(
            'field' => Field::layout(
                'checkbox',
                array(
                    'data' => array(
                        'options' => $perms
                    )
                )
            ),
            'name' => $mod,
            'type' => 'checkbox',
            'value' => array(
                'data' => $group->permission
            )
        )
    );
}
$layout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

// }}}
//{{{ form submitted
if (isset($_POST['form']))
{
    $gpost = $layout->acts('post', $_POST['group']);
    $layout->merge($_POST['group']);
    $gpost['permission'] = array();
    foreach ($perm_mods as $mod)
    {
        $gpost['permission'] = array_merge($gpost['permission'], $gpost[$mod]);
    }
    $group->merge($gpost);
    $group->save();
}

//}}}
//{{{ make form
$form = new FormBuilderRows;
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH
);
$rows[] = array(
    'label' => array(
        'text' => 'Name'
    ),
    'fields' => $layout->get_layout('name'),
);
if (isset($perm_mods))
{
    foreach ($perm_mods as $mod)
    {
        $rows[] = array(
            'label' => array(
                'text' => $mod.' Permissions'
            ),
            'fields' => $layout->get_layout($mod),
        );
    }
}
$form->add_group(
    array(
        'rows' => $rows
    ),
    'group'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            ),
        )
    ),
    'form'
);
$fh = $form->build();

//}}}

?>
