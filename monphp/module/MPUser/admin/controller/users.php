<?php

if (!MPUser::perm('view users'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

MPAdmin::set('title', 'MPUser Accounts');
MPAdmin::set('header', 'MPUser Accounts');

$users = iterator_to_array(MPDB::selectCollection('user_account')->find());
$href = '/admin/module/MPUser/edit_user';

?>

