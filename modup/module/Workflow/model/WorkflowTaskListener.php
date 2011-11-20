<?php

class WorkflowTaskListener extends Doctrine_Record_Listener
{
    //{{{ public function preSave(Doctrine_Event $event)
    public function preSave(Doctrine_Event $event)
    {
        $row = $event->getInvoker();
        if (!is_string($row->trigger_params))
        {
            $row->trigger_params = serialize($row->trigger_params);
        }
        if (!is_string($row->response))
        {
            $row->response = serialize($row->response);
        }
    }
    //}}}
    //{{{ public function preUpdate(Doctrine_Event $event)
    public function preUpdate(Doctrine_Event $event)
    {
        $row = $event->getInvoker();
        if (!is_string($row->trigger_params))
        {
            $row->trigger_params = serialize($row->trigger_params);
        }
        if (!is_string($row->response))
        {
            $row->response = serialize($row->response);
        }
    }
    //}}}
}
