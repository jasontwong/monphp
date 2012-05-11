<?php

if (!MPUser::perm('edit users'))
{
    if (MPUser::$info['id'] === URI_PART_4 && (!MPUser::perm('edit self')))
    {
        MPAdmin::set('title', 'Permission Denied');
        MPAdmin::set('header', 'Permission Denied');
        return;
    }
}

$uat = Doctrine::getTable('MPUserAccount');
$user = $uat->findOneById(URI_PART_4);
if (!$user)
{
    header('Location: /admin/module/MPUser/users/');
    exit;
}

MPAdmin::set('title', 'Delete MPUser &ldquo;'.$user->name.'&rdquo;');
MPAdmin::set('header', 'Delete MPUser &ldquo;'.$user->name.'&rdquo;');
// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_confirm'),
        'name' => 'do',
        'type' => 'submit_confirm'
    )
);

// }}}
//{{{ form submit
if (isset($_POST['do']))
{
    $apost = $layout->acts('post', $_POST);
    var_dump($apost);
    if ($apost['do'])
    {
        $ugt = Doctrine::getTable('MPUserGrouping');
        $groups = $ugt->findByMPUserId($user->id);
        $user->delete();
        $groups->delete();
        header('Location: /admin/module/MPUser/users/');
        exit;
    }
    else
    {
        header('Location: /admin/module/MPUser/edit_user/'.URI_PART_4.'/');
        exit;
    }
}

//}}}
//{{{ form build
$form = new MPFormRows;
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('do')
            )
        )
    )
);
$df = $form->build();

//}}}

?>
