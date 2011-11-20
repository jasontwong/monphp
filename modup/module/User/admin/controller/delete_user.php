<?php

if (!User::perm('edit users'))
{
    if (User::$info['id'] === URI_PART_4 && (!User::perm('edit self')))
    {
        Admin::set('title', 'Permission Denied');
        Admin::set('header', 'Permission Denied');
        return;
    }
}

$uat = Doctrine::getTable('UserAccount');
$user = $uat->findOneById(URI_PART_4);
if (!$user)
{
    header('Location: /admin/module/User/users/');
    exit;
}

Admin::set('title', 'Delete User &ldquo;'.$user->name.'&rdquo;');
Admin::set('header', 'Delete User &ldquo;'.$user->name.'&rdquo;');
// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('submit_confirm'),
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
        $ugt = Doctrine::getTable('UserGrouping');
        $groups = $ugt->findByUserId($user->id);
        $user->delete();
        $groups->delete();
        header('Location: /admin/module/User/users/');
        exit;
    }
    else
    {
        header('Location: /admin/module/User/edit_user/'.URI_PART_4.'/');
        exit;
    }
}

//}}}
//{{{ form build
$form = new FormBuilderRows;
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
