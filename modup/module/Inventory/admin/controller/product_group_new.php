<?php

Admin::set('title', 'Create a new inventory product group');
Admin::set('header', 'Create a new inventory product group');

$option_groups = Inventory::get_option_groups();
$has_option_groups = (bool)$option_groups;
if ($option_groups)
{
    $option_groups_dropdown = array('' => '&mdash; Select One &mdash;');
    foreach ($option_groups as $group)
    {
        $option_groups_dropdown[$group['id']] = $group['name'];
    }
}
// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'html_after' => array(
            'data' => !$has_option_groups ? '<p>There are currently no option groups. You can add them later.</p>' : ''
        )
    )
);
if ($has_option_groups)
{
    $layout->add_layout(
        array(
            'field' => Field::layout('dropdown'),
            'name' => 'options_x',
            'type' => 'dropdown',
            'options' => array(
                'data' => $option_groups_dropdown
            )
        )
    );
    $layout->add_layout(
        array(
            'field' => Field::layout('dropdown'),
            'name' => 'options_y',
            'type' => 'dropdown',
            'options' => array(
                'data' => $option_groups_dropdown
            )
        )
    );
}
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
    $pgroup = $layout->acts('post', $_POST['product_group']);
    $pg_id = Inventory::save_product_group(
        $pgroup['name'], 
        deka(NULL, $pgroup, 'options_x'),
        deka(NULL, $pgroup, 'options_y')
    );
    header('Location: /admin/module/Inventory/product_group_edit/'.$pg_id.'/');
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
if ($has_option_groups)
{
    $form_rows[] = array(
        'fields' => $layout->get_layout('options_x'),
        'label' => array(
            'text' => 'Option Group on the X-Axis'
        )
    );
    $form_rows[] = array(
        'fields' => $layout->get_layout('options_y'),
        'label' => array(
            'text' => 'Option Group on the Y-Axis'
        )
    );
}
$form_rows[] = array('fields' => $layout->get_layout('submit'));
$form->add_group(
    array('rows' => $form_rows),
    'product_group'
);
$nfh = $form->build();
//}}}

?>
