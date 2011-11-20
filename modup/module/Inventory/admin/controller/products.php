<?php

Admin::set('title', 'Edit a product inventory');
Admin::set('header', 'Edit a product inventory');

$products = Inventory::get_products();

?>
