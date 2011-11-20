<?php

if (!User::has_perm('view ecommerce orders'))
{
    throw new Exception('You do not have access to this page');
}

Admin::set('title', 'View Order');
Admin::set('header', 'View Order');

if (!defined('URI_PART_4') || !is_numeric(URI_PART_4))
{
    throw new Exception('No order id');
}

$eot = Doctrine::getTable('EcommerceOrder');
$eo = $eot->find(URI_PART_4);

?>
