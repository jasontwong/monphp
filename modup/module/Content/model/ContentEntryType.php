<?php

class ContentEntryType extends Doctrine_Record
{
    //{{{ constants
    const ORDERING_AUTO = 0;
    const ORDERING_MANUAL = 1;
    const STACK_TOP = 0;
    const STACK_BOTTOM = 1;
    const STATUS_DISALLOW = 0;
    const STATUS_ALLOW = 1;
    const FLAGGING_DISALLOW = 0;
    const FLAGGING_ALLOW = 1;

    //}}}
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 1,
            array(
                'primary' => TRUE,
                'autoincrement' => TRUE,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'name', 'string', 200,
            array(
                'default' => '',
                'minlength' => 1,
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'description', 'string', NULL,
            array(
                'default' => '',
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'ordering', 'int', 1,
            array(
                'default' => self::ORDERING_AUTO,
            )
        );
        $this->hasColumn(
            'stack', 'int', 1,
            array(
                'default' => self::STACK_TOP,
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
        $this->hasMany(
            'ContentFieldGroup', 
            array(
                'cascade' => array('delete'),
                'foreign' => 'content_entry_type_id',
                'local' => 'id',
                'onDelete' => 'CASCADE',
            )
        );
        $this->hasMany(
            'ContentEntryMeta', 
            array(
                'cascade' => array('delete'),
                'foreign' => 'content_entry_type_id',
                'local' => 'id',
                'onDelete' => 'CASCADE',
            )
        );
    }

    //}}}
}

?>
