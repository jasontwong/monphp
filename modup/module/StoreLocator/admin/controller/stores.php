<?php

Admin::set('title', 'Stores');
Admin::set('header', 'Stores');

$slnt = Doctrine::getTable('StoreLocatorName');
$stores = $slnt->find('get.all', array(), Doctrine::HYDRATE_ARRAY);

?>
