<?php

if (!User::perm('create user'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Create New User');
Admin::set('header', 'Create New User');

// {{{ layout
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
        'name' => 'nice_name',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'email',
        'type' => 'text',
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('password_confirm_sha1'),
        'name' => 'pass',
        'type' => 'password_confirm_sha1'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'hidden',
        'type' => 'hidden',
        'hidden' => array('data')
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
$groups = array();
foreach (User::find_groups() as $id => $group)
{
    if (is_numeric($id))
    {
        $groups[$id] = $group['name'];
    }
}
$layout->add_layout(
    array(
        'field' => Field::layout(
            'checkbox',
            array(
                'data' => array(
                    'options' => $groups
                )
            )
        ),
        'name' => 'groups',
        'type' => 'checkbox',
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
// }}}
//{{{ form submitted
if (isset($_POST['form']))
{
    $upost = $layout->acts('post', $_POST['user']);
    $layout->merge($_POST['user']);
    $upost['permission'] = array();
    foreach ($perm_mods as $mod => $groups)
    {
        foreach ($groups as $group)
        {
            $upost['permission'] = array_merge($upost['permission'], $upost[$mod.'_'.$group]);
        }
    }
    if (strlen($upost['pass']))
    {
        $user = new UserAccount;
        $user->merge($upost);
        $user->save();
        $huser = $user->toArray();
        //$huser['groups'] = $upost['groups'];
        if (ake('groups', $upost))
        {
            foreach ($upost['groups'] as $gid)
            {
                $group = new UserGrouping;
                $group->user_id = $user->id;
                //$group->user_id = 1;
                $group->group_id = (int)$gid;
                $group->save();
            }
        }
        //module::h('workflow_task', 'Workflow', 'User', 'user create', $huser);
        header('Location: /admin/module/User/edit_user/'.$user->id.'/');
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
        'text' => 'Username'
    ),
    'fields' => $layout->get_layout('name'),
);
$rows[] = array(
    'label' => array(
        'text' => 'Name'
    ),
    'fields' => $layout->get_layout('nice_name'),
);
$rows[] = array(
    'label' => array(
        'text' => 'Email'
    ),
    'fields' => $layout->get_layout('email'),
);
$rows[] = array(
    'label' => array(
        'text' => 'New Password'
    ),
    'fields' => $layout->get_layout('pass'),
);
$form->add_group(
    array(
        'rows' => $rows
    ),
    'user'
);

if (isset($perm_mods))
{
    $form->add_group(
        array(
            'rows' => array(
                array(
                    'label' => array(
                        'text' => 'Permissions'
                    ),
                    'fields' => $layout->get_layout('hidden')
                )
            )
        )
    );
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
                'user'
            );
        }
    }
}

$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Group'
                ),
                'fields' => $layout->get_layout('groups'),
            )
        )
    ),
    'user'
);

$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit'),
            ),
        )
    ),
    'form'
);
$fh = $form->build();

//}}}

?>
