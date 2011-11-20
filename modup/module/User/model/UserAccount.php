<?php

class UserAccount extends Doctrine_Record
{
    //{{{ public function save()
    public function save()
    {
        if (empty($this->setting))
        {
            $this->setting = array();
        }
        if (is_null($this->nice_name) || !strlen($this->nice_name))
        {
            $this->nice_name = $this->name;
        }
        parent::save();
    }

    //}}}
    //{{{ public function setTableDefinition()
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
            'name', 'string', 50, 
            array(
                'type' => 'string', 
                'unique' => TRUE, 
            )
        );
        $this->hasColumn(
            'nice_name', 'string', 200, 
            array(
                'type' => 'string', 
            )
        );
        $this->hasColumn(
            'pass', 'string', 40, 
            array(
                'type' => 'string', 
                'fixed' => TRUE, 
                'length' => '40'
            )
        );
        $this->hasColumn(
            'salt', 'string', 5, 
            array(
                'type' => 'string', 
                'fixed' => TRUE, 
                'length' => '5'
            )
        );
        $this->hasColumn(
            'joined', 'integer', 8
        );
        $this->hasColumn(
            'logged_in', 'integer', 8
        );
        $this->hasColumn(
            'email', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255'
            )
        );
        $this->hasColumn('permission', 'array');
        $this->hasColumn('setting', 'array');
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }
    
    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->setListener(new UserListener);
        $this->hasMany(
            'UserGroup as Groups', 
            array(
                'refClass' => 'UserGrouping',
                'local' => 'user_id',
                'foreign' => 'group_id',
            )
        );
    }
    
    //}}}
}
