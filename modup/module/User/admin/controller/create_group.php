<?php

if (!User::perm('create group'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Create New Group');
Admin::set('header', 'Create New Group');
// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'nice_name',
        'type' => 'text'
    )
);
foreach (User::permissions() as $mod => $perms)
{
    foreach ($perms as $group => $perm)
    {
        if (!is_array($perm))
        {
            continue;
        }
        $perm_mods[$mod][] = $group;
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
                'name' => $mod.'_'.$group,
                'type' => 'checkbox',
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
    foreach ($perm_mods as $mod => &$groups)
    {
        foreach ($groups as &$group)
        {
            $gpost['permission'] = array_merge($gpost['permission'], $gpost[$mod.'_'.$group]);
        }
    }
    if (strlen($gpost['nice_name']))
    {
        $ugc = MonDB::selectCollection('user_group');
        $gpost['name'] = slugify($gpost['nice_name']);
        $ugc->save($gpost);
        header('Location: /admin/module/User/edit_group/' . $gpost['name'] . '/');
        exit;
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
    'fields' => $layout->get_layout('nice_name'),
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
        foreach ($perm_groups as $group)
        {
            $form->add_group(
                array(
                    'attr' => array(
                        'class' => 'clear tabbed tab-'.$mod
                    ),
                    'label' => array(
                        'text' => nl2br($group)
                    ),
                    'rows' => array(
                        array(
                            'fields' => $layout->get_layout($mod.'_'.$group)
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
$gfh = $form->build();

//}}}

?>
