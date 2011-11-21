<?php

if (!User::perm('edit users'))
{
    if (User::i('name') === URI_PART_4 && (!User::perm('edit self')))
    {
        Admin::set('title', 'Permission Denied');
        Admin::set('header', 'Permission Denied');
        return;
    }
}

Admin::set('title', 'Edit Account');
Admin::set('header', 'Edit Account');

if (!defined('URI_PART_4'))
{
    Admin::notify(Admin::TYPE_ERROR, "Invalid user");
    header('Location: /admin/module/User/users/');
    exit;
}

$uac = MonDB::selectCollection('user_account');
$ua = $uac->findOne(array('name' => URI_PART_4));

if (is_null($ua))
{
    Admin::notify(Admin::TYPE_ERROR, "That user does not exist");
    header('Location: /admin/module/User/users/');
    exit;
}

$edit_self = User::i('name') === URI_PART_4;
$ui = new UserInfo($ua['name']);
$user = $ui->user;
$settings = User::setting();

//{{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'value' => array(
            'data' => $user['name']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'nice_name',
        'type' => 'text',
        'value' => array(
            'data' => $user['nice_name']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'email',
        'type' => 'text',
        'value' => array(
            'data' => $user['email']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('password_confirm'),
        'name' => 'password',
        'type' => 'password_confirm'
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
                'value' => array(
                    'data' => $user['permission']
                )
            )
        );
    }
}
$groups = array();
foreach (User::find_groups() as $name => $group)
{
    $groups[$name] = $group['name'];
}
foreach ($user['group'] as &$ugroup)
{
    $ugroup = $ugroup['name'];
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
        'name' => 'group',
        'type' => 'checkbox',
        'value' => array(
            'data' => $user['group']
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
if ($edit_self && is_array(deka('',$settings,'admin','quicklinks')))
{
    $layout->add_layout(
        array(
            'field' => Field::layout(
                'checkbox',
                array(
                    'data' => array(
                        'options' => &$settings['admin']['quicklinks']
                    )
                )
            ),
            'name' => 'quicklinks',
            'type' => 'checkbox'
        )
    );
}
//}}}
//{{{ form submitted
if (isset($_POST['form']))
{
    $upost = $layout->acts('post', $_POST['user']);
    unset($_POST['user']['password']['password'], $_POST['user']['password']['password_confirm']);
    $upost['permission'] = array();
    foreach ($perm_mods as $mod => $groups)
    {
        foreach ($groups as $group)
        {
            if (ake($mod.'_'.$group, $upost))
            {
                $upost['permission'] = array_merge($upost['permission'], $upost[$mod.'_'.$group]);
            }
        }
    }
    //{{{ current user
    if ($edit_self)
    {
        $layout->merge($_POST['user']);
        if (eka($_POST, 'settings', 'admin'))
        {
            $spost['admin'] = $layout->acts('post', $_POST['settings']['admin']);
            foreach ($spost as $mod => $setting)
            {
                $layout->merge($_POST['settings'][$mod]);
                if ($mod === 'admin')
                {
                    if (ake('quicklinks', $setting))
                    {
                        foreach ($setting['quicklinks'] as $link)
                        {
                            unset($settings[$mod]['quicklinks'][$link]);
                        }
                        User::update('setting', 'admin', 'quicklinks', $settings[$mod]['quicklinks']);
                    }
                }
            }
        }
        $groups = User::find_groups();
        $user_groups = array();
        if (User::has_perm('edit permissions'))
        {
            foreach ($upost['group'] as $g)
            {
                if (isset($groups[$g]))
                {
                    $user_groups[$g] = $groups[$g];
                }
            }
            User::update('group', $user_groups);
            User::update('permission', $upost['permission']);
        }
        $fields = array('email');
        if (strlen($upost['password']))
        {
            $fields[] = 'password';
        }
        foreach ($upost as $k => $v)
        {
            if (in_array($k, $fields))
            {
                if ($k === 'password')
                {
                    if ($v)
                    {
                        $v = sha1($user['salt'].$v);
                        User::update('pass', $v);
                    }
                }
                else
                {
                    User::update($k, $v);
                }
            }
        }
    }
    
    //}}}
    //{{{ other user
    else
    {
        if (strlen($upost['password']))
        {
            $upost['pass'] = sha1($user['salt'].$upost['password']);
        }
        else
        {
            unset($upost['pass']);
        }
        if (!User::has_perm('edit permissions'))
        {
            unset($upost['permission']);
        }
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
        unset($user['_id']);
        $success = $uac->update(array('_id' => $ua['_id']), array('$set' => $user), array('safe' => TRUE));
        if (deka(FALSE, $success, 'ok'))
        {
            Admin::notify(Admin::TYPE_SUCCESS, 'User successfully updated');
            $layout->merge($_POST['user']);
        }
        else
        {
            Admin::notify(Admin::TYPE_ERROR, 'There was a problem updating the user');
        }
    }

    //}}}
}

//}}}
//{{{ make form
$form = new FormBuilderRows;
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH,
    'id' => 'user-edit'
);

$rows[] = array(
    'label' => array(
        'text' => 'Login'
    ),
    'fields' => $layout->get_layout('name'),
);
$rows[] = array(
    'label' => array(
        'text' => 'Display Name'
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

if ($edit_self && is_array(deka('', $settings, 'admin', 'quicklinks')) && !empty($settings['admin']['quicklinks']))
{
    $ql_layout = $layout->get_layout('quicklinks');
    $ql_layout['field']['data']['options'] = $settings['admin']['quicklinks'];
    
    $form->add_group(
        array(
            'rows' => array(
                array(
                    'label' => array(
                        'text' => 'Quicklinks'
                    ),
                    'fields' => $ql_layout,
                    'description' => array(
                        'text' => $settings['admin']['quicklinks']
                            ? 'Check off items you wish to delete'
                            : 'None'
                    ),
                ),
            )
        ),
        'settings[admin]'
    );
}
if (isset($perm_mods) && User::has_perm('edit permissions'))
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
                'user'
            );
        }
    }
}

if (User::has_perm('edit permissions'))
{
    $form->add_group(
        array(
            'rows' => array(
                array(
                    'label' => array(
                        'text' => 'Group'
                    ),
                    'fields' => $layout->get_layout('group'),
                )
            )
        ),
        'user'
    );
}

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
