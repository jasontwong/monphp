<?php

if (!User::perm('view users'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'User Accounts');
Admin::set('header', 'User Accounts');

$users = Doctrine_Query::create()
         ->select('a.id, a.name, a.nice_name, a.joined, a.email, g.group_id')
         ->from('UserAccount a');
if (!User::perm('admin'))
{
    $users->leftJoin('UserGrouping g')->addWhere('g.group_id <> ?', GROUP_ADMIN);
}
$users = $users->fetchArray();
$href = '/admin/module/User/edit_user';

?>

