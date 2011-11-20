<?php

class ProfileData extends Doctrine_Record
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
            'user_id', 'integer', 8,
            array(
                'notblank' => TRUE
            )
        );
        $this->hasColumn(
            'profile_field_id', 'integer', 8,
            array(
                'notblank' => TRUE
            )
        );
        $this->hasColumn(
            'data', 'string', NULL,
            array(
                'default' => ''
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
            'ProfileField as Field', 
            array(
                'local' => 'profile_field_id',
                'foreign' => 'id',
            )
        );
        $this->hasOne(
            'UserAccount as User', 
            array(
                'local' => 'user_id',
                'foreign' => 'id',
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
