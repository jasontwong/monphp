<?php

if (!User::has_perm('edit workflow') || URI_PARTS < 5)
{
    header('Location: /admin/');
    exit;
}

$task = Workflow::get(URI_PART_4);
if (!$task)
{
    header('Location: /admin/module/Workflow/list/');
    exit;
}

$task_trigger_name = $task['module'].':'.$task['trigger_main'].':'.$task['trigger_sub'];
$task_trigger_module = $task['module'];
$task_response_name = $task['response'][0]['module'].':'.$task['response'][0]['response'];
$task_response_module = $task['response'][0]['module'];
$params_trigger = Workflow::params(Workflow::TRIGGER, $task_trigger_name);
$params_response = Workflow::params(Workflow::RESPONSE, $task_response_name);
$ptask = array(
    'name' => array('data' => $task['name']),
    'description' => array('data' => $task['description']),
    'trigger' => array('data' => $task_trigger_name),
    'response' => array('data' => $task_response_name)
);

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
                        'options' => $trigger_options,
                        'value' => $task_trigger_name
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

if ($params_trigger)
{
    foreach ($params_trigger as $name => $param_trigger)
    {
        $playout = array(
            'name' => 't:'.$task_trigger_module.'_'.$name,
            'type' => $param_trigger['type']
        );
        $ptask[$playout['name']]['data'] = $task['trigger_params'][$name];
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
}

if ($params_response)
{
    foreach ($params_response as $name => $param_response)
    {
        $playout = array(
            'name' => 'r:'.$task_response_module.'_'.$name,
            'type' => $param_response['type']
        );
        $ptask[$playout['name']]['data'] = $task['response'][0]['params'][$name];
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
$posted = FALSE;
if (eka($_POST, 'trigger') && eka($_POST, 'response'))
{
    $posted = TRUE;
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
        'id' => URI_PART_4,
        'module' => $ptriggers[0],
        'trigger_main' => $ptriggers[1],
        'trigger_sub' => $ptriggers[2],
        'trigger_params' => array(),
        'name' => $ptrigger['name'],
        'description' => $ptrigger['description']
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
    $task = Workflow::update($wf);

    if ($task)
    {
        header('Location: /admin/module/Workflow/edit/'.$task['id'].'/');
        exit;
    }
}
//}}}
//{{{ form build
$layout->merge($ptask);
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
    $module_t = $posted ? $ptriggers[0] : $task_trigger_module;
    $i = 1;
    foreach ($params_trigger as $k => $v)
    {
        $trigger_param_rows[] = array(
            'label' => array('text' => 'Trigger parameter '.$i++.': '.$v['label']),
            'fields' => $layout->get_layout('t:'.$module_t.'_'.$k)
        );
    }
    $form->add_group(
        array(
            'attr' => array('class' => 'group_trigger_parameters'),
            'rows' => $trigger_param_rows
        ), 
        'trigger_param'
    );
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
    $module_r = $posted ? $ptriggers[0] : $task_response_module;
    $i = 1;
    foreach ($params_response as $k => $v)
    {
        $response_param_rows[] = array(
            'label' => array('text' => 'Response parameter '.$i++.': '.$v['label']),
            'fields' => $layout->get_layout('r:'.$module_r.'_'.$k)
        );
    }
    $form->add_group(
        array(
            'attr' => array('class' => 'group_response_parameters'),
            'rows' => $response_param_rows
        ), 
        'response_param'
    );
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
