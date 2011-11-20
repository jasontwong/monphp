<?php

if (!User::perm('edit gallery'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Manage Albums');
Admin::set('header', 'Manage Albums');

$albums = array();
$gat = Doctrine::getTable('GalleryAlbum');
$ga = $gat->findAll();
if ($ga !== FALSE)
{
    $albums = $ga->toArray();
    $ga->free();
}
unset($ga);

?>
