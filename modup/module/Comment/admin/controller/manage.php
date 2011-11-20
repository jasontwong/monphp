<?php

if (!User::perm('moderate comments') && !User::perm('edit comments'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Manage Comments');
Admin::set('header', 'Manage Comments');

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'approved',
        'type' => 'checkbox_boolean'
    )
);

// }}}
// {{{ submmision
if (isset($_GET['filter']))
{
    $filter = $layout->post('acts', $_GET['filter']);
    $layout->merge($_GET['filter']);
}

// }}}
// {{{ form
$form = new FormBuilderRows();
$form->attr = array(
    'method' => 'get',
    'action' => URI_PATH,
);

// }}}

?>
