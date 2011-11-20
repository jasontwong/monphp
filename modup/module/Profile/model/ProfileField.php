<?php

class ProfileField extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 8,
            array(
                'primary' => TRUE,
                'notnull' => TRUE,
                'autoincrement' => TRUE
            )
        );
        $this->hasColumn(
            'name', 'string', 150,
            array(
                'notblank' => TRUE,
                'unique' => TRUE
            )
        );
        $this->hasColumn(
            'type', 'string', 50,
            array(
                'notblank' => TRUE
            )
        );
        $this->hasColumn(
            'profile_group_id', 'integer', 4,
            array(
                'notblank' => TRUE
            )
        );
        $this->hasColumn(
            'weight', 'integer', 4,
            array(
                'default' => 0
            )
        );
        $this->hasColumn(
            'description', 'string', NULL,
            array(
                'default' => ''
            )
        );
        $this->hasColumn(
            'required', 'integer', 1,
            array(
                'default' => 0
            )
        );
        $this->hasColumn(
            'meta', 'array', NULL,
            array(
                'default' => array()
            )
        );
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasOne(
            'ProfileGroup as Group', 
            array(
                'local' => 'profile_group_id',
                'foreign' => 'id',
            )
        );
        $this->hasMany(
            'ProfileData as Data', 
            array(
                'local' => 'id',
                'foreign' => 'profile_field_id',
                'cascade' => array('delete')
            )
        );
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
    }

    //}}}
}
