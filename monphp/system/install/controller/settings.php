<?php

MPDB::drop();
rm_resource_dir(DIR_FILE.'/module', FALSE);

// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'title',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'description',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('dropdown_timezones'),
        'name' => 'time_zone',
        'type' => 'dropdown_timezones'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);
$layout->merge(
    array(
        'time_zone' => array('data' => 'America/New_York')
    )
);
// }}}
//{{{ form submission
if (isset($_POST['settings']))
{
    $result = $layout->acts('post', $_POST['settings']);
    $layout->merge($_POST['settings']);
    foreach ($result as $key => $data)
    {
        MPData::update('_Site', $key, $data, TRUE);
        MPData::save();
    }
    header('Location: /install/modules/');
    exit;
}

//}}}
// {{{ form
$form = new MPFormRows;
$form->attr = array(
    'action' => '/install/settings/',
    'method' => 'POST'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Site Title'
                ),
                'fields' => $layout->get_layout('title')
            ),
            array(
                'label' => array(
                    'text' => 'Site Description'
                ),
                'fields' => $layout->get_layout('description')
            ),
            array(
                'label' => array(
                    'text' => 'Default Time Zone'
                ),
                'fields' => $layout->get_layout('time_zone')
            )
        )
    ),
    'settings'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            )
        )
    )
);
$fh = $form->build();
// }}}

?>
