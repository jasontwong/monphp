<?php

if (!user::perm('add content'))
{
    admin::set('title', 'Permission Denied');
    admin::set('header', 'Permission Denied');
    return;
}

admin::set('title', 'Snippet Regions');
admin::set('header', 'Snippet Regions');

$srt = Doctrine::getTable('SnippetRegion');
$regions = $srt->findAll()->toArray();

?>
