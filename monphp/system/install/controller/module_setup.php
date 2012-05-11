<?php

MPModule::h('active');
$mod_fields = MPModule::h('install_form_build');
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout(
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
$form = new MPFormRows;
$form->attr = array(
    'action' => '/install/module_setup/',
    'method' => 'post'
);
$form->label = array(
    'text' => 'MPModule Setup'
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
        MPModule::h('install_form_process', $mod, $group_data, array());
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
