<?php

if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

MPAdmin::set('title', 'Select Which Entry Type to Edit');
MPAdmin::set('header', 'Select Which Entry Type to Edit');

$entry_types = MPContent::get_types(
    array(), 
    array('name', 'nice_name', 'description')
);
