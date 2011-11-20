<?php

class ContentFieldGroup extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 2,
            array(
                'autoincrement' => TRUE,
                'notnull' => TRUE,
                'primary' => TRUE,
            )
        );
        $this->hasColumn(
            'weight', 'integer', 2,
            array(
                'default' => 0,
                'notnull' => TRUE,
                'range' => array(-10000, 10000)
            )
        );
        $this->hasColumn(
            'name', 'string', 200,
            array(
                'default' => '',
                'minlength' => 1,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'content_entry_type_id', 'integer', 1,
            array(
                'notnull' => TRUE
            )
        );
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->option('type', 'MyISAM');
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasOne(
            'ContentEntryType', 
            array(
                'foreign' => 'id',
                'local' => 'content_entry_type_id',
                'onDelete' => 'CASCADE'
            )
        );
        $this->hasMany(
            'ContentFieldType', 
            array(
                'cascade' => array('delete'),
                'foreign' => 'content_field_group_id',
                'local' => 'id',
                'onDelete' => 'CASCADE'
            )
        );
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
        $cett = Doctrine::getTable('ContentEntryType');
        $entry_type = $cett->findOneById($this->content_entry_type_id);
        if (!$entry_type)
        {
            $errors->add('entry type', 'The entry type does not exist. Please specify an existing entry type.');
        }
    }

    //}}}
}

?>
