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
        'type' => 'text',
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'nice_name',
        'type' => 'text',
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
        'field' => Field::layout('password_confirm'),
        'name' => 'password',
        'type' => 'password_confirm',
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
                            'options' => $perm,
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
foreach (User::find_groups() as $name => $group)
{
    $groups[$name] = $group['nice_name'];
}
$layout->add_layout(
    array(
        'field' => Field::layout(
            'checkbox',
            array(
                'data' => array(
                    'options' => $groups,
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
                    'text' => 'Save',
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
    if (strlen($upost['password']))
    {
        $uac = MonDB::selectCollection('user_account');
        $user = array();
        $user['name'] = '';
        $user['nice_name'] = '';
        $user['salt'] = random_string(5);
        $user['pass'] = sha1($user['salt'].$upost['password']);
        $user['email'] = '';
        $user['permission'] = array();
        $user['group'] = array();
        $user['group_ids'] = array();
        if (ake('groups', $upost))
        {
            $groups = iterator_to_array(MonDB::selectCollection('user_group')->find(array('name' => array('$in' => $upost['groups']))));
            $upost['group'] = array();
            $upost['group_ids'] = array();
            foreach ($groups as &$group)
            {
                $upost['group'][] = $group;
                $upost['group_ids'][] = $group['_id'];
            }
        }
        $user = array_join($user, $upost);
        $uac->insert($user);
        header('Location: /admin/module/User/edit_user/' . $user['name'] . '/');
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
    'fields' => $layout->get_layout('password'),
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
