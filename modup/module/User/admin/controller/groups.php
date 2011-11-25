<?php

if (!User::perm('view groups'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'User Groups');
Admin::set('header', 'User Groups');

$query = User::check_group(User::GROUP_ADMIN)
    ? array()
    : array('name' => array('$ne' => User::GROUP_ADMIN));
$groups = iterator_to_array(MonDB::selectCollection('user_group')->find($query));
/*
if (!User::check_group(User::GROUP_ADMIN))
{
    $groups->addWhere('name <> ?', User::GROUP_ADMIN);
}
$groups = $groups->fetchArray();
*/

?>
