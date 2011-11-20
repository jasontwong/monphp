<?php

$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('textarea_array'),
        'name' => 'success',
        'type' => 'textarea_array'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('textarea_array'),
        'name' => 'error',
        'type' => 'textarea_array'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('textarea_array'),
        'name' => 'notice',
        'type' => 'textarea_array'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('textarea_array'),
        'name' => 'important',
        'type' => 'textarea_array'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('submit'),
        'name' => 'submit',
        'type' => 'submit'
    )
);

if (isset($_POST['notices']))
{
    $notices = $layout->acts('post', $_POST['notices']);
    foreach ($notices as $type => $messages)
    {
        switch ($type)
        {
            case 'success':
                Notification::notify(Notification::TYPE_SUCCESS, $messages);
            break;
            case 'error':
                Notification::notify(Notification::TYPE_ERROR, $messages);
            break;
            case 'notice':
                Notification::notify(Notification::TYPE_NOTICE, $messages);
            break;
            case 'important':
                Notification::notify(Notification::TYPE_IMPORTANT, $messages);
            break;
        }
    }
}

$form = new FormBuilderRows();
$form->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);

$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Success messages'
                ),
                'fields' => $layout->get_layout('success')
            ),
            array(
                'label' => array(
                    'text' => 'Error messages'
                ),
                'fields' => $layout->get_layout('error')
            ),
            array(
                'label' => array(
                    'text' => 'Notice messages'
                ),
                'fields' => $layout->get_layout('notice')
            ),
            array(
                'label' => array(
                    'text' => 'Important messages'
                ),
                'fields' => $layout->get_layout('important')
            ),
            array(
                'fields' => $layout->get_layout('submit')
            ),
        )
    ),
    'notices'
);

echo $form->build();

?>
