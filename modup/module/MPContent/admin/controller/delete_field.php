<?php

if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

MPAdmin::set('title', 'Delete Custom MPField');
MPAdmin::set('header', 'Delete Custom MPField');

$_GET = $_GET ? $_GET : array('f' => array(''), 'et' => '');

// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('hidden'),
        'name' => 'entry_type',
        'type' => 'hidden',
        'value' => array(
            'data' => $_GET['et']
        )
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout(
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
        'field' => MPField::layout('submit_confirm'),
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
        MPContent::delete_field_by_ids($confirm['field']);
    }
    header('Location: /admin/module/MPContent/edit_type/'.$confirm['entry_type'].'/');
    exit;
}

//}}}
//{{{ form build
$form = new MPFormBuilderRows;
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
