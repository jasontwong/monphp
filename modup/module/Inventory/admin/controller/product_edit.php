<?php

Admin::set('title', 'Edit an inventory product');
Admin::set('header', 'Edit an inventory product');

$product = Inventory::get_product(URI_PART_4);

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'value' => array(
            'data' => $product['product_name']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'inventory', 
            array(
                'data' => array(
                    'attr' => array(
                        'type' => 'hidden',
                        'class' => 'hidden inventory-grid',
                        'data-options-x' => $product['group_x'],
                        'data-options-y' => $product['group_y'],
                        'data-product' => $product['product_id'],
                    )
                )
            )
        ),
        'name' => 'inventory',
        'type' => 'inventory',
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
    $product_post = $layout->acts('post', $_POST['product']);
    $product_save = array(
        'product_name' => $product_post['name'],
        'inventory' => $product_post['inventory']['inventory'],
        'x' => $product_post['inventory']['options_x'],
        'y' => $product_post['inventory']['options_y']
    );
    Inventory::update_product_and_inventory(URI_PART_4, $product_save);
    header('Location: /admin/module/Inventory/product_edit/'.URI_PART_4.'/');
    exit;
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
    'fields' => $layout->get_layout('name'),
    'label' => array(
        'text' => 'Name'
    )
);
$form_rows[] = array(
    'fields' => $layout->get_layout('inventory'),
    'label' => array(
        'text' => 'Inventory'
    )
);
$form_rows[] = array('fields' => $layout->get_layout('submit'));
$form->add_group(
    array('rows' => $form_rows),
    'product'
);
$nfh = $form->build();
//}}}

?>
