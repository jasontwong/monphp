<?php

if (!User::perm('view groups'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Edit Group');
Admin::set('header', 'Edit Group');

$gt = Doctrine::getTable('UserGroup');
$group = $gt->find(URI_PART_4);
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
    foreach ($perms as $perm_group => $perm)
    {
        if (!is_array($perm))
        {
            continue;
        }
        $perm_mods[$mod][] = $perm_group;
        $layout->add_layout(
            array(
                'field' => Field::layout(
                    'checkbox',
                    array(
                        'data' => array(
                            'options' => $perm
                        )
                    )
                ),
                'name' => $mod.'_'.$perm_group,
                'type' => 'checkbox',
                'value' => array(
                    'data' => $group->permission
                )
            )
        );
    }
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
    foreach ($perm_mods as $mod => $groups)
    {
        foreach ($groups as $perm_group)
        {
            $gpost['permission'] = array_merge($gpost['permission'], $gpost[$mod.'_'.$perm_group]);
        }
    }
    $group->merge($gpost);
    if ($group->isValid())
    {
        $group->save();
    }
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
$form->add_group(
    array(
        'rows' => $rows
    ),
    'group'
);
if (isset($perm_mods))
{
    foreach ($perm_mods as $mod => $perm_groups)
    {
        foreach ($perm_groups as $perm_group)
        {
            $form->add_group(
                array(
                    'attr' => array(
                        'class' => 'clear tabbed tab-'.$mod
                    ),
                    'label' => array(
                        'text' => nl2br($perm_group)
                    ),
                    'rows' => array(
                        array(
                            'fields' => $layout->get_layout($mod.'_'.$perm_group)
                        )
                    )
                ),
                'group'
            );
        }
    }
}
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
