<?php

if (!User::has_perm('view ecommerce orders'))
{
    throw new Exception('You do not have access to this page');
}

Admin::set('title', 'Orders');
Admin::set('header', 'Orders');

$page = defined('URI_PART_4') ? URI_PART_4 : 1;

$user_filter = User::i('ecommerce_order_filter');

$default_filter = array(
    'rows' => '25',
    'start_date' => date('Y-m-d', strtotime('-1 year')),
    'end_date' => date('Y-m-d'),
    'statuses' => array(),
    'state' => '',
    'sort' => array(
        'type' => 'modified_date',
        'order' => 'DESC',
    ),
);

$filter = is_null($user_filter)
    ? $default_filter
    : array_merge($default_filter, $user_filter);

User::update('ecommerce_order_filter', $filter);
User::update('ecommerce_order_filter', $filter);

$orders_filter = $filter;
$orders_filter['start_date'] = strtotime($filter['start_date']);
$orders_filter['end_date'] = strtotime($filter['end_date']);

$columns = array(
    'order_name' => 'Order',
    'customer_name' => 'Name',
    'customer_email' => 'Email',
    'ship_to' => 'State',
    'date' => 'Date',
    'subtotal' => 'Subtotal',
    'tax' => 'Tax',
    'shipping' => 'Shipping',
    'weight' => 'Weight',
    'total' => 'Total',
    'status' => 'Status',
    'admin_comments' => 'Admin Comments',
    'edit' => '',
);

// turn into helper function?
$orig_orders = EcommerceAPI::get_orders_paginated($orders_filter, $page, $filter['rows']);
$num_pages = (int)floor($orig_orders['total_items'] / $filter['rows']);
if ($orig_orders['total_items'] % $filter['rows'] > 0)
{
    $num_pages++;
}
$states = Ecommerce::get_us_states();
$provinces = Ecommerce::get_ca_provinces();
$orders = array();
foreach ($orig_orders['items'] as $order)
{
    $tmp['order_name'] = '<a href="/admin/module/Ecommerce/view_order/'.$order->id.'/">'.$order->order_name.'</a>';
    $tmp['edit'] = '<a href="/admin/module/Ecommerce/edit_order/'.$order->id.'/">[edit]</a>';
    $tmp['customer_name'] = $order->BillingAddress->name;
    $tmp['customer_email'] = $order->customer_email;
    $tmp['ship_to'] = $order->ShippingAddress->state;
    if ($order->ShippingAddress->country === 'US')
    {
        $tmp['ship_to'] = $states[$order->ShippingAddress->state];
    }
    if ($order->ShippingAddress->country === 'CA')
    {
        $tmp['ship_to'] = $provinces[$order->ShippingAddress->state];
    }
    $tmp['date'] = date('Y-m-d', $order['modified_date']);
    $tmp['subtotal'] = '$'.$order->subtotal;
    $tmp['tax'] = '$'.$order->tax;
    $tmp['shipping'] = '$'.$order->shipping;
    $tmp['weight'] = $order->weight;
    $tmp['total'] = '$'.$order->total;
    $tmp['admin_comments'] = $order->admin_comments;
    $tmp['status'] = $order->Status->name;
    $orders[] = $tmp;
}
$orig_orders['items']->free();

// dropdowns
$types = array(
    'order_name' => 'Order',
    'customer_email' => 'Email',
    'state' => 'State',
    'modified_date' => 'Date',
    'subtotal' => 'Subtotal',
    'tax' => 'Tax',
    'shipping' => 'Shipping',
    'weight' => 'Weight',
    'total' => 'Total',
    'order_status_id' => 'Status',
);
$rows = array(25, 50, 100);

?>
