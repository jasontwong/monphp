<?php

if (!User::perm('edit content type'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Select Which Entry Type to Edit');
Admin::set('header', 'Select Which Entry Type to Edit');

$entry_types = Content::get_entry_types(
    array(), 
    array('select' => array('ety.id', 'ety.name', 'ety.description'))
);


?>
