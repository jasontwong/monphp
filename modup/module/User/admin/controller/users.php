<?php

if (!User::perm('view users'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'User Accounts');
Admin::set('header', 'User Accounts');

$users = iterator_to_array(MonDB::selectCollection('user_account')->find());
$href = '/admin/module/User/edit_user';

?>

