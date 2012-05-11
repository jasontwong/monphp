<?php

if (!MPUser::perm('view groups'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

MPAdmin::set('title', 'MPUser Groups');
MPAdmin::set('header', 'MPUser Groups');

$query = MPUser::check_group(MPUser::GROUP_ADMIN)
    ? array()
    : array('name' => array('$ne' => MPUser::GROUP_ADMIN));
$groups = iterator_to_array(MPDB::selectCollection('user_group')->find($query));
/*
if (!MPUser::check_group(MPUser::GROUP_ADMIN))
{
    $groups->addWhere('name <> ?', MPUser::GROUP_ADMIN);
}
$groups = $groups->fetchArray();
*/

?>
