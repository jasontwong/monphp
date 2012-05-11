<?php

//{{{ prepare database data from file and or POST
$db = array(
    'host' => '',
    'port' => '',
    'options' => array(
        'connect' => TRUE,
        'timeout' => 5000,
        'username' => '',
        'password' => '',
        'replicaSet' => '',
        'db' => '',
    ),
);

$dbfile = DIR_SYS.'/config.database.php';
$valid['writeable'] = is_writeable($dbfile);
if (is_readable($dbfile))
{
    include $dbfile;
    $dbf = array_merge($db, $_db_conn['default']);
    $dbf['options'] = array_merge($db['options'], $_db_conn['default']['options']);
    $valid['file'] = test_db_settings($dbf);
}
else
{
    $valid['file'] = FALSE;
}
//}}}
// {{{ form
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'host',
        'type' => 'text',
        'value' => array('data' => $dbf['host'])
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'port',
        'type' => 'text',
        'value' => array('data' => $dbf['port'])
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'username',
        'type' => 'text',
        'value' => array('data' => $dbf['options']['username'])
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'password',
        'type' => 'text',
        'value' => array('data' => $dbf['options']['password'])
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'db',
        'type' => 'text',
        'value' => array('data' => $dbf['options']['db'])
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('text'),
        'name' => 'replicaSet',
        'type' => 'text',
        'value' => array('data' => $dbf['options']['replicaSet'])
    )
);
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
        'type' => 'submit_reset'
    )
);
//}}}
// {{{ post
if (isset($_POST['database']))
{
    $dbp = array_merge($db, $layout->acts('POST', $_POST['database']));
    $dbp['options'] = array_merge($db['options'], $layout->acts('POST', $_POST['options']));
    $layout->merge($_POST['database']);
    $layout->merge($_POST['options']);
    $valid['post'] = $db === $dbp;
    if (!$valid['post'] && $valid['post'] = test_db_settings($dbp))
    {
        ob_start();
        include DIR_SYS.'/install/template/config.database.php';
        file_put_contents(DIR_SYS.'/config.database.php', ob_get_clean());
    }
}
else
{
    $valid['post'] = FALSE;
}
// }}}
//{{{ conclusion?
if ($valid['writeable'])
{
    if (isset($_POST['database']))
    {
        if ($valid['post'])
        {
            $is_valid = TRUE;
            $messages['success'][] = 'The settings have been saved.';
        }
        else
        {
            if ($valid['file'])
            {
                $is_valid = TRUE;
                $messages['success'][] = 'The settings are not good. But the details in the conf/database.php file are. If you wish to continue using the settings in that file, click proceed.';
            }
            else
            {
                $is_valid = FALSE;
                if (is_readable($dbfile))
                {
                    $messages['notice'][] = 'The settings submitted and the details in the conf/database.php are not good. Please enter in the correct details below.';
                }
                else
                {
                    $messages['notice'][] = 'The settings submitted are not good. Please try again.';
                }
            }
        }
    }
    else
    {
        if ($valid['file'])
        {
            $is_valid = TRUE;
            $messages['success'][] = 'The settings in conf/database.php are good. You may proceed or enter other settings with the form below.';
        }
        else
        {
            $is_valid = FALSE;
            $messages['notice'][] = 'Please enter the database settings below.';
        }
    }
}
else
{
    if (isset($_POST['database']))
    {
        if ($valid['post'])
        {
            if ($valid['file'])
            {
                $is_valid = TRUE;
                $messages['notice'][] = 'The settings in conf/database.php are good, but they cannot be overwritten with your submission. You may proceed with the settings in the file if you would like.';
            }
            else
            {
                $is_valid = FALSE;
                $messages['notice'][] = 'The settings submitted are good. But the conf/database.php file is not writeable. Please grant write permissions for that file.';
            }
        }
        else
        {
            if ($valid['file'])
            {
                $is_valid = TRUE;
                $messages['notice'][] = 'The settings submitted are not good. But the conf/database.php settings are. To use these settings click proceed.';
            }
            else
            {
                $is_valid = FALSE;
                $messages['notice'][] = 'The settings submitted are not good. Please try again.';
                $messages['notice'][] = 'The conf/database.php file is not writeable. Please grant write permissions to this file.';
            }
        }
    }
    else
    {
        if ($valid['file'])
        {
            $is_valid = TRUE;
            $messages['success'][] = 'The settings in conf/database.php are good. If you want to use the file settings click proceed.';
        }
        else
        {
            $is_valid = FALSE;
            $messages['notice'][] = 'The settings in conf/database.php are not good, and cannot be written. Please either give write permissions or change the settings for that file.';
        }
    }
}
//}}}
//{{{ build form
$form = new MPFormRows;
$form->attr = array(
    'action' => '/install/database/',
    'method' => 'POST'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Host'
                ),
                'fields' => $layout->get_layout('host')
            ),
            array(
                'label' => array(
                    'text' => 'Port'
                ),
                'fields' => $layout->get_layout('port')
            ),
        )
    ),
    'database'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'MPUsername'
                ),
                'fields' => $layout->get_layout('username')
            ),
            array(
                'label' => array(
                    'text' => 'Password'
                ),
                'fields' => $layout->get_layout('password')
            ),
            array(
                'label' => array(
                    'text' => 'MPDatabase'
                ),
                'fields' => $layout->get_layout('db')
            ),
            array(
                'label' => array(
                    'text' => 'Replica Set'
                ),
                'fields' => $layout->get_layout('replicaSet')
            ),
        )
    ),
    'options'
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

//}}}

?>
