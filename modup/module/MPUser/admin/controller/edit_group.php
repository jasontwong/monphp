<?php

if (!MPUser::perm('view groups'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

MPAdmin::set('title', 'Edit Group');
MPAdmin::set('header', 'Edit Group');

if (!defined('URI_PART_4'))
{
    MPAdmin::notify(MPAdmin::TYPE_ERROR, "Invalid group");
    header('Location: /admin/module/MPUser/groups/');
    exit;
}

$ugc = MPDB::selectCollection('user_group');
$group = $ugc->findOne(array('name' => URI_PART_4));
if (is_null($group))
{
    MPAdmin::notify(MPAdmin::TYPE_ERROR, "That group does not exist");
    header('Location: /admin/module/MPUser/groups/');
    exit;
}
// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'nice_name',
        'type' => 'text',
        'value' => array(
            'data' => $group['nice_name'],
        )
    )
);
foreach (MPUser::permissions() as $mod => $perms)
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
                'field' => MPField::layout(
                    'checkbox',
                    array(
                        'data' => array(
                            'options' => $perm,
                        )
                    )
                ),
                'name' => $mod.'_'.$perm_group,
                'type' => 'checkbox',
                'value' => array(
                    'data' => $group['permission'],
                )
            )
        );
    }
}
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

// }}}
//{{{ form submitted
if (isset($_POST['form']))
{
    $gpost = $layout->acts('post', $_POST['group']);
    $new_data['permission'] = array();
    foreach ($perm_mods as $mod => $groups)
    {
        foreach ($groups as $perm_group)
        {
            $new_data['permission'] = array_merge($new_data['permission'], $gpost[$mod.'_'.$perm_group]);
        }
    }
    $success = $ugc->update(array('_id' => $group['_id']), array('$set' => $new_data), array('safe' => TRUE));
    if (deka(FALSE, $success, 'ok'))
    {
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Group successfully updated');
        MPAdmin::log(MPAdmin::TYPE_NOTICE, 'Group ' . $group['name'] . ' updated');
        $layout->merge($_POST['group']);
    }
    else
    {
        MPAdmin::notify(MPAdmin::TYPE_ERROR, 'There was a problem updating the group');
    }
}

//}}}
//{{{ make form
$form = new MPFormBuilderRows;
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
