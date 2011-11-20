<?php

if (!User::has_perm('add ecommerce orders'))
{
    throw new Exception('You do not have access to this page');
}

Admin::set('title', 'Add New Order');
Admin::set('header', 'Add New Order');

//{{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'order_name',
        'type' => 'text'
    )
);

$layout->add_layout(
    array(
        'field' => Field::layout('ecommerce_address'),
        'name' => 'billing_address',
        'type' => 'ecommerce_address'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('ecommerce_address'),
        'name' => 'shipping_address',
        'type' => 'ecommerce_address'
    )
);

$coupons = Ecommerce::get_available_coupons();
$options = array();
foreach ($coupons as $coupon)
{
    $options[$coupon['id']] = $coupon['code'];
}

$layout->add_layout(
    array(
        'field' => Field::layout(
            'select_multiple',
            array(
                'data' => array(
                    'options' => $options,
                ),
            )
        ),
        'name' => 'coupons',
        'type' => 'dropdown'
    )
);

$gift_cards = Ecommerce::get_available_gift_cards();
$options = array();
foreach ($gift_cards as $gift_card)
{
    $options[$gift_card['id']] = $gift_card['code'];
}

$layout->add_layout(
    array(
        'field' => Field::layout(
            'select_multiple',
            array(
                'data' => array(
                    'options' => $options,
                ),
            )
        ),
        'name' => 'gift_cards',
        'type' => 'dropdown'
    )
);

$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => Ecommerce::get_status_options(),
                ),
            )
        ),
        'name' => 'order_status_id',
        'type' => 'dropdown'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('textarea'),
        'name' => 'user_comments',
        'type' => 'textarea'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('textarea'),
        'name' => 'admin_comments',
        'type' => 'textarea'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'customer_email',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'pp_authorization_id',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'pp_transaction_id',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'tracking_number',
        'type' => 'text'
    )
);

$layout->add_layout(
    array(
        'field' => Field::layout('ecommerce_product'),
        'name' => '15',
        'array' => TRUE,
        'type' => 'ecommerce_product',
    )
);

// cost
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'weight',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'text',
            array(
                'data' => array(
                    'attr' => array(
                        'class' => 'text EcommerceOrderShipping',
                        'type' => 'text',
                    ),
                ),
            )
        ),
        'name' => 'shipping',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'text',
            array(
                'data' => array(
                    'attr' => array(
                        'class' => 'text EcommerceOrderDiscount',
                        'type' => 'text',
                    ),
                ),
            )
        ),
        'name' => 'discount',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'text',
            array(
                'data' => array(
                    'attr' => array(
                        'class' => 'text EcommerceOrderTax',
                        'type' => 'text',
                    ),
                ),
            )
        ),
        'name' => 'tax',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'text',
            array(
                'data' => array(
                    'attr' => array(
                        'class' => 'text EcommerceOrderSubtotal',
                        'type' => 'text',
                    ),
                ),
            )
        ),
        'name' => 'subtotal',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'text',
            array(
                'data' => array(
                    'attr' => array(
                        'class' => 'text EcommerceOrderGiftCardDiscount',
                        'type' => 'text',
                    ),
                ),
            )
        ),
        'name' => 'gift_card_discount',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'text',
            array(
                'data' => array(
                    'attr' => array(
                        'class' => 'text EcommerceOrderTotal',
                        'type' => 'text',
                    ),
                ),
            )
        ),
        'name' => 'total',
        'type' => 'text'
    )
);

$layout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

