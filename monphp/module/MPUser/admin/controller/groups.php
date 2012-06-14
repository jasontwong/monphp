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
$groups = MPDB::selectCollection('user_group')->find($query);
