<?php

$modules = Module::available();
$names = array_keys($modules);
$mods = array();

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout(
            'checkbox',
            array(
                'data' => array(
                    'options' => array_combine($names,$names)
                )
            )
        ),
        'name' => 'modules',
        'type' => 'checkbox',
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'submit_reset',
            array(
                'submit' => array(
                    'text' => 'Save',
                    'label' => ''
                )
            )
        ),
        'name' => 'submit',
        'type' => 'submit_reset',
    )
);
// }}}
// {{{ post
if (isset($_POST['mods']))
{
    $mods = $layout->acts('post', $_POST['mods']);
    $layout->merge($_POST['mods']);
    Data::update('_System', 'modules', $mods['modules'], TRUE);
    if (Module::check_dependency($mods['modules']))
    {
        Module::load_active();
        Module::install();
        Data::save();
        header('Location: /install/module_setup/');
        exit;
    }
    else
    {
        $messages['notice'][] = 'Some module dependencies do not match.';
    }
}
// }}}
// {{{ form
$form = new FormBuilderRows;
$form->attr = array(
    'action' => '/install/modules/',
    'method' => 'POST'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Modules'
                ),
                'fields' => $layout->get_layout('modules')
            ),
        )
    ),
    'mods'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            ),
        )
    )
);
$fh = $form->build();
// }}}

?>
