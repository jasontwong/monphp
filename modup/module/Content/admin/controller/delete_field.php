<?php

if (!User::perm('edit content type'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Delete Custom Field');
Admin::set('header', 'Delete Custom Field');

$_GET = $_GET ? $_GET : array('f' => array(''), 'et' => '');

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'entry_type',
        'type' => 'hidden',
        'value' => array(
            'data' => $_GET['et']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'checkbox',
            array(
                'data' => array(
                    'options' => array_combine($_GET['f'], $_GET['f'])
                )
            )
        ),
        'name' => 'field',
        'type' => 'checkbox',
        'value' => array(
            'data' => $_GET['f']
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('submit_confirm'),
        'name' => 'do',
        'type' => 'submit_confirm'
    )
);

// }}}
//{{{ form submission
if (eka($_POST, 'confirm'))
{
    $confirm = $layout->acts('post', $_POST['confirm']);
    if ($confirm['do'])
    {
        Content::delete_field_by_ids($confirm['field']);
    }
    header('Location: /admin/module/Content/edit_type/'.$confirm['entry_type'].'/');
    exit;
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
                'row' => array(
                    'attr' => array(
                        'class' => 'hiddens',
                    )
                ),
                'fields' => $layout->get_layout('field')
            ),
            array(
                'fields' => $layout->get_layout('entry_type')
            ),
            array(
                'fields' => $layout->get_layout('do')
            ),
        )
    ),
    'confirm'
);
$cfh = $form->build();

//}}}

?>
