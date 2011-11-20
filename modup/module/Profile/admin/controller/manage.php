<?php

$groups = Doctrine_Query::create()
    ->from('ProfileGroup g')
    ->leftJoin('g.Fields f')
    ->orderBy('g.weight ASC, f.weight ASC, g.name ASC, f.name ASC')
    ->fetchArray();

?>
