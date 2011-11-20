<?php

if (!User::perm('view groups'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'User Groups');
Admin::set('header', 'User Groups');

$groups = Doctrine_Query::create()
          ->select('id, name')
          ->from('UserGroup');
/*
if (!User::check_group(User::GROUP_ADMIN))
{
    $groups->addWhere('name <> ?', User::GROUP_ADMIN);
}
*/
$groups = $groups->fetchArray();

?>