$rows = array();
foreach (Ecommerce::get_order_keys() as $key)
{
    $layout->add_layout(
        array(
            'field' => Field::layout('text'),
            'name' => '_'.$key,
            'type' => 'text',
        )
    );
    $flayout = $layout->get_layout('_'.$key);
    $row['fields'] = $flayout;
    $row['label']['text'] = ucwords($key);
    $rows[] = $row;
    unset($row);
}
//}}}
// {{{ form submission
if (isset($_POST['form']))
{
    $order = $layout->acts('post', $_POST['order']);
    $data = $layout->acts('post', $_POST['data']);
    $products = $layout->acts('post', $_POST['products']);
    $options = $layout->acts('post', $_POST['options']);
    foreach ($options as $k => $v)
    {
        if (strpos($k, '_') === 0)
        {
            if (strpos($k, '_id') !== FALSE && strlen($k) > 3)
            {
                if (strlen($v))
                {
                    $order['Options'][str_replace('_id', '', substr($k, 1))]['id'] = $v;
                }
            }
            else
            {
                $order['Options'][substr($k, 1)]['name'] = substr($k, 1);
                $order['Options'][substr($k, 1)]['data'] = $v;
            }
        }
    }

    if (ake('Options', $order))
    {
        sort($order['Options']);
    }

    $eo = new EcommerceOrder();
    $eo->merge($order);
    $eo->BillingAddress->merge($data['billing_address']);
    $eo->ShippingAddress->merge($data['shipping_address']);
    $eo->Products->fromArray($products[15]);

    if (is_numeric($data['order_status_id']))
    {
        $eo->link('Status', $data['order_status_id']);
    }
    if (count($data['coupons']))
    {
        $eo->link('Coupons', $data['coupons']);
    }
    if (count($data['gift_cards']))
    {
        $eo->link('GiftCards', $data['gift_cards']);
    }

    if ($eo->isValid())
    {
        $eo->save();
        Admin::notify(Admin::TYPE_SUCCESS, 'Successfully saved');
        header('Location: /admin/module/Ecommerce/view_order/' . $eo->id . '/');
        exit;
    }
    else
    {
        var_dump($eo->getErrorStack()->toArray());
        Admin::notify(Admin::TYPE_ERROR, 'Unsuccessful Save');
    }
}
// }}}
//{{{ form build
$eform = new FormBuilderRows;
$eform->attr = array(
    'action' => URI_PATH,
    'enctype' => 'multipart/form-data',
    'method' => 'post'
);

$eform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('order_name'),
                'label' => array(
                    'text' => 'Order Name'
                )
            ),
            array(
                'fields' => $layout->get_layout('customer_email'),
                'label' => array(
                    'text' => 'Customer Email'
                )
            ),
        )
    ),
    'order'
);
$eform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('billing_address'),
                'label' => array(
                    'text' => 'Billing Address'
                )
            ),
            array(
                'fields' => $layout->get_layout('shipping_address'),
                'label' => array(
                    'text' => 'Shipping Address'
                )
            ),
        )
    ),
    'data'
);
$eform->add_group(
    array(
        'rows' => array(
            array(
                'row' => array(
                    'attr' => array(
                        'class' => 'content_multiple'
                    )
                ),
                'fields' => $layout->get_layout('15'),
                'label' => array(
                    'text' => 'Product'
                )
            ),
        )
    ),
    'products'
);
$eform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('coupons'),
                'label' => array(
                    'text' => 'Coupons'
                )
            ),
            array(
                'fields' => $layout->get_layout('gift_cards'),
                'label' => array(
                    'text' => 'Gift Cards'
                )
            ),
            array(
                'fields' => $layout->get_layout('order_status_id'),
                'label' => array(
                    'text' => 'Order Status'
                )
            ),
        )
    ),
    'data'
);
$eform->add_group(
    array(
        'rows' => $rows,
    ),
    'options'
);
$rows = array(
    array(
        'fields' => $layout->get_layout('user_comments'),
        'label' => array(
            'text' => 'User Comments'
        )
    ),
    array(
        'fields' => $layout->get_layout('admin_comments'),
        'label' => array(
            'text' => 'Admin Comments'
        )
    ),
    array(
        'fields' => $layout->get_layout('tracking_number'),
        'label' => array(
            'text' => 'Tracking Number'
        )
    ),
);

if (Ecommerce::is_using_paypal())
{
    $rows[] = array(
        'fields' => $layout->get_layout('pp_authorization_id'),
        'label' => array(
            'text' => 'PayPal Authorization ID'
        )
    );
    $rows[] = array(
        'fields' => $layout->get_layout('pp_transiaction_id'),
        'label' => array(
            'text' => 'PayPal Transaction ID'
        )
    );
}

$eform->add_group(
    array(
        'rows' => $rows,
    ),
    'order'
);
$eform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('weight'),
                'label' => array(
                    'text' => 'Weight'
                )
            ),
            array(
                'fields' => $layout->get_layout('subtotal'),
                'label' => array(
                    'text' => 'Subtotal'
                )
            ),
            array(
                'fields' => $layout->get_layout('discount'),
                'label' => array(
                    'text' => 'Discount'
                )
            ),
            array(
                'fields' => $layout->get_layout('gift_card_discount'),
                'label' => array(
                    'text' => 'Gift Card Discount'
                )
            ),
            array(
                'fields' => $layout->get_layout('tax'),
                'label' => array(
                    'text' => 'Tax'
                )
            ),
            array(
                'fields' => $layout->get_layout('shipping'),
                'label' => array(
                    'text' => 'Shipping'
                )
            ),
            array(
                'fields' => $layout->get_layout('total'),
                'label' => array(
                    'text' => 'Total'
                )
            ),
        )
    ),
    'order'
);
$eform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit'),
            ),
        )
    ),
    'form'
);

$efh = $eform->build();
// }}}

?>
