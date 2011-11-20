<?php

class WorkflowParam extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 8, 
            array(
                'autoincrement' => TRUE,
                'notnull' => TRUE,
                'primary' => TRUE, 
            )
        );
        $this->hasColumn(
            'type', 'integer', 1
        );
        $this->hasColumn(
            'type_id', 'integer', 4
        );
        $this->hasColumn(
            'trigger', 'string', 255
        );
        $this->hasColumn(
            'subtrigger', 'string', 255
        );
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }
    public function setUp()
    {
    }
}

?>
