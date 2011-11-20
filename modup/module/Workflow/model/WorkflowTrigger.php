<?php

class WorkflowTrigger extends Doctrine_Record
{
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
            'maintrigger', 'string', 50
        );
        $this->hasColumn(
            'subtrigger', 'string', 50
        );
        $this->hasColumn(
            'params', 'string'
        );
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->index(
            'trigger_index', 
            array(
                'fields' => array('module', 'maintrigger')
            )
        );
    }
    public function setUp()
    {
        $this->hasMany(
            'WorkflowResponse', 
            array(
                'refClass' => 'WorkflowResponse',
                'local' => 'id',
                'foreign' => 'trigger_id'
            )
        );
    }
}

?>
