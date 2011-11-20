<?php

class WorkflowResponse extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 4, 
            array(
                'autoincrement' => TRUE,
                'notnull' => TRUE,
                'primary' => TRUE
            )
        );
        $this->hasColumn(
            'trigger_id', 'integer', 4
        );
        $this->hasColumn(
            'module', 'string', 30
        );
        $this->hasColumn(
            'response', 'string', 50
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
                'fields' => array('trigger_id')
            )
        );
        $this->index(
            'response_index', 
            array(
                'fields' => array('trigger_id', 'module', 'response')
            )
        );
    }
    public function setUp()
    {
        $this->hasOne(
            'WorkflowTrigger', 
            array(
                'refClass' => 'WorkflowTrigger',
                'local' => 'trigger_id',
                'foreign' => 'id'
            )
        );
    }
}

?>
