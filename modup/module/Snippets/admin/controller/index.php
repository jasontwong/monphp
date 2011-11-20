<?php

Admin::set('title', 'Snippet Regions');
Admin::set('header', 'Snippet Regions');

$snippets = Doctrine_Query::create()
            ->select('id, name, description, active')
            ->from('SnippetRegion')
            ->orderBy('name ASC')
            ->execute()
            ->toArray();

?>
