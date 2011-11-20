<?php 

if (!User::has_perm('create workflow'))
{
    header('Location: /admin/');
    exit;
}

$params_trigger = $params_response = NULL;

//{{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('textarea'),
        'name' => 'description',
        'type' => 'textarea'
    )
);
$mod_triggers = &Workflow::$triggers;
$trigger_options = array('' => 'Select a trigger');
foreach ($mod_triggers as $mod => $triggers)
{
    $trigger_options[$mod] = array();
    foreach ($triggers as $trigger => $trigger_data)
    {
        foreach ($trigger_data['subtriggers'] as $subtrigger => $stmisc)
        {
            if ($stmisc)
            {
                $trigger_label = $trigger_data['label'].' '.$subtrigger;
                $trigger_name = $mod.':'.$trigger.':'.$subtrigger;
            }
            else
            {
                $trigger_label = $trigger_data['label'];
                $trigger_name = $mod.':'.$trigger.':';
            }
            $name = $trigger_data['label'];
            $trigger_options[$mod][$trigger_name] = $trigger_label;
        }
    }
    $layout->add_layout(
        array(
            'field' => Field::layout(
                'dropdown',
                array(
                    'data' => array(
                        'options' => $trigger_options
                    )
                )
            ),
            'name' => 'trigger',
            'type' => 'dropdown'
        )
    );
}

$mod_responses = &Workflow::$responses;
$response_options = array('' => 'Select a response');
foreach ($mod_responses as $mod => $responses)
{
    $response_options[$mod] = array();
    foreach ($responses as $response => $response_data)
    {
        $response_name = $mod.':'.$response;
        $response_options[$mod][$response_name] = $response_data['label'];
    }
    $layout->add_layout(
        array(
            'field' => Field::layout(
                'dropdown',
                array(
                    'data' => array(
                        'options' => $response_options
                    )
                )
            ),
            'name' => 'response',
            'type' => 'dropdown'
        )
    );
}
$layout->add_layout(
    array(
        'field' => Field::layout(
            'submit_reset',
            array(
                'submit' => array(
                    'text' => 'Save'
                )
            )
        ),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);
//}}}
//{{{ form submitted
if (eka($_POST, 'trigger') && eka($_POST, 'response'))
{
    $ptrigger = $layout->acts('post', $_POST['trigger']);
    $presponse = $layout->acts('post', $_POST['response']);
    $ptriggers = explode(':', $ptrigger['trigger']);
    $presponses = explode(':', $presponse['response']);
    $params_trigger = Workflow::params(Workflow::TRIGGER, $ptrigger['trigger']);
    $params_response = Workflow::params(Workflow::RESPONSE, $presponse['response']);
    $pmod_trigger = $ptriggers[0];
    $pmod_response = $presponses[0];
    foreach ($params_trigger as $name => $param_trigger)
    {
        $playout = array(
            'name' => $pmod_trigger.'_'.$name,
            'type' => $param_trigger['type']
        );
        switch ($param_trigger['type'])
        {
            case 'dropdown':
                $playout['field'] = Field::layout(
                    'dropdown',
                    array(
                        'data' => array(
                            'options' => deka(array(), $param_trigger['options'])
                        )
                    )
                );
            break;
            case 'textarea':
                $playout['field'] = Field::layout('textarea');
            break;
        }
        $layout->add_layout($playout);
    }
    foreach ($params_response as $name => $param_response)
    {
        $playout = array(
            'name' => $pmod_response.'_'.$name,
            'type' => $param_response['type']
        );
        switch ($param_response['type'])
        {
            case 'dropdown':
                $playout['field'] = Field::layout(
                    'dropdown',
                    array(
                        'data' => array(
                            'options' => deka(array(), $param_response['options'])
                        )
                    )
                );
            break;
            case 'textarea':
                $playout['field'] = Field::layout('textarea');
            break;
        }
        $layout->add_layout($playout);
    }
    $pparams_trigger = $layout->acts('post', $_POST['trigger_param']);
    $pparams_response = $layout->acts('post', $_POST['response_param']);
    $layout->merge($_POST['trigger']);
    $layout->merge($_POST['response']);
    $layout->merge($_POST['trigger_param']);
    $layout->merge($_POST['response_param']);
    $wf = array(
        'name' => $ptrigger['name'],
        'description' => $ptrigger['description'],
        'module' => $ptriggers[0],
        'trigger_main' => $ptriggers[1],
        'trigger_sub' => $ptriggers[2],
        'trigger_params' => array(),
    );
    foreach ($pparams_trigger as $k => $v)
    {
        $key = substr($k, strlen($ptriggers[0]) + 3);
        $wf['trigger_params'][$key] = $v;
    }
    $wf['response'][0] = array(
        'module' => $presponses[0],
        'response' => $presponses[1],
        'params' => array()
    );
    foreach ($pparams_response as $k => $v)
    {
        $key = substr($k, strlen($presponses[0]) + 3);
        $wf['response'][0]['params'][$key] = $v;
    }
    //var_dump($ptrigger, $pparams_trigger, $presponse, $pparams_response, $wf);
    $task = Workflow::save($wf);
    if ($task)
    {
        header('Location: /admin/module/Workflow/edit/'.$task['id'].'/');
        exit;
    }
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
                'label' => array('text' => 'Name'),
                'fields' => $layout->get_layout('name')
            ),
            array(
                'label' => array('text' => 'Description'),
                'fields' => $layout->get_layout('description')
            ),
            array(
                'label' => array('text' => 'Trigger'),
                'fields' => $layout->get_layout('trigger')
            )
        )
    ),
    'trigger'
);
if ($params_trigger)
{
    $trigger_param_rows = array();
    $module_t = $ptriggers[0];
    $i = 1;
    foreach ($params_trigger as $k => $v)
    {
        $trigger_param_rows[] = array(
            'label' => array('text' => 'Trigger parameter '.$i++.': '.$v['label']),
            'fields' => $layout->get_layout($module_t.'_'.$k)
        );
    }
    $form->add_group(array('rows' => $trigger_param_rows), 'trigger_param');
}
$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array('text' => 'Response'),
                'fields' => $layout->get_layout('response')
            )
        )
    ),
    'response'
);
if ($params_response)
{
    $response_param_rows = array();
    $module_r = $ptriggers[0];
    $i = 1;
    foreach ($params_response as $k => $v)
    {
        $response_param_rows[] = array(
            'label' => array('text' => 'Response parameter '.$i++.': '.$v['label']),
            'fields' => $layout->get_layout($module_r.'_'.$k)
        );
    }
    $form->add_group(array('rows' => $response_param_rows), 'response_param');
}
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit'),
            ),
        )
    ),
    'form'
);
$fh = $form->build();

//}}}

?>
