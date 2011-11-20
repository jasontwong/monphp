<?php

Module::h('active');
$mod_fields = Module::h('install_form_build');
$layout = new Field();
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
//{{{ build form
$form = new FormBuilderRows;
$form->attr = array(
    'action' => '/install/module_setup/',
    'method' => 'post'
);
$form->label = array(
    'text' => 'Module Setup'
);
foreach ($mod_fields as $mod => $group)
{
    if (isset($_POST[$mod]))
    {
        foreach ($group['rows'] as &$row)
        {
            $layout->add_layout($row['fields']);
            $layout->merge($_POST[$mod]);
            $row['fields'] = $layout->get_layout($row['fields']['name']);
        }
        $group_data = $layout->acts('post', $_POST[$mod]);
        Module::h('install_form_process', $mod, $group_data, array());
    }
    $form->add_group($group, $mod);
}

// TODO check for errors
if (isset($_POST['submit']))
{
    header('Location: /install/done/');
    exit;
}
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            )
        )
    ),
    'submit'
);
$fh = $form->build();

//}}}

?>
