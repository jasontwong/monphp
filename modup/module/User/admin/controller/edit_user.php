<?php

if (!User::perm('edit users'))
{
    if (User::i('id') === URI_PART_4 && (!User::perm('edit self')))
    {
        Admin::set('title', 'Permission Denied');
        Admin::set('header', 'Permission Denied');
        return;
    }
}

Admin::set('title', 'Edit Account');
Admin::set('header', 'Edit Account');

if (User::i('id') === URI_PART_4)
{
    $edit_self = TRUE;
    $ui = new UserInfo(User::i('name'));
    $user = $ui->user;
    $settings = User::setting();
}
else
{
    $edit_self = FALSE;
    $uat = Doctrine::getTable('UserAccount');
    $ua = $uat->find(URI_PART_4);
    $ui = new UserInfo($ua->name);
    $user = $ui->user;
    $ua->free();
}
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
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => $user['id']
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
        'field' => Field::layout('password_confirm_sha1'),
        'name' => 'pass',
        'type' => 'password_confirm_sha1'
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
foreach (User::find_groups() as $id => $group)
{
    if (is_numeric($id))
    {
        $groups[$id] = $group['name'];
    }
}
foreach ($user['group'] as &$ugroup)
{
    $ugroup = $ugroup['id'];
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
    unset($_POST['user']['pass']['password'], $_POST['user']['pass']['password_confirm']);
    $layout->merge($_POST['user']);
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
        if (strlen($upost['pass']))
        {
            $fields[] = 'pass';
        }
        foreach ($upost as $k => $v)
        {
            if (in_array($k, $fields))
            {
                if ($k === 'pass')
                {
                    if ($v)
                    {
                        $v = sha1($user['salt'].$v);
                        User::update($k, $v);
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
        $ugt = Doctrine::getTable('UserGrouping');
        $ua = $uat->findOneById(URI_PART_4);
        $groups = $ugt->findByUserId(URI_PART_4);
        if (strlen($upost['pass']))
        {
            $upost['pass'] = sha1($user['salt'].$upost['pass']);
        }
        else
        {
            unset($upost['pass']);
        }
        if (!User::has_perm('edit permissions'))
        {
            unset($upost['permission']);
        }
        $ua->merge($upost);
        $ua->save();
        if (User::has_perm('edit permissions'))
        {
            $groups->delete();
            foreach ($upost['group'] as $i => $gid)
            {
                $group = new UserGrouping;
                $group->user_id = $ua->id;
                $group->group_id = $gid;
                $group->save();
                $group->free();
                unset($group);
            }
        }
        $ua->free();
        unset($ua);
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
    'fields' => $layout->get_layout('pass'),
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
