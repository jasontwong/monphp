<?php

Admin::set('title', 'Create a new inventory product');
Admin::set('header', 'Create a new inventory product');

$product_group = Inventory::get_product_group(URI_PART_4);
$product_group['options_x'] = strlen($product_group['ogx_id'])
    ? Inventory::get_options($product_group['ogx_id'])
    : NULL;
$product_group['options_y'] = strlen($product_group['ogy_id'])
    ? Inventory::get_options($product_group['ogy_id'])
    : NULL;

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
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
                        'data-options-x' => $product_group['ogx_id'],
                        'data-options-y' => $product_group['ogy_id'],
                        'data-product' => NULL,
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
    $p_id = Inventory::save_product(URI_PART_4, $product_post['name']);
    if (eka($product_post, 'inventory', 'options_x'))
    {
        Inventory::save_product_options($p_id, 'x', $product_post['inventory']['options_x']);
    }
    if (eka($product_post, 'inventory', 'options_y'))
    {
        Inventory::save_product_options($p_id, 'y', $product_post['inventory']['options_y']);
    }
    Inventory::save_product_inventory($p_id, $product_post['inventory']['inventory']);
    header('Location: /admin/module/Inventory/product_edit/'.$p_id.'/');
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
