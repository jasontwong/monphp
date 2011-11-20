<?php

class ProfileGroup extends Doctrine_Record
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
                'notblank' => TRUE
            )
        );
        $this->hasColumn(
            'weight', 'integer', 1,
            array(
                'default' => 0
            )
        );
        $this->hasColumn(
            'status', 'integer', 1,
            array(
                'default' => Profile::LISTED
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
        $this->hasMany(
            'ProfileField as Fields', 
            array(
                'local' => 'id',
                'foreign' => 'profile_group_id',
                'cascade' => array('delete')
            )
        );
    }

    //}}}
}
