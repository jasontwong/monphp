<?php

class WorkflowTask extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 4, 
            array(
                'autoincrement' => TRUE,
                'notnull' => TRUE,
                'primary' => TRUE, 
            )
        );
        $this->hasColumn(
            'name', 'string', 20,
            array(
                'unique' => TRUE
            )
        );
        $this->hasColumn(
            'description', 'string', 200
        );
        $this->hasColumn(
            'module', 'string', 20
        );
        $this->hasColumn(
            'trigger_main', 'string', 50
        );
        $this->hasColumn(
            'trigger_sub', 'string', 50
        );
        $this->hasColumn(
            'trigger_params', 'string'
        );
        $this->hasColumn(
            'response', 'string'
        );
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->index(
            'trigger_index', 
            array(
                'fields' => array('module', 'trigger_main')
            )
        );
        $this->index(
            'name_index', 
            array(
                'fields' => array('name')
            )
        );
    }
    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->setListener(new WorkflowTaskListener);
    }
    //}}}
}

?>
