<?php

Admin::set('title', 'Delete an inventory product');
Admin::set('header', 'Delete an inventory product');

$product = Inventory::get_product(URI_PART_4);

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => $product['product_id']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);
// }}}
//{{{ form submission
if ($_POST)
{
    $product = $layout->acts('post', $_POST['product']);
    if (Inventory::delete_product($product['id']))
    {
        header('Location: /admin/module/Inventory/products/');
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
$form_rows = array();
$form_rows[] = array(
    'fields' => $layout->get_layout('id'),
);
$form_rows[] = array('fields' => $layout->get_layout('submit'));
$form->add_group(
    array('rows' => $form_rows),
    'product'
);
$fh = $form->build();
//}}}

?>
