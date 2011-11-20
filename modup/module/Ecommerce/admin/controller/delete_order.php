<?php

Admin::set('title', 'Delete Order');
Admin::set('header', 'Delete Order');

if (!defined('URI_PART_4'))
{
    throw new Exception("You're not supposed to be here");
}

$eot = Doctrine::getTable('EcommerceOrder');
$eo = $eot->find(URI_PART_4);

//{{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('submit_confirm'),
        'name' => 'do',
        'type' => 'submit_confirm'
    )
);

//}}}
//{{{ form submission
if (isset($_POST['confirm']))
{
    $confirm = $layout->acts('post', $_POST['confirm']);
    if ($confirm['do'])
    {
        $eo->BillingAddress->delete();
        $eo->ShippingAddress->delete();
        $eo->Options->delete();
        foreach ($eo->Products as $product)
        {
            $product->Options->delete();
        }
        $eo->Products->delete();
        $eo->unlink('Coupons');
        $eo->unlink('GiftCards');
        $eo->delete();
        header('Location: /admin/module/Ecommerce/orders/');
        exit;
    }
    else
    {
        header('Location: /admin/module/Ecommerce/edit_order/'.URI_PART_4.'/');
        exit;
    }
}

//}}}
//{{{ form build
$form = new FormBuilderRows;
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('do')
            ),
        )
    ),
    'confirm'
);
$dfh = $form->build();

echo $dfh;
//}}}

?>
