<?php

class UserGroup extends Doctrine_Record
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
            'name', 'string', 50, 
            array(
                'type' => 'string', 
                'unique' => true, 
                'length' => '50'
            )
        );
        $this->hasColumn('permission', 'array');
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasMany(
            'UserAccount', 
            array(
                'refClass' => 'UserGrouping',
                'local' => 'group_id',
                'foreign' => 'user_id'
            )
        );
    }

    //}}}
}
