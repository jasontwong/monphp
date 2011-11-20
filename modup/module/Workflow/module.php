<?php
/**
 * Creates and manages custom triggers and actions to automate workflow tasks
 */
//{{{ class Workflow
class Workflow
{
    //{{{ properties
    static public $triggers;
    static public $responses;
    //}}}
    //{{{ constants
    const MODULE_AUTHOR = 'Glenn';
    const MODULE_DESCRIPTION = 'Automate workflow tasks';
    const MODULE_WEBSITE = 'http://www.glennyonemitsu.com/';
    const TRIGGER = 0;
    const RESPONSE = 1;
    //}}}
    //{{{ public function cb_workflow_trigger()
    public function cb_workflow_trigger()
    {
    }
    //}}}
    //{{{ public function cb_workflow_task()
    public function cb_workflow_task()
    {
        $a = func_get_args();
        var_dump('cb', $a);
    }
    //}}}
    //{{{ public function prep_workflow_task()
    public function prep_workflow_task($me, $module, $trigger)
    {
        $a = func_get_args();
        $data = array_slice($a, 3);
        var_dump('prep', $module, $trigger, $data);
    }
    //}}}
    //{{{ public function hook_workflow_task()
    public function hook_workflow_task()
    {
        // not used but needed to trigger prep method
    }
    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
        self::$triggers = Module::h('workflow_triggers');
        self::$responses = Module::h('workflow_responses');
    }

    //}}}
    //{{{ public function hook_admin_module_page($page)
    public function hook_admin_module_page($page)
    {
    }
    
    //}}}
    //{{{ public function hook_admin_nav()
    public function hook_admin_nav()
    {
        $links = array('Workflow' => array());
        if (User::has_perm('create workflow'))
        {
            $links['Tools'][] = '<a href="/admin/module/Workflow/create/">Create Workflow</a>';
        }
        if (User::has_perm('edit workflow'))
        {
            $links['Tools'][] = '<a href="/admin/module/Workflow/list/">Edit Workflow</a>';
        }
        return $links;
    }

    //}}}
    //{{{ public function hook_admin_rpc($action)
    public function hook_rpc($action)
    {
        $params = func_get_args();
        array_shift($params);
        $output = json_encode(NULL);
        switch ($action)
        {
            case 'trigger_params':
                $data = explode(':', json_decode($params[0]['json']));
                if (count($data) === 3)
                {
                    list($mod, $trigger, $subtrigger) = $data;
                    if ($subtrigger)
                    {
                        if (eka(Workflow::$triggers, $mod, $trigger, 'subtriggers', $subtrigger))
                        {
                            $output = json_encode(Workflow::$triggers[$mod][$trigger]['subtriggers'][$subtrigger]['params']);
                        }
                    }
                }
            break;
            case 'response_params':
                $data = explode(':', json_decode($params[0]['json']));
                if (count($data) === 2)
                {
                    list($mod, $response) = $data;
                    if (eka(Workflow::$responses, $mod, $response, 'params'))
                    {
                        $output = json_encode(Workflow::$responses[$mod][$response]['params']);
                    }
                }
            break;
        }
        echo $output;
    }
    //}}}
    //{{{ public function hook_admin_js()
    public function hook_admin_js()
    {
        $js = array();
        if (strpos(URI_PATH, '/admin/module/Workflow/create/') === 0 ||
            strpos(URI_PATH, '/admin/module/Workflow/edit/') === 0)
            {
                $js[] = '/admin/static/Workflow/workflow.js/';
            }
        return $js;
    }
    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        return array(
            'Workflow' => array(
                'create workflow' => 'Create automated workflow',
                'edit workflow' => 'Edit automated workflow',
                'delete workflow' => 'Delete automated workflow'
            )
        );
    }

    //}}}
    //{{{ static function save($workflow)
    static function save($workflow)
    {
        $task = new WorkflowTask;
        try
        {
            $task->merge($workflow);
            $task->save();
            return $task;
        }
        catch (Exception $e)
        {
            return FALSE;
        }
    }
    //}}}
    //{{{ static function update($workflow)
    static function update($workflow)
    {
        $tt = Doctrine::getTable('WorkflowTask');
        try
        {
            $task = $tt->findOneById($workflow['id']);
            $task->merge($workflow);
            $task->save();
            return $task;
        }
        catch (Exception $e)
        {
            return FALSE;
        }
    }
    //}}}
    //{{{ static function get($id = NULL)
    static function get($id = NULL)
    {
        $dql = Doctrine_Query::create()
               ->select('
                id, name, description, module, response,
                trigger_main, trigger_sub, trigger_params')
               ->from('WorkflowTask');
        $tasks = NULL;
        if (is_null($id))
        {
            $tasks = $dql->fetchArray();
            foreach ($tasks as &$task)
            {
                $task['trigger_params'] = unserialize($task['trigger_params']);
                $task['response'] = unserialize($task['response']);
            }
        }
        else
        {
            $dql->where('id = ?', $id);
            $tasks = $dql->fetchOne();
            $tasks['trigger_params'] = unserialize($task['trigger_params']);
            $tasks['response'] = unserialize($task['response']);
        }
        return $tasks;
    }
    //}}}
    //{{{ static function get_list()
    static function get_list()
    {
        return Doctrine_Query::create()
               ->select('id, name, description')
               ->from('WorkflowTask')
               ->fetchArray();
    }
    //}}}
    //{{{ static function params($type, $trigger)
    static function params($type, $trigger)
    {
        switch ($type)
        {
            case Workflow::TRIGGER:
                list($module, $trigger, $subtrigger) = explode(':', $trigger);
                return deka(NULL, Workflow::$triggers, $module, $trigger, 'subtriggers', $subtrigger, 'params');
            break;
            case Workflow::RESPONSE:
                list($module, $response) = explode(':', $trigger);
                return deka(NULL, Workflow::$responses, $module, $response, 'params');
            break;
        }
    }
    //}}}
}
//}}}

?>
